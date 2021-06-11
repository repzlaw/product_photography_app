<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotographRequest extends Model
{
    use HasFactory;
    protected $fillable = ['product_name','product_owner_id','facility_name'];
}
