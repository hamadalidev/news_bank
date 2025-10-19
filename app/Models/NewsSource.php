<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NewsSourceEnum;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class NewsSource extends BaseModel
{
    use HasFactory;
    protected $fillable = [
        'source_enum',
        'name',
        'api_key',
        'status',
        'base_url',
        'rate_limit_per_hour',
        'last_fetched_at',
    ];

    protected $casts = [
        'source_enum' => NewsSourceEnum::class,
        'last_fetched_at' => 'datetime',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'source_id');
    }


    protected function apiKey(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => decrypt($value),
            set: fn ($value) => encrypt($value),
        );
    }

    public function scopeEnabled($query)
    {
        return $query->where('status', 'enabled');
    }

    public function scopeDisabled($query)
    {
        return $query->where('status', 'disabled');
    }
}
