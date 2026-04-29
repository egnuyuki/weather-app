import { useQuery } from '@tanstack/react-query'
import { fetchCurrentWeather } from '../api/weatherApi'

export const useWeather = (locationId = 1) => {
  return useQuery({
    queryKey: ['weather', locationId],  // locationId が変わると再フェッチ
    queryFn: () => fetchCurrentWeather(locationId),
  })
}