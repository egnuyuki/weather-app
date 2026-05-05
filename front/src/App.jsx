import { useWeather } from "./hooks/useWeather";
import BackgroundCanvas from "./components/BackgroundCanvas";
import Setting from "./components/Setting";

function App() {
  const { data, isPending, isError } = useWeather();

  return (
    <>
      {/* Layer 0: 背景（データ取得前はデフォルト表示） */}
      <BackgroundCanvas weatherCode={data?.weather_code ?? 0} />

      {/* 設定パネル（最前面固定） */}
      <Setting />

      {/* メインコンテンツ */}
      <main className="relative min-h-screen">
        {isPending && <p>読み込み中...</p>}
        {isError && <p>データの取得に失敗しました</p>}
        {data && (
          <div>
            <h1>{data.location}</h1>
            <p>現在気温: {data.current_temp}°C</p>
            <p>
              最高: {data.max_temp}°C / 最低: {data.min_temp}°C
            </p>
            <pre>{JSON.stringify(data.graph_data?.slice(0, 3), null, 2)}</pre>
          </div>
        )}
      </main>
    </>
  );
}

export default App;
