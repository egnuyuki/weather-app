import { useEffect, useRef } from 'react'
import { gsap } from 'gsap'
import { getTheme } from './weatherTheme'

const BackgroundCanvas = ({ weatherCode }) => {
  const bgRef = useRef(null)
  const currentThemeRef = useRef(null)

  useEffect(() => {
    console.log('天気コード:', weatherCode)
    console.log(bgRef)
    console.log(currentThemeRef)
    if (bgRef.current === null) return

    const hour  = new Date().getHours()
    const theme = getTheme(weatherCode, hour)

    // 初回は即時適用
    if (currentThemeRef.current === null) {
        console.log('初回テーマ適用:', theme)
      gsap.set(bgRef.current, {
        background: `linear-gradient(to bottom right, ${theme.from}, ${theme.to})`,
      })
      currentThemeRef.current = theme
      return
    }

    // 天気・時間帯が変わったときはアニメーションで遷移
    // API返答までに時間がかかることもあるので、常にアニメーションで遷移させる
    gsap.to(bgRef.current, {
      duration: 2.0,
      ease: 'power2.inOut',
      background: `linear-gradient(to bottom right, ${theme.from}, ${theme.to})`,
    })
    currentThemeRef.current = theme

  }, [weatherCode])  // weatherCode が変わるたびに実行

  return (
    <div
      ref={bgRef}
      className="fixed inset-0 -z-10"
    />
  )
}

export default BackgroundCanvas