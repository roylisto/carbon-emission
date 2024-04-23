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

    /**
     *
     * @param string $methdology
     * @param string $country
     * @param int $stars
     * @param boolean $hcmi_member
     * @param string $room_type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findBy($methdology, $country, $stars, $hcmi_member, $room_type)
    {
        return self::where('methdology', strtoupper($methdology))
            ->where('country', strtoupper($country))
            ->where('stars', strtoupper($stars))
            ->where('hcmi_member', strtoupper($hcmi_member))
            ->where('room_type', strtoupper($room_type))
            ->with('emission')->first();
    }

    public function emission()
    {
        return $this->belongsTo(Emission::class);
    }
}
