<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherLog extends Model
{
    protected $fillable = [
        'location_id',
        'forecast_time',
        'forecast_fetched_at',
        'temperature_2m',
        'precipitation',
        'wind_speed_10m',
        'relative_humidity',
        'weather_code',
        'actual_fetched_at',
        'actual_temperature',
        'actual_precipitation',
        'actual_wind_speed',
        'actual_humidity',
        'actual_weather_code'
    ];

    // キャスト設定
    // 有効桁数設定のためにdecimalは文字列でキャストし、アクセサで小数点以下2桁に丸める

    protected $casts = [
        'forecast_time' => 'datetime',
        'forecast_fetched_at' => 'datetime',
        'actual_fetched_at' => 'datetime',
        'temperature_2m' => 'decimal:2',
        'precipitation' => 'decimal:2',
        'wind_speed_10m' => 'decimal:2',
        'actual_temperature' => 'decimal:2',
        'actual_precipitation' => 'decimal:2',
        'actual_wind_speed' => 'decimal:2',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
