<?php

namespace App\Models;

use App\Weather\Forecast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Last valid forecast fetched for a location.
 */
#[Fillable(['provider', 'location', 'data', 'fetched_at'])]
class WeatherForecast extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'fetched_at' => 'immutable_datetime',
        ];
    }

    public function forecast(): Forecast
    {
        return Forecast::fromArray($this->data);
    }
}
