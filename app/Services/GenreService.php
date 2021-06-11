<?php

namespace App\Services;

use App\Models\Genre;
use Illuminate\Support\Facades\Hash;

class GenreService
{
    public function __construct()
    {
    }

    public function getAllGenre()
    {
        return Genre::all();
    }
    

    public function createGenre (array $array)
    {
        Genre::create($array);
        
    }

    public function updateGenre(Genre $genre, array $array)
    {
        $genre->update($array);
    }

}