import axios from 'axios'

const apiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000,
})

export const fetchCurrentWeather = async (locationId = 1) => {
  const { data } = await apiClient.get('/weather/current', {
    params: { location_id: locationId },
  })
  console.log('APIからの天気データ:', data)
  return data
}