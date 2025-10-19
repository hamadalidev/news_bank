<?php

declare(strict_types=1);

namespace App\Repositories\Base;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function all(array $criteria = [], array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): Collection;

    public function get(array $criteria = [], array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): Collection;

    public function first(array $criteria = [], array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): ?Model;

    public function find(int|string $id, array $columns = ['*'], array $relations = []): ?Model;

    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Model;

    public function findBy(string $attribute, mixed $value, array $columns = ['*'], array $relations = []): ?Model;

    public function findWhere(array $criteria, array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): Collection;

    public function create(array $attributes): Model;

    public function createMany(array $data): Collection;

    public function update(int|string $id, array $attributes): bool;

    public function updateOrCreate(array $attributes, array $values = []): Model;

    public function delete(int|string $id): bool;

    public function exists(array $criteria = [], array $whereIn = []): bool;

    public function count(array $criteria = [], array $whereIn = []): int;

    public function paginate(int $perPage = 15, array $criteria = [], array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): LengthAwarePaginator;

    public function query(): Builder;

    public function getModel(): Model;

    public function makeModel(): Model;
}
