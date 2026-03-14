# 天気予報DB設計（Open-Meteo × 沖縄41市町村）

## 概要

Open-Meteo API を使って沖縄県41市町村の天気予報を取得・蓄積するシステムのDB設計と実装方針。
予報値は毎日上書き更新し、予報時刻が過去になった後に実績値（Historical API）で同一レコードを補完する。
予報・実績が1レコードに共存するため、差分比較も可能。

---

## データフロー

```
毎朝6時  fetch_weather.php  → 7日分の予報を UPSERT（予報カラムのみ更新）
毎朝7時  fetch_actual.php   → 前日以前の実績未取得レコードを Historical API で補完
                              （actual_* カラムに書き込み）
```

---

## テーブル設計

### municipalities（市町村マスタ）

沖縄県41市町村の名前・緯度・経度を管理。標高はAPI初回取得時に自動保存。

| カラム | 型 | 説明 |
|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | PRIMARY KEY |
| name | VARCHAR(50) NOT NULL | 市町村名　UNIQUE |
| latitude | DECIMAL(9,6) NOT NULL | 緯度 |
| longitude | DECIMAL(9,6) NOT NULL | 経度 |
| elevation | SMALLINT NULL | 標高(m)　API取得後に自動更新 |
| created_at | TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | 更新日時 |

---

### weather_forecasts（天気予報・実績）

1レコード = 1地点 × 1時刻。
`UNIQUE KEY (municipality_id, forecast_time)` により重複なし。
予報カラムと実績カラムが同居する構造。

#### 管理カラム

| カラム | 型 | 説明 |
|---|---|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT | PRIMARY KEY |
| municipality_id | INT UNSIGNED | 市町村ID（FK） |
| forecast_time | DATETIME | 予報対象日時（JST） |

#### 予報カラム（毎日 UPSERT で最新値に上書き）

| カラム | 型 | 説明 |
|---|---|---|
| forecast_fetched_at | DATETIME | 予報取得日時 |
| temperature_2m | DECIMAL(5,2) | 予報気温(℃) |
| precipitation | DECIMAL(6,2) | 予報降水量(mm) |
| wind_speed_10m | DECIMAL(6,2) | 予報風速(m/s) |
| relative_humidity | TINYINT UNSIGNED | 予報相対湿度(%) |
| weather_code | SMALLINT | 予報WMOコード |

#### 実績カラム（forecast_time 経過後に Historical API で補完）

| カラム | 型 | 説明 |
|---|---|---|
| actual_fetched_at | DATETIME NULL | 実績取得日時。**NULL = 未取得** |
| actual_temperature | DECIMAL(5,2) | 実績気温(℃) |
| actual_precipitation | DECIMAL(6,2) | 実績降水量(mm) |
| actual_wind_speed | DECIMAL(6,2) | 実績風速(m/s) |
| actual_humidity | TINYINT UNSIGNED | 実績相対湿度(%) |
| actual_weather_code | SMALLINT | 実績WMOコード |

---

## UPSERT の動作まとめ

- `fetch_weather.php` の UPSERT では `actual_*` カラムを UPDATE 対象に含めない  
  → 実績値が先に書き込まれていても、予報の再取得で上書きされない
- `fetch_actual.php` では `actual_fetched_at IS NULL` の行のみ UPDATE  
  → 二重更新が発生しない

---

## 差分比較クエリ例

予報と実績が同一レコードに共存するため、以下で差分比較が可能。

```sql
SELECT
    m.name,
    wf.forecast_time,
    wf.temperature_2m        AS forecast_temp,
    wf.actual_temperature    AS actual_temp,
    ROUND(wf.actual_temperature - wf.temperature_2m, 2) AS temp_error,
    wf.precipitation         AS forecast_precip,
    wf.actual_precipitation  AS actual_precip
FROM weather_forecasts wf
JOIN municipalities m ON m.id = wf.municipality_id
WHERE wf.actual_fetched_at IS NOT NULL
ORDER BY ABS(wf.actual_temperature - wf.temperature_2m) DESC;
```

---

## インデックス

| インデックス | カラム | 目的 |
|---|---|---|
| UNIQUE KEY uq_municipality_forecast | (municipality_id, forecast_time) | 重複防止・UPSERTキー |
| INDEX idx_wf_forecast_time | (forecast_time) | 時刻での絞り込み |
| INDEX idx_wf_actual_null | (actual_fetched_at, forecast_time) | 実績未取得レコードの高速検索 |

---

## ファイル構成

| ファイル | 説明 |
|---|---|
| schema.sql | テーブル定義（municipalities / weather_forecasts） |
| fetch_weather.php | 予報取得・UPSERT（cron 毎朝6時） |
| fetch_actual.php | 実績取得・UPDATE（cron 毎朝7時） |

---

## cron 設定例

```cron
0 6 * * * /usr/bin/php /path/to/fetch_weather.php >> /var/log/weather_fetch.log 2>&1
0 7 * * * /usr/bin/php /path/to/fetch_actual.php  >> /var/log/weather_actual.log 2>&1
```

## 市町村データ
1. 那覇市	26.2124	127.6809
2. 宜野湾市	26.2816	127.7788
3. 石垣市	24.3406	124.1557
4. 浦添市	26.2464	127.7247
5. 名護市	26.592	127.9774
6. 糸満市	26.1261	127.6692
7. 沖縄市	26.3356	127.8014
8. 豊見城市	26.1741	127.6744
9. うるま市	26.3758	127.8597
10. 宮古島市	24.7969	125.2842
11. 南城市	26.1489	127.7739
12. 国頭村	26.757	128.1764
13. 大宜味村	26.6908	128.1147
14. 東村	26.5989	128.1614
15. 今帰仁村	26.6844	127.9625
16. 本部町	26.6536	127.8864
17. 恩納村	26.5144	127.8694
18. 宜野座村	26.4717	127.9575
19. 金武町	26.455	127.9319
20. 伊江村	26.7175	127.7911
21. 読谷村	26.4025	127.7442
22. 嘉手納町	26.3508	127.7569
23. 北谷町	26.3117	127.7606
24. 北中城村	26.3022	127.7939
25. 中城村	26.2731	127.8058
26. 西原町	26.2367	127.7567
27. 与那原町	26.2044	127.7569
28. 南風原町	26.1961	127.7264
29. 渡嘉敷村	26.2014	127.3625
30. 座間味村	26.2269	127.3031
31. 粟国村	26.5822	127.2344
32. 渡名喜村	26.3703	127.1428
33. 南大東村	25.8458	131.2467
34. 北大東村	25.9469	131.3006
35. 伊平屋村	27.04	127.9719
36. 伊平名村	26.9281	127.9428
37. 久米島町	26.335	126.764
38. 八重瀬町	26.1553	127.7222
39. 多良間村	24.6547	124.6975
40. 竹富町	24.3236	123.8569
41. 与那国町	24.4681	122.9869