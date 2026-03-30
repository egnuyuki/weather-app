<?php

namespace App\Services;

use App\Models\Location;
use App\Models\WeatherLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenMeteoService
{
    private string $baseUrl = 'https://api.open-meteo.com/v1/forecast';

    public function testFetch()
    {
        $locations = Location::all(['id', 'latitude', 'longitude'])->toArray();
        $result = $this->fetchForecast($locations);
        // 結果をJSONファイルに保存（デバッグ用）
        file_put_contents(storage_path('app/open_meteo_test.json'), json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $formattedRecords = $this->formatRecords($result, $locations);
        // フォーマットされたレコードをJSONファイルに保存（デバッグ用）
        file_put_contents(storage_path('app/open_meteo_formatted.json'), json_encode($formattedRecords, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function fetchForecast(array $locations): ?array
    {

        $latitudes  = array_column($locations, 'latitude');
        $longitudes = array_column($locations, 'longitude');
        $maxRetries = 3;

        $params = [
            'latitude'      => implode(',', $latitudes),
            'longitude'     => implode(',', $longitudes),
            'hourly'        => 'temperature_2m,precipitation,wind_speed_10m,relative_humidity_2m,weathercode',
            'wind_speed_unit' => 'ms',
            'cell_selection' => 'land',
            'timezone'      => 'auto',  // 各地点の現地時間に自動調整
            'forecast_days' => 7,
        ];

        try {
            return $this->attemptFetch($params, $maxRetries);
        } catch (\Exception $e) {
            Log::error('Open-Meteo API request failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function attemptFetch(array $params, int $maxRetries): ?array
    {

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $response = Http::timeout(10)->get($this->baseUrl, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("Open-Meteo API attempt {$attempt} failed", [
                'status' => $response->status(),
            ]);

            if ($attempt < $maxRetries) {
                sleep($attempt * 2);
            }
        }
        return null;
    }

    /**
     * APIレスポンスを weather_forecasts の UPSERT 用配列に変換する。
     *
     * @param  array  $responseData  Open-Meteo のレスポンス（単一地点は連想配列、複数地点は配列）
     * @param  array  $locations
     * @return array
     */
    private function formatRecords(array $responseData, array $locations): array
    {
        $records = [];
        $fetchedAt = now()->toDateTimeString();

        foreach ($responseData as $index => $locationData) {
            $locationId = $locations[$index]['id'];
            $hourly = $locationData['hourly'];
            $times = $hourly['time'];
            $count = count($times);

            for ($i = 0; $i < $count; $i++) {
                $dateData = [
                    'location_id' => $locationId,
                    'forecast_time' => $times[$i],
                    'forecast_fetched_at' => $fetchedAt,
                    'temperature_2m' => $hourly['temperature_2m'][$i] ?? null,
                    'precipitation' => $hourly['precipitation'][$i] ?? null,
                    'wind_speed_10m' => $hourly['wind_speed_10m'][$i] ?? null,
                    'relative_humidity' => $hourly['relative_humidity_2m'][$i] ?? null,
                    'weather_code' => $hourly['weathercode'][$i] ?? null,
                ];
                array_push($records, $dateData);
            }
        }

        return $records;
    }
}
