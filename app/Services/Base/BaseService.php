<?php

declare(strict_types=1);

namespace App\Services\Base;

use App\Repositories\Base\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    protected BaseRepository $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function all(array $criteria = [], array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): Collection
    {
        return $this->repository->all($criteria, $whereIn, $columns, $relations, $orderBy, $orderDirection);
    }

    public function get(array $criteria = [], array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): Collection
    {
        return $this->repository->get($criteria, $whereIn, $columns, $relations, $orderBy, $orderDirection);
    }

    public function first(array $criteria = [], array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): ?Model
    {
        return $this->repository->first($criteria, $whereIn, $columns, $relations, $orderBy, $orderDirection);
    }

    public function find(int|string $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->repository->find($id, $columns, $relations);
    }

    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Model
    {
        return $this->repository->findOrFail($id, $columns, $relations);
    }

    public function findBy(string $attribute, mixed $value, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->repository->findBy($attribute, $value, $columns, $relations);
    }

    public function findWhere(array $criteria, array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): Collection
    {
        return $this->repository->findWhere($criteria, $whereIn, $columns, $relations, $orderBy, $orderDirection);
    }

    public function create(array $attributes): Model
    {
        return $this->repository->create($attributes);
    }

    public function createMany(array $data): Collection
    {
        return $this->repository->createMany($data);
    }

    public function update(int|string $id, array $attributes): bool
    {
        return $this->repository->update($id, $attributes);
    }

    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->repository->updateOrCreate($attributes, $values);
    }

    public function delete(int|string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function exists(array $criteria = [], array $whereIn = []): bool
    {
        return $this->repository->exists($criteria, $whereIn);
    }

    public function count(array $criteria = [], array $whereIn = []): int
    {
        return $this->repository->count($criteria, $whereIn);
    }

    public function paginate(int $perPage = 15, array $criteria = [], array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $criteria, $whereIn, $columns, $relations, $orderBy, $orderDirection);
    }

    public function query()
    {
        return $this->repository->query();
    }

    public function deleteWhereIn(array $ids): int
    {
        return $this->repository->deleteWhereIn($ids);
    }
}
