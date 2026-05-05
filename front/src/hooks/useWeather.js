import { useQuery } from '@tanstack/react-query'
import { fetchCurrentWeather } from '../api/weatherApi'

export const useWeather = (location) => {
  const locationId = location || getLocalArea()?.value || 1  // デフォルトは 1
  return useQuery({
    queryKey: ['weather', locationId],  // locationId が変わると再フェッチ
    queryFn: () => fetchCurrentWeather(locationId),
  })
}

const getLocalArea = () => {
  const area = localStorage.getItem('weatherArea')
  return area ? JSON.parse(area) : null
}