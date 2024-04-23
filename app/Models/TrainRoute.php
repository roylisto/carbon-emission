<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'methodology',
        'origin',
        'destination',
        'train_type',
        'emission_id',
    ];

    /**
     * Find train routes based on origin, destination, methodology, train_type
     *
     * @param string $origin
     * @param string $destination
     * @param string $methdology
     * @param string $train_type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findBy($origin, $destination, $methodology, $train_type)
    {
        return self::where('origin', strtoupper($origin))
            ->where('destination', strtoupper($destination))
            ->where('methodology', strtoupper($methodology))
            ->where('train_type', strtoupper($train_type))
            ->with('emission')->first();
    }

    public function emission()
    {
        return $this->belongsTo(Emission::class);
    }
}
