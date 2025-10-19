<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\NewsAggregationService;
use Illuminate\Console\Command;

class FetchNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch {--source= : Fetch from specific source only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news articles from enabled sources and save to database';

    /**
     * Execute the console command.
     */
    public function handle(NewsAggregationService $aggregationService): int
    {
        $this->info('Starting news fetch...');

        try {
            $results = $aggregationService->fetchFromAllEnabledSources();

            $this->newLine();
            $this->info('📰 News Fetch Results:');
            $this->newLine();

            $totalArticles = 0;
            foreach ($results as $source => $result) {
                $status = $result['success'] ? '✅' : '❌';
                $this->line("$status $source: {$result['message']}");

                if (isset($result['articles_count'])) {
                    $this->line("   📄 Articles saved: {$result['articles_count']}");
                    $totalArticles += $result['articles_count'];
                }

                if (isset($result['total_fetched'])) {
                    $this->line("   📡 Articles fetched: {$result['total_fetched']}");
                }

                if (isset($result['date_range'])) {
                    $this->line("   📅 Date range: {$result['date_range']}");
                }

                $this->newLine();
            }

            $this->info("🎉 Total articles saved: $totalArticles");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ News fetch failed: '.$e->getMessage());
            $this->newLine();
            $this->line('Error details:');
            $this->line($e->getTraceAsString());

            return Command::FAILURE;
        }
    }
}
