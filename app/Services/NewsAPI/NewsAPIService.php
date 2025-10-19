<?php

declare(strict_types=1);

namespace App\Services\NewsAPI;

use App\Models\NewsSource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsAPIService
{
    public function fetchLatestNews(NewsSource $source, array $params = []): Collection
    {
        $defaultParams = [
            'apiKey' => $source->api_key,
            'language' => 'en',
            'sortBy' => 'publishedAt',
            'pageSize' => 20,
            'page' => 1,
        ];

        $requestParams = array_merge($defaultParams, $params);

        try {
            $response = Http::timeout(30)->get($source->base_url . 'top-headlines', $requestParams);

            Log::info($response->body());
            if ($response->successful()) {
                $data = $response->json();
                return $this->transformArticles($data['articles'] ?? [], $source);
            }

            Log::error('NewsAPI error', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return collect();
        } catch (\Exception $e) {
            Log::error('NewsAPI exception', [
                'message' => $e->getMessage(),
                'source_id' => $source->id,
            ]);

            return collect();
        }
    }

    private function transformArticles(array $articles, NewsSource $source): Collection
    {
        return collect($articles)->map(function ($article) use ($source) {
            return [
                'title' => $article['title'] ?? '',
                'description' => $article['description'] ?? null,
                'content' => $article['content'] ?? null,
                'url' => $article['url'] ?? '',
                'image_url' => $article['urlToImage'] ?? null,
                'published_at' => $article['publishedAt'] ?? now(),
                'source_id' => $source->id,
                'external_id' => md5($article['url'] ?? ''),
                'author' => $article['author'] ?? null,
                'category' => $this->extractCategory($article),
                'language' => 'en',
                'country' => 'us',
            ];
        })->filter(function ($article) {
            // Filter out articles with missing essential data
            return !empty($article['title']) && !empty($article['url']);
        });
    }

    private function extractCategory(array $article): ?string
    {
        // NewsAPI doesn't provide categories directly,
        // so we extract from source name or try to infer from title
        $sourceName = $article['source']['name'] ?? null;

        if ($sourceName) {
            // Map common source names to categories
            $categoryMappings = [
                'TechCrunch' => 'Technology',
                'Ars Technica' => 'Technology',
                'The Verge' => 'Technology',
                'Wired' => 'Technology',
                'ESPN' => 'Sports',
                'BBC Sport' => 'Sports',
                'CNN' => 'News',
                'BBC News' => 'News',
                'Reuters' => 'News',
                'Associated Press' => 'News',
                'Forbes' => 'Business',
                'Bloomberg' => 'Business',
                'Wall Street Journal' => 'Business',
                'Financial Times' => 'Business',
            ];

            foreach ($categoryMappings as $source => $category) {
                if (stripos($sourceName, $source) !== false) {
                    return $category;
                }
            }
        }

        return 'General';
    }
}
