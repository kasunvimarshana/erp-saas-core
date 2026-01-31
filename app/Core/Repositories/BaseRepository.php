<?php

namespace App\Core\Repositories;

use App\Core\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Base Repository Implementation
 * 
 * Implements the Repository pattern for data access abstraction.
 * Provides consistent CRUD operations with automatic tenant scoping.
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected Model $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->select($columns)->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->model->select($columns)->findOrFail($id);
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->select($columns)->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $attributes): bool
    {
        $model = $this->findOrFail($id);
        return $model->update($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $model = $this->findOrFail($id);
        return $model->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function findWhere(array $criteria, array $columns = ['*']): Collection
    {
        $query = $this->model->select($columns);
        
        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }
        
        return $query->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findWhereFirst(array $criteria, array $columns = ['*']): ?Model
    {
        $query = $this->model->select($columns);
        
        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }
        
        return $query->first();
    }

    /**
     * Get the model instance.
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Set the model instance.
     *
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;
        return $this;
    }
}
