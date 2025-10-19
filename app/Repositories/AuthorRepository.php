<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Author;
use App\Repositories\Base\BaseRepository;

class AuthorRepository extends BaseRepository
{
    public function model(): string
    {
        return Author::class;
    }


    public function index(array $request = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $columnArray = ['id', 'name'];
        $ascArray = ['desc', 'asc'];

        $query = $this->model->query();
        
        if (isset($request['search']) && $request['search']) {
            $query->where('name', 'like', '%'.$request['search'].'%');
        }

        if (isset($request['column']) && isset($request['dir']) && 
            in_array($request['column'], $columnArray) && 
            in_array($request['dir'], $ascArray)) {
            $query = $query->orderBy($request['column'], $request['dir']);
        } else {
            $query = $query->orderBy('id', 'desc');
        }

        $pageSize = 10;
        if (isset($request['length']) && $request['length']) {
            $pageSize = $request['length'];
        }

        return $query->paginate($pageSize);
    }
}