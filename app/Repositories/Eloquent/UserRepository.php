<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }
}
