const WeatherInfo = ({ data, textColor }) => {

    if (!data) return null

    const label       = getWeatherLabel(data.weather_code)
    const lastUpdated = new Date(data.last_updated).toLocaleTimeString('ja-JP', {
        hour:   '2-digit',
        minute: '2-digit',
    })

    // console.log('WeatherInfo rendered with data:', data)
    // console.log('Weather label:', label)
    // console.log('Last updated:', lastUpdated)

    return (
        <div
            className="relative z-10 flex flex-col justify-end min-h-screen p-8 pb-16"
            style={{ color: textColor }}
        >
        {/* 場所名・天気状態 */}
        <div className="mb-2 flex items-center gap-3">
            <span className="text-lg opacity-80">{data.location}</span>
            <span className="text-sm opacity-60">{label}</span>
        </div>

        {/* 現在気温（メイン） */}
        <div className="flex items-end gap-2 leading-none">
            <span className="text-8xl font-thin">
            {data.current_temp}
            </span>
            <span className="text-3xl font-thin mb-3 opacity-70">°C</span>
        </div>

        {/* 最高・最低気温 */}
        <div className="mt-3 flex gap-4 text-sm opacity-70">
            <span>最高 {data.max_temp}°C</span>
            <span>最低 {data.min_temp}°C</span>
        </div>

        {/* 最終更新時刻 */}
        <div className="mt-2 text-xs opacity-40">
            最終更新 {lastUpdated}
        </div>
        </div>
    )
}

const getWeatherLabel = (code) => {
  if (code === 0)
    return '快晴'
  if (code <= 3)
    return '晴れ'
  if (code <= 48)
    return '霧'
  if (code <= 57)
    return '霧雨'
  if (code <= 67)
    return '雨'
  if (code <= 77)
    return '雪'
  if (code <= 86)
    return 'にわか雨'
  return '雷雨'
}

export default WeatherInfo