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
    public static function findBy($methodology, $country = null, $stars = null, $hcmi_member = null, $room_type = null)
    {
        return self::query()
            ->where('methodology', strtoupper($methodology))
            ->when($country, function ($query) use ($country) {
                return $query->where('country', strtoupper($country));
            })
            ->when($stars !== null, function ($query) use ($stars) {
                return $query->where('stars', $stars);
            })
            ->when($hcmi_member !== null, function ($query) use ($hcmi_member) {
                return $query->where('hcmi_member', $hcmi_member);
            })
            ->when($room_type, function ($query) use ($room_type) {
                return $query->where('room_type', strtoupper($room_type));
            })
            ->with('emission')
            ->first();
    }



    public function emission()
    {
        return $this->belongsTo(Emission::class);
    }
}
