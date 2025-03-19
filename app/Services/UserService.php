<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->userRepository->create($data);
    }

    public function findByEmail($email)
    {
        return $this->userRepository->findByEmail($email);
    }

    public function updateProfile($userId, array $data)
    {
        // Filter out fields that shouldn't be updated
        $data = array_filter($data, function ($key) {
            return !in_array($key, ['email', 'password', 'role']);
        }, ARRAY_FILTER_USE_KEY);

        return $this->userRepository->update($userId, $data);
    }
    
    public function deleteUser($id)
    {
        return $this->userRepository->delete($id);
    }
}