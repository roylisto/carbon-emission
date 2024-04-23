<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'methodology',
        'country',
        'stars',
        'hcmi_member',
        'room_type',
        'emission_id',
    ];
}
