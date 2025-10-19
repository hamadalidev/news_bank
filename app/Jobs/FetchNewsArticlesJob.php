<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\NewsAggregationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchNewsArticlesJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(NewsAggregationService $aggregationService): void
    {
        Log::info('Starting news fetch job');

        try {
            $results = $aggregationService->fetchFromAllEnabledSources();
            
            $totalArticles = array_sum(array_column($results, 'articles_count'));
            
            Log::info('News fetch job completed', [
                'total_articles_saved' => $totalArticles,
                'sources_processed' => count($results),
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('News fetch job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('News fetch job failed permanently', [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}
