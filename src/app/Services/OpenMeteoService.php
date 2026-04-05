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
        // file_put_contents(storage_path('app/open_meteo_test.json'), json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $formattedRecords = $this->formatRecords($result, $locations);
        // フォーマットされたレコードをJSONファイルに保存（デバッグ用）
        // file_put_contents(storage_path('app/open_meteo_formatted.json'), json_encode($formattedRecords, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->saveWeatherLogs($formattedRecords);
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
    public function formatRecords(array $responseData, array $locations): array
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

    /**
     * WHO コードを日本語の天気説明に変換する
     *
     * @param int|null $weatherCode
     * @return string
     */
    public function getWeatherDescription(?int $weatherCode): string
    {
        $descriptions = [
            0 => '晴れ',
            1 => '主に晴れ',
            2 => '部分的に曇り',
            3 => '曇り',
            45 => '霧雨',
            48 => '氷霧雨',
            51 => '弱い降水',
            53 => '中程度の降水',
            55 => '強い降水',
            56 => '弱い氷点下の降水',
            57 => '強い氷点下の降水',
            61 => '弱い雨',
            63 => '中程度の雨',
            65 => '強い雨',
            66 => '弱い氷点下の雨',
            67 => '強い氷点下の雨',
            71 => '弱い雪',
            73 => '中程度の雪',
            75 => '強い雪',
            77 => '雪片',
            80 => '弱いにわか雨',
            81 => '中程度のにわか雨',
            82 => '強いにわか雨',
            85 => '弱いにわか雪',
            86 => '強いにわか雪',
        ];

        return $descriptions[$weatherCode] ?? '';
    }

    /**
     * 取得データを weather_logs テーブルに保存する
     *
     * @param array $records フォーマットされたレコードの配列
     * @return void
     */
    public function saveWeatherLogs(array $records): void
    {
        foreach ($records as $record) {
            WeatherLog::updateOrCreate(
                [
                    'location_id' => $record['location_id'],
                    'forecast_time' => $record['forecast_time'],
                ],
                [
                    'forecast_fetched_at' => $record['forecast_fetched_at'],
                    'temperature_2m' => $record['temperature_2m'],
                    'precipitation' => $record['precipitation'],
                    'wind_speed_10m' => $record['wind_speed_10m'],
                    'relative_humidity' => $record['relative_humidity'],
                    'weather_code' => $record['weather_code'],
                ]
            );
        }
    }
}
