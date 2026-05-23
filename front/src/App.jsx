import { useWeather } from "./hooks/useWeather";
import BackgroundCanvas from "./components/BackgroundCanvas";
import Setting from "./components/Setting";
import AmbientGraph from "./components/AmbientGraph";
import { getTheme } from "./components/weatherTheme";
import WeatherInfo from "./components/WeatherInfo";
import { useState } from "react";

function App() {
  const { data, isPending, isError } = useWeather();

  const [location, setLocation] = useState(() => {
    const area = localStorage.getItem('weatherArea')
    return area ? JSON.parse(area) : null
  });

  const hour  = new Date().getHours()
  const theme = getTheme(data?.weather_code ?? 0, hour)

  return (
    <>
      {/* Layer 0: 背景（データ取得前はデフォルト表示） */}
      <BackgroundCanvas weatherCode={data?.weather_code ?? 0} />


      {/* Layer 1: 情報表示 */}
      <WeatherInfo data={data ?? null} textColor={theme.textColor} />

      {/* Layer 2: 気温グラフ */}
      {data && (
        <AmbientGraph
          graphData={data.graph_data}
          textColor={theme.textColor}
        />
      )}
      {/* 設定パネル（最前面固定） */}
      <Setting location={location} onLocationChange={setLocation} textColor={theme.textColor} />

      {/* メインコンテンツ */}
      <main className="relative">
        {isPending && <p>読み込み中...</p>}
        {isError && <p>データの取得に失敗しました</p>}
      </main>
    </>
  );
}

export default App;
