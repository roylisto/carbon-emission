<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'methodology',
        'number_of_travelers',
        'origin',
        'destination',
        'emission_id',
    ];
}
