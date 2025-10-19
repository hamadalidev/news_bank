<?php

declare(strict_types=1);

namespace App\Services\NewsAPI;

use App\Models\NewsSource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianAPIService
{
    public function fetchLatestNews(NewsSource $source, array $params = []): Collection
    {
        $defaultParams = [
            'api-key' => $source->api_key,
            'show-fields' => 'headline,byline,body,thumbnail',
            'page-size' => 20,
            'order-by' => 'newest',
            'page' => 1,
            'format' => 'json',
        ];

        $requestParams = array_merge($defaultParams, $params);

        try {
            $response = Http::timeout(30)->get($source->base_url . 'search', $requestParams);

            if ($response->successful()) {
                $data = $response->json();
                return $this->transformArticles($data['response']['results'] ?? [], $source);
            }

            Log::error('Guardian API error', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return collect();
        } catch (\Exception $e) {
            Log::error('Guardian API exception', [
                'message' => $e->getMessage(),
                'source_id' => $source->id,
            ]);

            return collect();
        }
    }

    private function transformArticles(array $articles, NewsSource $source): Collection
    {
        return collect($articles)->map(function ($article) use ($source) {
            $fields = $article['fields'] ?? [];
            
            return [
                'title' => $fields['headline'] ?? $article['webTitle'] ?? '',
                'description' => $this->extractDescription($fields['body'] ?? ''),
                'content' => $fields['body'] ?? null,
                'url' => $article['webUrl'] ?? '',
                'image_url' => $fields['thumbnail'] ?? null,
                'published_at' => $article['webPublicationDate'] ?? now(),
                'source_id' => $source->id,
                'external_id' => $article['id'] ?? md5($article['webUrl'] ?? ''),
                'author' => $fields['byline'] ?? null,
                'category' => $this->extractCategory($article),
                'language' => 'en',
                'country' => 'gb',
            ];
        });
    }

    private function extractDescription(string $body): ?string
    {
        if (empty($body)) {
            return null;
        }

        // Strip HTML tags and get first 200 characters
        $plainText = strip_tags($body);
        return strlen($plainText) > 200 ? substr($plainText, 0, 200) . '...' : $plainText;
    }

    private function extractCategory(array $article): ?string
    {
        return $article['sectionName'] ?? $article['pillarName'] ?? null;
    }
}