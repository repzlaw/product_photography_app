<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct()
    {
    }

    public function getAllUser()
    {
        return User::all();
    }
    

    public function createGenre (array $array)
    {
        User::create($array);
        
    }

    public function updateUser(User $user, array $array)
    {
        $user->update($array);
    }

}