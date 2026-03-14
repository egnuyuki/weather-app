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
        Schema::create('locations', function (Blueprint $table) {
            $table->comment('沖縄県41市町村の地点情報を格納するテーブル');
            $table->id();
            $table->string('name')->unique();           // 地名（那覇市 など）UNIQUE
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->smallInteger('elevation')->nullable(); // 標高（m）API取得後に自動更新
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
