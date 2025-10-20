<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\NewsSourceEnum;
use App\Models\NewsSource;
use Illuminate\Database\Seeder;

class NewsSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'source_enum' => NewsSourceEnum::NEWSDATA_IO,
                'name' => NewsSourceEnum::NEWSDATA_IO->getDisplayName(),
                'api_key' => 'pub_07aaea0264ea47af86479a220aaeacab', //todo we will replace with orignal keys and UI.
                'status' => 'enabled',
                'base_url' => NewsSourceEnum::NEWSDATA_IO->getBaseUrl(),
                'rate_limit_per_hour' => NewsSourceEnum::NEWSDATA_IO->getDefaultRateLimit(),
            ],
            [
                'source_enum' => NewsSourceEnum::GUARDIAN,
                'name' => NewsSourceEnum::GUARDIAN->getDisplayName(),
                'api_key' => '57bd490b-2e0d-41e0-97dd-564a9f008efb',
                'status' => 'enabled',
                'base_url' => NewsSourceEnum::GUARDIAN->getBaseUrl(),
                'rate_limit_per_hour' => NewsSourceEnum::GUARDIAN->getDefaultRateLimit(),
            ],
            [
                'source_enum' => NewsSourceEnum::NEWSAPI,
                'name' => NewsSourceEnum::NEWSAPI->getDisplayName(),
                'api_key' => '9a0f6972fde048f99271691e2ad81234',
                'status' => 'enabled',
                'base_url' => NewsSourceEnum::NEWSAPI->getBaseUrl(),
                'rate_limit_per_hour' => NewsSourceEnum::NEWSAPI->getDefaultRateLimit(),
            ],
        ];

        foreach ($sources as $sourceData) {
            NewsSource::updateOrCreate(
                ['source_enum' => $sourceData['source_enum']],
                $sourceData
            );
        }

        $this->command->info('News sources seeded successfully!');
        $this->command->info('- NewsData.io: enabled');
        $this->command->info('- The Guardian: enabled');
        $this->command->info('- NewsAPI: enabled');
    }
}
