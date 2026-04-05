<?php

namespace App\Console\Commands;

use App\Models\Location;
use Illuminate\Console\Command;
use App\Services\OpenMeteoService;
use Illuminate\Support\Facades\Log;

class WeatherFetchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:weather-fetch-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '全市町村の天気予報を Open-Meteo から取得し weather_forecasts に UPSERT する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Start Fetching Weather Data...');
        $service = app()->make(OpenMeteoService::class);

        try {

            // 市町村の緯度経度を取得
            $locations = Location::all(['id', 'latitude', 'longitude'])->toArray();
            if (empty($locations)) {
                Log::error('市町村データが見つかりませんでした。');
                return self::FAILURE; // エラーコードを返す
            }

            // 天気予報の取得
            $record = $service->fetchForecast($locations);
            if (is_null($record)) {
                Log::error('天気予報の取得に失敗しました。');
                return self::FAILURE; // エラーコードを返す
            }

            // 取得したデータを整形
            $formattedRecord = $service->formatRecords($record, $locations);

            // 整形されたデータを保存
            $service->saveWeatherLogs($formattedRecord);

        } catch (\Exception $e) {
            Log::error('データの取得に失敗: ' . $e->getMessage());
            return self::FAILURE; // エラーコードを返す
        }

        Log::info('Weather Data Fetching Completed Successfully.');
        Log::info('---------------------------------------------');
        return self::SUCCESS; // 成功コードを返す
    }
}
