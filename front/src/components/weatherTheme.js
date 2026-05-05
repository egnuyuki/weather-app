// 時間帯を4つに分類
const getTimeSlot = (hour) => {
  if (hour >= 5 && hour < 10) return 'morning'   // 朝  5〜9時
  if (hour >= 10 && hour < 17) return 'day'        // 昼 10〜16時
  if (hour >= 17 && hour < 20) return 'evening'    // 夕 17〜19時
  return 'night'                                    // 夜 20〜4時
}

// 天気状態を分類
const getWeatherType = (weatherCode) => {
  if (weatherCode === 0) // 晴れ
    return 'clear'
  if (weatherCode <= 3) // 曇り
    return 'partlyCloudy'
  if (weatherCode <= 48) // 霧
    return 'foggy'
  if (weatherCode <= 57) // 霧雨
    return 'drizzle'
  if (weatherCode <= 67) // 雨
    return 'rainy'
  if (weatherCode <= 77) // 雪
    return 'snowy'
  if (weatherCode <= 86) // 雨のち曇り
    return 'showery'
  return 'stormy'
}

// [天気][時間帯] → { from, to, textColor }
const themes = {
  clear: {
    morning: { from: '#FFB347', to: '#87CEEB', textColor: '#1A1A2E' },  // オレンジ→水色
    day: { from: '#87CEEB', to: '#4A90D9', textColor: '#1A1A2E' },  // 水色→青
    evening: { from: '#FF6B6B', to: '#FF8C42', textColor: '#1A1A2E' },  // 夕焼け
    night: { from: '#0D1B4B', to: '#1A1A6E', textColor: '#E8E8FF' },  // 深夜ブルー
  },
  partlyCloudy: {
    morning: { from: '#FFD580', to: '#B0C4DE', textColor: '#1A1A2E' },
    day: { from: '#B0C4DE', to: '#7A9BBF', textColor: '#1A1A2E' },
    evening: { from: '#C07A5A', to: '#8B6F8F', textColor: '#F0E8FF' },
    night: { from: '#1A2A4A', to: '#2A3A5A', textColor: '#E8E8FF' },
  },
  foggy: {
    morning: { from: '#C8C8C8', to: '#A0A8B0', textColor: '#2A2A2A' },
    day: { from: '#B0B0B0', to: '#787878', textColor: '#1A1A1A' },
    evening: { from: '#908080', to: '#605858', textColor: '#F0E8E8' },
    night: { from: '#303030', to: '#404040', textColor: '#D0D0D0' },
  },
  drizzle: {
    morning: { from: '#8AA8C8', to: '#6080A0', textColor: '#F0F4FF' },
    day: { from: '#7A9BBF', to: '#4A6A8F', textColor: '#F0F4FF' },
    evening: { from: '#506080', to: '#304060', textColor: '#E0E8FF' },
    night: { from: '#1A2A3A', to: '#0A1A2A', textColor: '#C0D0E0' },
  },
  rainy: {
    morning: { from: '#5A7A9A', to: '#3A5A7A', textColor: '#E8F0FF' },
    day: { from: '#4A6A8F', to: '#2C4A6E', textColor: '#E8F0FF' },
    evening: { from: '#3A4A6A', to: '#1A2A4A', textColor: '#D0D8F0' },
    night: { from: '#0A1A2A', to: '#050F1A', textColor: '#A0B0C8' },
  },
  snowy: {
    morning: { from: '#E8F4FF', to: '#C0D8F0', textColor: '#1A2A3A' },
    day: { from: '#E0EFFF', to: '#B0C8E0', textColor: '#1A2A3A' },
    evening: { from: '#C0C8D8', to: '#9098A8', textColor: '#1A2A3A' },
    night: { from: '#1A2030', to: '#2A3040', textColor: '#D0E0F0' },
  },
  showery: {
    morning: { from: '#6080A8', to: '#405878', textColor: '#E8F0FF' },
    day: { from: '#3A6A9F', to: '#1A3A6F', textColor: '#E8F0FF' },
    evening: { from: '#2A3A60', to: '#1A2040', textColor: '#D0D8F0' },
    night: { from: '#080F20', to: '#101828', textColor: '#90A0C0' },
  },
  stormy: {
    morning: { from: '#5A4A6A', to: '#3A2A4A', textColor: '#E8E0FF' },
    day: { from: '#4A3A6F', to: '#1A1A3F', textColor: '#E8E0FF' },
    evening: { from: '#2A1A3A', to: '#100A20', textColor: '#D0C8F0' },
    night: { from: '#080510', to: '#0F0818', textColor: '#A090C0' },
  },
}

// 外部から使う関数はこれだけ
export const getTheme = (weatherCode, hour) => {
  const weatherType = getWeatherType(weatherCode)
  const timeSlot = getTimeSlot(hour)
  return themes[weatherType][timeSlot]
}