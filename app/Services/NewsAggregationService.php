<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NewsSourceEnum;
use App\Models\NewsSource;
use App\Repositories\ArticleRepository;
use App\Repositories\AuthorRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\NewsSourceRepository;
use App\Services\NewsAPI\GuardianAPIService;
use App\Services\NewsAPI\NewsAPIService;
use App\Services\NewsAPI\NewsDataIOService;
use Illuminate\Support\Facades\Log;

class NewsAggregationService
{
    public function __construct(
        private NewsSourceRepository $newsSourceRepository,
        private ArticleRepository $articleRepository,
        private CategoryRepository $categoryRepository,
        private AuthorRepository $authorRepository,
        private NewsDataIOService $newsDataIOService,
        private GuardianAPIService $guardianAPIService,
        private NewsAPIService $newsAPIService,
    ) {}

    public function fetchFromAllEnabledSources(): array
    {
        $sources = $this->newsSourceRepository->get(['status' => 'enabled']);
        $results = [];

        foreach ($sources as $source) {
            // Smart fetching based on last fetch time
            $params = $this->getSmartFetchParams($source);
            $result = $this->fetchFromSource($source, $params);
            $results[$source->source_enum->value] = $result;

            if ($result['success']) {
                $this->newsSourceRepository->update($source->id, ['last_fetched_at' => now()]);
            }
        }

        return $results;
    }

    private function getSmartFetchParams(NewsSource $source): array
    {
        $params = [];

        // If first time fetching, get articles from last 7 days
        if (! $source->last_fetched_at) {
            if ($source->source_enum === NewsSourceEnum::GUARDIAN) {
                $params['page-size'] = 50;
            } elseif ($source->source_enum === NewsSourceEnum::NEWSAPI) {
                $params['pageSize'] = 50;
            }
            // NewsData.io doesn't support size parameter - uses default

            // Add date range for initial fetch (last 7 days)
            $fromDate = now()->subDays(7);
            $params = $this->addDateRangeParams($params, $source->source_enum, $fromDate);

            return $params;
        }

        // Use last_fetched_at as starting point for new articles
        $fromDate = $source->last_fetched_at;
        $hoursSinceLastFetch = now()->diffInHours($fromDate);

        if ($hoursSinceLastFetch > 6) {
            if ($source->source_enum === NewsSourceEnum::GUARDIAN) {
                $params['page-size'] = 30;
            } elseif ($source->source_enum === NewsSourceEnum::NEWSAPI) {
                $params['pageSize'] = 30;
            }
            // NewsData.io doesn't support size parameter - uses default
        } else {
            if ($source->source_enum === NewsSourceEnum::GUARDIAN) {
                $params['page-size'] = 15;
            } elseif ($source->source_enum === NewsSourceEnum::NEWSAPI) {
                $params['pageSize'] = 15;
            }
            // NewsData.io doesn't support size parameter - uses default
        }

        // Add date range to fetch only articles since last sync
        $params = $this->addDateRangeParams($params, $source->source_enum, $fromDate);

        return $params;
    }

    private function addDateRangeParams(array $params, NewsSourceEnum $sourceEnum, \Carbon\Carbon $fromDate): array
    {
        switch ($sourceEnum) {
            case NewsSourceEnum::NEWSDATA_IO:
                // NewsData.io /latest endpoint doesn't support date filters
                // It always returns the latest articles
                break;

            case NewsSourceEnum::GUARDIAN:
                // Guardian uses 'from-date' parameter (YYYY-MM-DD format)
                $params['from-date'] = $fromDate->format('Y-m-d');
                break;

            case NewsSourceEnum::NEWSAPI:
                // NewsAPI uses 'from' parameter (ISO 8601 format)
                $params['from'] = $fromDate->startOfDay()->format('Y-m-d\TH:i:s\Z');
                break;
        }

        return $params;
    }

    private function getDateRangeInfo(array $params): string
    {
        if (isset($params['from-date'])) {
            return "from {$params['from-date']} (Guardian)";
        }

        if (isset($params['from'])) {
            return "from {$params['from']} (NewsAPI)";
        }

        return 'latest articles';
    }

    public function fetchFromSource(NewsSource $source, array $params = []): array
    {
        try {
            $articles = $this->getApiService($source->source_enum)->fetchLatestNews($source, $params);

            if ($articles->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'No new articles found',
                    'articles_count' => 0,
                ];
            }

            $savedCount = 0;
            foreach ($articles as $articleData) {
                if ($this->saveArticle($articleData)) {
                    $savedCount++;
                }
            }

            $dateRange = $this->getDateRangeInfo($params);

            return [
                'success' => true,
                'message' => "Successfully processed {$savedCount} articles",
                'articles_count' => $savedCount,
                'total_fetched' => $articles->count(),
                'date_range' => $dateRange,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to fetch from source', [
                'source' => $source->source_enum->value,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch articles: '.$e->getMessage(),
                'articles_count' => 0,
            ];
        }
    }

    private function getApiService(NewsSourceEnum $sourceEnum)
    {
        return match ($sourceEnum) {
            NewsSourceEnum::NEWSDATA_IO => $this->newsDataIOService,
            NewsSourceEnum::GUARDIAN => $this->guardianAPIService,
            NewsSourceEnum::NEWSAPI => $this->newsAPIService,
            default => throw new \InvalidArgumentException("Unsupported source: {$sourceEnum->value}"),
        };
    }

    private function saveArticle(array $articleData): bool
    {
        try {
            // Check if article already exists
            $existingArticle = $this->articleRepository->first([
                'external_id' => $articleData['external_id'],
                'source_id' => $articleData['source_id'],
            ]);

            if ($existingArticle) {
                return false; // Article already exists
            }

            // Handle category
            $categoryId = null;
            if (! empty($articleData['category'])) {
                $category = $this->categoryRepository->updateOrCreate(['name' => $articleData['category']]);
                $categoryId = $category->id;
            }

            // Handle author
            $authorId = null;
            if (! empty($articleData['author'])) {
                $author = $this->authorRepository->updateOrCreate(['name' => $articleData['author']]);
                $authorId = $author->id;
            }

            // Prepare article data
            $finalArticleData = $articleData;
            $finalArticleData['category_id'] = $categoryId;
            $finalArticleData['author_id'] = $authorId;
            unset($finalArticleData['category'], $finalArticleData['author']);

            // Save article
            $this->articleRepository->create($finalArticleData);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to save article', [
                'title' => $articleData['title'] ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
