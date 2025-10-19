<?php

declare(strict_types=1);

namespace App\Enums;

enum NewsSourceEnum: string
{
    case NEWSDATA_IO = 'newsdata_io';
    case GUARDIAN = 'guardian';
    case NEW_YORK_TIMES = 'new_york_times';
    case NEWSAPI = 'newsapi';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::NEWSDATA_IO => 'NewsData.io',
            self::GUARDIAN => 'The Guardian',
            self::NEW_YORK_TIMES => 'New York Times',
            self::NEWSAPI => 'NewsAPI',
        };
    }

    public function getBaseUrl(): string
    {
        return match ($this) {
            self::NEWSDATA_IO => 'https://newsdata.io/api/1/',
            self::GUARDIAN => 'https://content.guardianapis.com/',
            self::NEW_YORK_TIMES => 'https://api.nytimes.com/svc/',
            self::NEWSAPI => 'https://newsapi.org/v2/',
        };
    }

    public function getDefaultRateLimit(): int
    {
        return match ($this) {
            self::NEWSDATA_IO => 200,
            self::GUARDIAN => 500,
            self::NEW_YORK_TIMES => 1000,
            self::NEWSAPI => 1000,
        };
    }
}
