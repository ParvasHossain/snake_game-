<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    // Tells Laravel it's safe to write to these columns
    protected $fillable = ['username', 'score'];
}
