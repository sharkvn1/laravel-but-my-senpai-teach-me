<?php

namespace App\Services;

use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Collection;

class UserService
{
    /**
     * Create a new service instance.
     *
     * @param UserRepositoryInterface $userRepository,
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
    ) {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->userRepository->all();
    }

    /**
     * Get detail
     *
     * @return Collection
     */
    public function getDetail(int $id): Collection
    {
        return $this->userRepository->find($id);
    }
}
