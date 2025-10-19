<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'content',
        'url',
        'image_url',
        'published_at',
        'source_id',
        'external_id',
        'author_id',
        'category_id',
        'language',
        'country',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class, 'source_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function scopeByCategory($query, string $categorySlug)
    {
        return $query->whereHas('category', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    public function scopeByCategoryId($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByAuthor($query, string $authorName)
    {
        return $query->whereHas('author', function ($q) use ($authorName) {
            $q->where('name', 'like', '%'.$authorName.'%');
        });
    }

    public function scopeByAuthorId($query, int $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    public function scopeBySource($query, string $sourceEnum)
    {
        return $query->whereHas('source', function ($q) use ($sourceEnum) {
            $q->where('source_enum', $sourceEnum);
        });
    }

    public function scopeSearch($query, string $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', '%'.$searchTerm.'%')
                ->orWhere('description', 'like', '%'.$searchTerm.'%')
                ->orWhere('content', 'like', '%'.$searchTerm.'%');
        });
    }

    public function scopeDateRange($query, ?string $from = null, ?string $to = null)
    {
        if ($from) {
            $query->where('published_at', '>=', Carbon::parse($from));
        }

        if ($to) {
            $query->where('published_at', '<=', Carbon::parse($to));
        }

        return $query;
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }
}
