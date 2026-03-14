<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            ['name' => '那覇市', 'latitude' => 26.2124, 'longitude' => 127.6809],
            ['name' => '宜野湾市', 'latitude' => 26.2816, 'longitude' => 127.7788],
            ['name' => '石垣市', 'latitude' => 24.3406, 'longitude' => 124.1557],
            ['name' => '浦添市', 'latitude' => 26.2464, 'longitude' => 127.7247],
            ['name' => '名護市', 'latitude' => 26.592, 'longitude' => 127.9774],
            ['name' => '糸満市', 'latitude' => 26.1261, 'longitude' => 127.6692],
            ['name' => '沖縄市', 'latitude' => 26.3356, 'longitude' => 127.8014],
            ['name' => '豊見城市', 'latitude' => 26.1741, 'longitude' => 127.6744],
            ['name' => 'うるま市', 'latitude' => 26.3758, 'longitude' => 127.8597],
            ['name' => '宮古島市', 'latitude' => 24.7969, 'longitude' => 125.2842],
            ['name' => '南城市', 'latitude' => 26.1489, 'longitude' => 127.7739],
            ['name' => '国頭村', 'latitude' => 26.757, 'longitude' => 128.1764],
            ['name' => '大宜味村', 'latitude' => 26.6908, 'longitude' => 128.1147],
            ['name' => '東村', 'latitude' => 26.5989, 'longitude' => 128.1614],
            ['name' => '今帰仁村', 'latitude' => 26.6844, 'longitude' => 127.9625],
            ['name' => '本部町', 'latitude' => 26.6536, 'longitude' => 127.8864],
            ['name' => '恩納村', 'latitude' => 26.5144, 'longitude' => 127.8694],
            ['name' => '宜野座村', 'latitude' => 26.4717, 'longitude' => 127.9575],
            ['name' => '金武町', 'latitude' => 26.455, 'longitude' => 127.9319],
            ['name' => '伊江村', 'latitude' => 26.7175, 'longitude' => 127.7911],
            ['name' => '読谷村', 'latitude' => 26.4025, 'longitude' => 127.7442],
            ['name' => '嘉手納町', 'latitude' => 26.3508, 'longitude' => 127.7569],
            ['name' => '北谷町', 'latitude' => 26.3117, 'longitude' => 127.7606],
            ['name' => '北中城村', 'latitude' => 26.3022, 'longitude' => 127.7939],
            ['name' => '中城村', 'latitude' => 26.2731, 'longitude' => 127.8058],
            ['name' => '西原町', 'latitude' => 26.2367, 'longitude' => 127.7567],
            ['name' => '与那原町', 'latitude' => 26.2044, 'longitude' => 127.7569],
            ['name' => '南風原町', 'latitude' => 26.1961, 'longitude' => 127.7264],
            ['name' => '渡嘉敷村', 'latitude' => 26.2014, 'longitude' => 127.3625],
            ['name' => '座間味村', 'latitude' => 26.2269, 'longitude' => 127.3031],
            ['name' => '粟国村', 'latitude' => 26.5822, 'longitude' => 127.2344],
            ['name' => '渡名喜村', 'latitude' => 26.3703, 'longitude' => 127.1428],
            ['name' => '南大東村', 'latitude' => 25.8458, 'longitude' => 131.2467],
            ['name' => '北大東村', 'latitude' => 25.9469, 'longitude' => 131.3006],
            ['name' => '伊平屋村', 'latitude' => 27.04, 'longitude' => 127.9719],
            ['name' => '伊是名村', 'latitude' => 26.9281, 'longitude' => 127.9428],
            ['name' => '久米島町', 'latitude' => 26.335, 'longitude' => 126.764],
            ['name' => '八重瀬町', 'latitude' => 26.1553, 'longitude' => 127.7222],
            ['name' => '多良間村', 'latitude' => 24.6547, 'longitude' => 124.6975],
            ['name' => '竹富町', 'latitude' => 24.3236, 'longitude' => 123.8569],
            ['name' => '与那国町', 'latitude' => 24.4681, 'longitude' => 122.9869],
        ];

        Location::truncate(); // 既存データを削除してリセット

        foreach ($locations as $loc) {
            Location::create($loc);
        }
    }
}
