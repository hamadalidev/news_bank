<?php

declare(strict_types=1);

namespace App\Services\NewsAPI;

use App\Models\NewsSource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsDataIOService
{
    /**
     * @param NewsSource $source
     * @param array $params
     * @return Collection
     */
    public function fetchLatestNews(NewsSource $source, array $params = []): Collection
    {
        $defaultParams = [
            'apikey' => $source->api_key,
            'language' => 'en',
            'prioritydomain' => 'top',
        ];

        $requestParams = array_merge($defaultParams, $params);

        try {
            $url = $source->base_url.'latest';
            Log::info('NewsData.io API Request', [
                'url' => $url,
                'params' => $requestParams,
                'full_url' => $url.'?'.http_build_query($requestParams),
            ]);

            $response = Http::timeout(30)->get($url, $requestParams);

            if ($response->successful()) {
                $data = $response->json();

                return $this->transformArticles($data['results'] ?? [], $source);
            }

            Log::error('NewsData.io API error', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return collect();
        } catch (\Exception $e) {
            Log::error('NewsData.io API exception', [
                'message' => $e->getMessage(),
                'source_id' => $source->id,
            ]);

            return collect();
        }
    }

    /**
     * @param array $articles
     * @param NewsSource $source
     * @return Collection
     */
    private function transformArticles(array $articles, NewsSource $source): Collection
    {
        return collect($articles)->map(function ($article) use ($source) {
            return [
                'title' => $article['title'] ?? '',
                'description' => $article['description'] ?? null,
                'content' => $article['content'] ?? null,
                'url' => $article['link'] ?? '',
                'image_url' => $article['image_url'] ?? null,
                'published_at' => $article['pubDate'] ?? now(),
                'source_id' => $source->id,
                'external_id' => $article['article_id'] ?? md5($article['link'] ?? ''),
                'author' => $this->extractAuthor($article),
                'category' => $this->extractCategory($article),
                'language' => $article['language'] ?? 'en',
                'country' => $article['country'][0] ?? null,
            ];
        });
    }

    /**
     * @param array $article
     * @return string|null
     */
    private function extractAuthor(array $article): ?string
    {
        if (! empty($article['creator'])) {
            return is_array($article['creator']) ? implode(', ', $article['creator']) : $article['creator'];
        }

        return $article['source_id'] ?? null;
    }

    /**
     * @param array $article
     * @return string|null
     */
    private function extractCategory(array $article): ?string
    {
        if (! empty($article['category'])) {
            return is_array($article['category']) ? $article['category'][0] : $article['category'];
        }

        return null;
    }
}
