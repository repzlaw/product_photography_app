<?php

namespace App\Services;

use App\Models\Film;
use Illuminate\Support\Facades\Hash;

class FilmService
{
    public function __construct()
    {
    }

    public function getAllFilm()
    {
        return Film::join('genres as g', 'g.id', '=', 'films.genre_id')
                ->select('g.name as genre_name', 'films.*')->get();
    }

    public function getFilmWithGenre()
    {
        return Film::all();
    }
    
    

    public function createFilm (array $array)
    {
       return  Film::create($array);
        
    }

    public function updateFilm(Film $Film, array $array)
    {
        $Film->update($array);
    }

}