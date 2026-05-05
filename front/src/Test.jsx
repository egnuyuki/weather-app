import { useWeather } from './hooks/useWeather'

function Test() {
  const { data, isPending, isError } = useWeather(2)

  if (isPending) return <p>読み込み中...</p>
  if (isError)   return <p>データの取得に失敗しました</p>

  return (
    <div>
      <h1>{data.location}</h1>
      <p>現在気温: {data.current_temp}°C</p>
      <p>最高: {data.max_temp}°C / 最低: {data.min_temp}°C</p>
      <pre>{JSON.stringify(data.graph_data?.slice(0, 3), null, 2)}</pre>
    </div>
  )
}

export default Test