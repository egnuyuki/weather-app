<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weather_logs', function (Blueprint $table) {
            $table->comment('天気予報・実績を格納（1レコード = 1地点 × 1時刻）');
            $table->id();
            $table->integer('location_id')->comment('locationsテーブルのキー');
            $table->dateTime('forecast_time')->comment('予報対象日時（JST）');

            // 予報カラム（毎日UPSERTで最新値に上書き）
            $table->dateTime('forecast_fetched_at')->nullable()->comment('予報取得日時。NULL=未取得');
            $table->decimal('temperature_2m', 5, 2)->nullable()->comment('予報気温(℃)');
            $table->decimal('precipitation', 6, 2)->nullable()->comment('予報降水量(mm)');
            $table->decimal('wind_speed_10m', 6, 2)->nullable()->comment('予報風速(m/s)');
            $table->unsignedTinyInteger('relative_humidity')->nullable()->comment('予報相対湿度(%)');
            $table->smallInteger('weather_code')->nullable()->comment('予報WMOコード');

            // 実績カラム（forecast_time経過後に補完）
            $table->dateTime('actual_fetched_at')->nullable()->comment('実績取得日時。NULL=未取得');
            $table->decimal('actual_temperature', 5, 2)->nullable()->comment('実績気温(℃)');
            $table->decimal('actual_precipitation', 6, 2)->nullable()->comment('実績降水量(mm)');
            $table->decimal('actual_wind_speed', 6, 2)->nullable()->comment('実績風速(m/s)');
            $table->unsignedTinyInteger('actual_humidity')->nullable()->comment('実績相対湿度(%)');
            $table->smallInteger('actual_weather_code')->nullable()->comment('実績WMOコード');

            $table->timestamps();

            // インデックス
            $table->unique(['location_id', 'forecast_time']);
            $table->index('forecast_time');
            $table->index(['actual_fetched_at', 'forecast_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_logs');
    }
};
