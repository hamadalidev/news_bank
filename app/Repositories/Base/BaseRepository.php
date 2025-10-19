<?php

declare(strict_types=1);

namespace App\Repositories\Base;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->makeModel();
    }

    abstract public function model(): string;

    public function makeModel(): Model
    {
        $model = app($this->model());

        if (! $model instanceof Model) {
            throw new InvalidArgumentException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    public function all(array $criteria = [], array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): Collection
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                [$operator, $val] = $value;
                $query->where($key, $operator, $val);
            } else {
                $query->where($key, $value);
            }
        }

        if (! empty($whereIn)) {
            foreach ($whereIn as $column => $values) {
                $query->whereIn($column, $values);
            }
        }

        if (! empty($relations)) {
            $query->with($relations);
        }

        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }

        return $query->get($columns);
    }

    public function find(int|string $id, array $columns = ['*'], array $relations = []): ?Model
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id, $columns);
    }

    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Model
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->findOrFail($id, $columns);
    }

    public function findBy(string $attribute, mixed $value, array $columns = ['*'], array $relations = []): ?Model
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->where($attribute, $value)->first($columns);
    }

    public function findWhere(array $criteria, array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): Collection
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                [$operator, $val] = $value;
                $query->where($key, $operator, $val);
            } else {
                $query->where($key, $value);
            }
        }

        if (! empty($whereIn)) {
            foreach ($whereIn as $column => $values) {
                $query->whereIn($column, $values);
            }
        }

        if (! empty($relations)) {
            $query->with($relations);
        }

        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }

        return $query->get($columns);
    }

    public function get(array $criteria = [], array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): Collection
    {
        return $this->all($criteria, $whereIn, $columns, $relations, $orderBy, $orderDirection);
    }

    public function first(array $criteria = [], array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): ?Model
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                [$operator, $val] = $value;
                $query->where($key, $operator, $val);
            } else {
                $query->where($key, $value);
            }
        }

        if (! empty($whereIn)) {
            foreach ($whereIn as $column => $values) {
                $query->whereIn($column, $values);
            }
        }

        if (! empty($relations)) {
            $query->with($relations);
        }

        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }

        return $query->first($columns);
    }

    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    public function createMany(array $data): Collection
    {
        $models = collect();

        foreach ($data as $attributes) {
            $models->push($this->create($attributes));
        }

        return $models;
    }

    public function update(int|string $id, array $attributes): bool
    {
        $model = $this->findOrFail($id);

        return $model->update($attributes);
    }

    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function delete(int|string $id): bool
    {
        $model = $this->findOrFail($id);

        return $model->delete();
    }

    public function exists(array $criteria = [], array $whereIn = []): bool
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                [$operator, $val] = $value;
                $query->where($key, $operator, $val);
            } else {
                $query->where($key, $value);
            }
        }

        if (! empty($whereIn)) {
            foreach ($whereIn as $column => $values) {
                $query->whereIn($column, $values);
            }
        }

        return $query->exists();
    }

    public function count(array $criteria = [], array $whereIn = []): int
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                [$operator, $val] = $value;
                $query->where($key, $operator, $val);
            } else {
                $query->where($key, $value);
            }
        }

        if (! empty($whereIn)) {
            foreach ($whereIn as $column => $values) {
                $query->whereIn($column, $values);
            }
        }

        return $query->count();
    }

    public function paginate(int $perPage = 15, array $criteria = [], array $whereIn = [], array $columns = ['*'], array $relations = [], ?string $orderBy = 'created_at', string $orderDirection = 'desc'): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                [$operator, $val] = $value;
                $query->where($key, $operator, $val);
            } else {
                $query->where($key, $value);
            }
        }

        if (! empty($whereIn)) {
            foreach ($whereIn as $column => $values) {
                $query->whereIn($column, $values);
            }
        }

        if (! empty($relations)) {
            $query->with($relations);
        }

        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }

        return $query->paginate($perPage, $columns);
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function deleteWhereIn(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function whereCount(array $where): int
    {
        return $this->model->where($where)->count();
    }
}
