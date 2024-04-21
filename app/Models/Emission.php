<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emission extends Model
{
    use HasFactory;

    protected $fillable = [
        'carbon_quantity',
        'carbon_unit',
        'external_reference',
        'type',
        'methodology',
        'distance',
        'distance_unit',
    ];
}
