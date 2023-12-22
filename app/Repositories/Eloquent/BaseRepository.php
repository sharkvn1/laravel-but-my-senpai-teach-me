<?php

namespace App\Repositories\Eloquent;

use App\Constants\Constants;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\EloquentRepositoryInterface;
use App\Traits\LoggerTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * BaseRepository Class
 */
class BaseRepository implements EloquentRepositoryInterface
{
    use LoggerTrait;

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
     *  Get all
     *
     * @param array $columns
     * @param bool $withTrashed
     * @return Collection
     */
    public function all(array $columns = ['*'], bool $withTrashed = false): Collection
    {
        $query = $this->model->query();
        if ($withTrashed && method_exists($this->model, 'bootSoftDeletes')) {
            $query->withTrashed();
        }
        return $query->get($columns);
    }

    /**
     * Create
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    /**
     * Insert
     *
     * @param array $records
     * @return bool
     */
    public function insert(array $records): bool
    {
        return $this->model->insert($records);
    }

    /**
     * Update
     *
     * @param $id
     * @param array $data
     * @return Model|null
     */
    public function update($id, array $data): ?Model
    {
        $entity = $this->find($id);
        if (is_null($entity)) {
            return null;
        }
        $entity->update($data);
        return $entity;
    }

    /**
     * Delete
     *
     * @param $id
     * @return bool
     */
    public function delete($id): bool
    {
        $entity = $this->find($id);
        if ($entity) {
            $entity->delete();
            return true;
        }
        return false;
    }

    /**
     * Delete all
     *
     * @param $id
     * @return bool
     */
    public function deleteAll(): bool
    {
        return $this->model->query()->delete();
    }

    /**
     * Find
     *
     * @param $id
     * @param array $relations
     * @return Model|null
     */
    public function find($id, array $relations = []): ?Model
    {
        $queryBuilder = $this->model;
        if (!empty($relations)) {
            $queryBuilder = $queryBuilder->with($relations);
        }
        return $queryBuilder->find($id);
    }

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
    ): LengthAwarePaginator {
        $query = $this->model->orderByDesc('id');
        if (!empty($relations)) {
            $query->with($relations);
        }
        $total = $query->count();
        $items = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();
        return new LengthAwarePaginator($items, $total, $perPage, $page);
    }

    /**
     * Insert or ignore
     *
     * @param array $data
     * @return int
     */
    public function insertOrIgnore(array $data): int
    {
        $tableName = $this->model->getTable();
        return DB::table($tableName)->insertOrIgnore($data);
    }

    /**
     * Update or create record
     *
     * @param array $attributes
     * @param array $value
     * @return Model|null
     */
    public function updateOrCreate(array $attributes = [], array $value = []): ?Model
    {
        try {
            return $this->model->updateOrCreate($attributes, $value);
        } catch (QueryException $exception) {
            $this->writeLog($exception);
            return null;
        }
    }


    /**
     * Upsert
     *
     * @param array $data
     * @param array $identifiers
     * @param array $updateColumns
     * @param bool  $restoreDeleted
     * @return bool
     */
    public function upsert(array $data, array $identifiers, array $updateColumns = [], bool $restoreDeleted = false): bool
    {
        if (method_exists($this, 'bootSoftDeletes') && $restoreDeleted) {
            $data = array_map(function ($item) {
                $item['deleted_at'] = null;
                return $item;
            }, $data);

            $updateColumns[] = 'deleted_at';
        }
        return $this->model->upsert($data, $identifiers, $updateColumns);
    }

    /**
     * Upsert or delete
     *
     * @param array $data
     * @param array $identifiers
     * @param array $updateColumns
     * @return bool
     */
    public function upsertOrDelete(array $data, array $identifiers, array $updateColumns = []): bool
    {
        if (method_exists($this, 'bootSoftDeletes')) {
            $data = array_map(function ($item) {
                $item['deleted_at'] = null;
                return $item;
            }, $data);

            $updateColumns[] = 'deleted_at';
        }

        $this->upsert($data, $identifiers, $updateColumns);

        $query = $this->model->newQuery();

        foreach ($data as $item) {
            $query->orWhere(function ($query) use ($item, $identifiers) {
                $query->where(array_intersect_key($item, array_flip($identifiers)));
            });
        }

        $excludedIds = $query->get()->pluck('id')->toArray();

        return $this->model->whereNotIn('id', $excludedIds)->delete();
    }

    /**
     * Find by condition
     *
     * @param array $relations
     * @param array $queries [where, whereIn, whereNotIn, whereBetween, whereDate]
     * @return Builder
     */
    public function queryByCondition(
        array $relations = [],
        array $queries = []
    ): Builder {
        [
            $whereInQueries,
            $whereNotInQueries,
            $whereBetweenQueries,
            $whereDateQueries,
        ] = [
            Arr::pull($queries, Constants::QUERY_WHERE_IN, []),
            Arr::pull($queries, Constants::QUERY_WHERE_NOT_IN, []),
            Arr::pull($queries, Constants::QUERY_WHERE_BETWEEN, []),
            Arr::pull($queries, Constants::QUERY_WHERE_DATE, []),
        ];

        $query = $this->model->query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        if (!empty($queries)) {
            $query->where($queries);
        }

        if (!empty($whereInQueries)) {
            foreach ($whereInQueries as $column => $whereInQuery) {
                $query->whereIn($column, $whereInQuery);
            }
        }

        if (!empty($whereNotInQueries)) {
            foreach ($whereNotInQueries as $column => $whereNotInQuery) {
                $query->whereNotIn($column, $whereNotInQuery);
            }
        }

        if (!empty($whereBetweenQueries)) {
            foreach ($whereBetweenQueries as $column => $whereBetweenQuery) {
                $query->whereBetween($column, $whereBetweenQuery);
            }
        }

        if (!empty($whereDateQueries)) {
            foreach ($whereDateQueries as $column => $whereDateQuery) {
                $query->whereDate($column, $whereDateQuery);
            }
        }

        return $query;
    }

    /**
     * Find by condition
     *
     * @param array $relations
     * @param array $queries [where, whereIn, whereNotIn, whereBetween, whereDate]
     * @return Collection
     */
    public function findByCondition(array $relations = [], array $queries = []): Collection
    {
        return $this->queryByCondition($relations, $queries)->get();
    }

    /**
     * Find one by condition
     *
     * @param array $relations
     * @param array $queries [where, whereIn, whereNotIn, whereBetween, whereDate]
     * @return Model|null
     */
    public function findOneByCondition(array $relations = [], array $queries = []): ?Model
    {
        return $this->queryByCondition($relations, $queries)->first();
    }

    /**
     * Update with condition
     *
     * @param array $data
     * @param array $queries [where, whereIn, whereNotIn, whereBetween, whereDate]
     * @param bool  $withoutTimestamps
     * @return bool
     */
    public function updateWithCondition(array $data, array $queries = [], bool $withoutTimestamps = false): bool
    {
        if ($withoutTimestamps) {
            $data['updated_at'] = DB::raw('updated_at');
        }
        return $this->queryByCondition([], $queries)->update($data);
    }

    /**
     * Delete by condition
     *
     * @param array $queries [where, whereIn, whereNotIn, whereBetween, whereDate]
     * @return bool
     */
    public function deleteByCondition(array $queries = []): bool
    {
        return $this->queryByCondition([], $queries)->delete();
    }
}
