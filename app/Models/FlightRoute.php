<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'methodology',
        'origin',
        'destination',
        'emission_id',
    ];

    /**
     * Find flight routes based on origin, destination and methodology
     *
     * @param string $origin
     * @param string $destination
     * @param string $methdology
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findByOriginDestinationMethodology($origin, $destination, $methodology = null)
    {
        $query = self::where('origin', $origin)
            ->where('destination', $destination)
            ->where('methodology', $methodology)
            ->with('emission');

        return $query->with('emission')->first();
    }

    public function emission()
    {
        return $this->belongsTo(Emission::class);
    }
}
