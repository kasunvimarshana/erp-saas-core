<?php

namespace App\Core\Services;

use App\Core\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Core\Exceptions\ServiceException;

/**
 * Base Service Class
 * 
 * Implements business logic layer with transaction management.
 * Orchestrates operations between controllers and repositories.
 * Enforces service-layer-only orchestration for cross-module interactions.
 */
abstract class BaseService
{
    /**
     * @var RepositoryInterface
     */
    protected RepositoryInterface $repository;

    /**
     * BaseService constructor.
     *
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all records with optional pagination.
     *
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getAll(?int $perPage = null)
    {
        try {
            if ($perPage) {
                return $this->repository->paginate($perPage);
            }
            return $this->repository->all();
        } catch (\Exception $e) {
            Log::error('Error fetching records: ' . $e->getMessage());
            throw new ServiceException('Failed to fetch records', 0, $e);
        }
    }

    /**
     * Find a record by ID.
     *
     * @param int $id
     * @return Model|null
     */
    public function findById(int $id): ?Model
    {
        try {
            return $this->repository->find($id);
        } catch (\Exception $e) {
            Log::error("Error finding record {$id}: " . $e->getMessage());
            throw new ServiceException("Failed to find record {$id}", 0, $e);
        }
    }

    /**
     * Create a new record with transaction support.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        DB::beginTransaction();
        
        try {
            $model = $this->repository->create($data);
            DB::commit();
            
            Log::info('Record created successfully', ['id' => $model->id]);
            return $model;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating record: ' . $e->getMessage());
            throw new ServiceException('Failed to create record', 0, $e);
        }
    }

    /**
     * Update a record with transaction support.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        DB::beginTransaction();
        
        try {
            $result = $this->repository->update($id, $data);
            DB::commit();
            
            Log::info("Record {$id} updated successfully");
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating record {$id}: " . $e->getMessage());
            throw new ServiceException("Failed to update record {$id}", 0, $e);
        }
    }

    /**
     * Delete a record with transaction support.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        
        try {
            $result = $this->repository->delete($id);
            DB::commit();
            
            Log::info("Record {$id} deleted successfully");
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting record {$id}: " . $e->getMessage());
            throw new ServiceException("Failed to delete record {$id}", 0, $e);
        }
    }

    /**
     * Execute a callback within a database transaction.
     *
     * @param callable $callback
     * @return mixed
     * @throws ServiceException
     */
    protected function transaction(callable $callback)
    {
        DB::beginTransaction();
        
        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction failed: ' . $e->getMessage());
            throw new ServiceException('Transaction failed', 0, $e);
        }
    }
}
