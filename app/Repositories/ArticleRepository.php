<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Article;
use App\Repositories\Base\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArticleRepository extends BaseRepository
{
    public function model(): string
    {
        return Article::class;
    }

    /**
     * @param array $request
     * @return LengthAwarePaginator
     */
    public function index(array $request = []): LengthAwarePaginator
    {
        $columnArray = ['id', 'title', 'published_at'];
        $ascArray = ['desc', 'asc'];

        $query = $this->model->with(['source', 'category', 'author']);

        // Search functionality
        if (isset($request['search']) && $request['search']) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request['search'].'%')
                    ->orWhere('description', 'like', '%'.$request['search'].'%');
            });
        }

        // Filter by category ID
        if (isset($request['category']) && $request['category']) {
            $query->where('category_id', $request['category']);
        }

        // Filter by author ID
        if (isset($request['author']) && $request['author']) {
            $query->where('author_id', $request['author']);
        }

        // Filter by source ID
        if (isset($request['source']) && $request['source']) {
            $query->where('source_id', $request['source']);
        }

        // Sorting
        if (isset($request['column']) && isset($request['dir']) &&
            in_array($request['column'], $columnArray) &&
            in_array($request['dir'], $ascArray)) {
            $query = $query->orderBy($request['column'], $request['dir']);
        } else {
            $query = $query->orderBy('published_at', 'desc');
        }

        $pageSize = 10;
        if (isset($request['length']) && $request['length']) {
            $pageSize = $request['length'];
        }

        return $query->paginate($pageSize);
    }
}
