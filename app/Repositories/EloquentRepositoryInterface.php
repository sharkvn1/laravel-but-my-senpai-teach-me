<?php

namespace App\Repositories;

use App\Constants\Constants;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * EloquentRepositoryInterface Class
 */
interface EloquentRepositoryInterface
{
    /**
     *  Get all
     *
     * @param array $columns
     * @param bool $withTrashed
     * @return Collection
     */
    public function all(array $columns = ['*'], bool $withTrashed = false): Collection;

    /**
     * Create
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * Insert
     *
     * @param array $records
     * @return bool
     */
    public function insert(array $records): bool;

    /**
     * Update
     *
     * @param $id
     * @param array $data
     * @return Model|null
     */
    public function update($id, array $data): ?Model;

    /**
     * Delete
     *
     * @param $id
     * @return bool
     */
    public function delete($id): bool;

    /**
     * Delete all
     *
     * @param $id
     * @return bool
     */
    public function deleteAll(): bool;

    /**
     * Find
     *
     * @param $id
     * @param array $relations
     * @return Model|null
     */
    public function find($id, array $relations = []): ?Model;

    /**
     * Paginate
     *
     * @param array $relations
     * @param int $page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(
        array $relations = [],
        int $page = Constants::DEFAULT_PAGE,
        int $perPage = Constants::DEFAULT_PER_PAGE
    ): LengthAwarePaginator;

    /**
     * Insert or ignore
     *
     * @param array $data
     * @return int
     */
    public function insertOrIgnore(array $data): int;

    /**
     * Update or create record
     *
     * @param array $attributes
     * @param array $value
     * @return Model|null
     */
    public function updateOrCreate(array $attributes = [], array $value = []): ?Model;

    /**
     * Upsert
     *
     * @param array $data
     * @param array $identifiers
     * @param array $updateColumns
     * @param bool  $restoreDeleted
     * @return bool
     */
    public function upsert(array $data, array $identifiers, array $updateColumns = [], bool $restoreDeleted = false): bool;

    /**
     * Upsert or delete
     *
     * @param array $data
     * @param array $identifiers
     * @param array $updateColumns
     * @return bool
     */
    public function upsertOrDelete(array $data, array $identifiers, array $updateColumns = []): bool;

    /**
     * Find by condition
     *
     * @param array $relations
     * @param array $queries
     * @return Builder
     */
    public function queryByCondition(array $relations = [], array $queries = []): Builder;

    /**
     * Find by condition
     *
     * @param array $relations
     * @param array $queries
     * @return Collection
     */
    public function findByCondition(array $relations = [], array $queries = []): Collection;

    /**
     * Find one by condition
     *
     * @param array $relations
     * @param array $queries
     * @return Model|null
     */
    public function findOneByCondition(array $relations = [], array $queries = []): ?Model;

    /**
     * Update with condition
     *
     * @param array $data
     * @param array $queries
     * @param bool  $withoutTimestamps
     * @return bool
     */
    public function updateWithCondition(array $data, array $queries = [], bool $withoutTimestamps = false): bool;

    /**
     * Delete by condition
     *
     * @param array $queries
     * @return bool
     */
    public function deleteByCondition(array $queries): bool;
}
