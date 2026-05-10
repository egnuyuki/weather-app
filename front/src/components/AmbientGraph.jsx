import {
  AreaChart,
  Area,
  XAxis,
  YAxis,
  Tooltip,
  ResponsiveContainer,
} from 'recharts'

const AmbientGraph = ({ graphData, textColor }) => {
  if (!graphData || graphData.length === 0) return null

  const timeLabel = graphData.filter((_, i) => i % 4 === 0).map((d) => d.time)

  // カスタムドットコンポーネント
    const CustomDot = (props) => {
        const { cx, cy, index } = props;
        if (index % 4 === 0 && index !== 0) { // 4時間ごとにドットを表示（データは30分ごとなので、8時間ごとに表示する場合は index % 16 === 0）
            return (
                <circle cx={cx} cy={cy} r={3} stroke="white" strokeWidth={1} fill="#8884d8" />
            );
        }
        return null; // 条件に合わない場合は何も表示しない
    };

  return (
    <div className="fixed inset-0 z-10" style={{ outline: 'none' }}>
      <ResponsiveContainer width="100%" height="100%">
        <AreaChart
          data={graphData}
          margin={{ top: 0, right: 0, left: 0, bottom: 0 }}
          onContextMenu={(_, e) => e.preventDefault()}
        >
        {/* <CartesianGrid strokeDasharray="4 4" stroke="#e5e7eb" /> */}
          {/* 軸は非表示（アンビエントデザイン） */}
          <XAxis dataKey="time" ticks={timeLabel} hide />
          {/* <YAxis domain={[0, 'auto']} hide /> */}
          <YAxis domain={[dataMin => Math.floor(dataMin * 0.9), dataMax => Math.floor(dataMax * 1.1)]} hide/> // 最小値の10%下を基点

          {/* ツールチップも非表示 */}
          <Tooltip active={true} contentStyle={{ backgroundColor: '#f5f5f5', borderColor: '#ccc' ,color: '#333'}} itemStyle={{ color: '#333' }} formatter={(value) => [`${value}°C`, null]} />

          <defs>
            <linearGradient id="tempGradient" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%"  stopColor={textColor} stopOpacity={0.25} />
              <stop offset="95%" stopColor={textColor} stopOpacity={0.02} />
            </linearGradient>
          </defs>

          <Area
            type="monotone"
            dataKey="temp"
            stroke={textColor}
            strokeWidth={1.5}
            strokeOpacity={0.5}
            fill="url(#tempGradient)"
            isAnimationActive={true}   // 再レンダリング時のアニメーションをOFF
            dot={(props) => <CustomDot {...props} />}  // カスタムドットを使用
          >
            {/* <LabelList 
                dataKey="temp" 
                position="top" 
                offset={10}
                content={(props) => <RenderCustomizedLabel {...props} />}
            /> */}
          </Area>
        </AreaChart>
      </ResponsiveContainer>
    </div>
  )
}

export default AmbientGraph