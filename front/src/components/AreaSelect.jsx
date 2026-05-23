import Select from 'react-select'

const options = [
  { value: 1,  label: '那覇市' },
  { value: 2,  label: '宜野湾市' },
  { value: 3,  label: '石垣市' },
  { value: 4,  label: '浦添市' },
  { value: 5,  label: '名護市' },
  { value: 6,  label: '糸満市' },
  { value: 7,  label: '沖縄市' },
  { value: 8,  label: '豊見城市' },
  { value: 9,  label: 'うるま市' },
  { value: 10, label: '宮古島市' },
  { value: 11, label: '南城市' },
  { value: 12, label: '国頭村' },
  { value: 13, label: '大宜味村' },
  { value: 14, label: '東村' },
  { value: 15, label: '今帰仁村' },
  { value: 16, label: '本部町' },
  { value: 17, label: '恩納村' },
  { value: 18, label: '宜野座村' },
  { value: 19, label: '金武町' },
  { value: 20, label: '伊江村' },
  { value: 21, label: '読谷村' },
  { value: 22, label: '嘉手納町' },
  { value: 23, label: '北谷町' },
  { value: 24, label: '北中城村' },
  { value: 25, label: '中城村' },
  { value: 26, label: '西原町' },
  { value: 27, label: '与那原町' },
  { value: 28, label: '南風原町' },
  { value: 29, label: '渡嘉敷村' },
  { value: 30, label: '座間味村' },
  { value: 31, label: '粟国村' },
  { value: 32, label: '渡名喜村' },
  { value: 33, label: '南大東村' },
  { value: 34, label: '北大東村' },
  { value: 35, label: '伊平屋村' },
  { value: 36, label: '伊是名村' },
  { value: 37, label: '久米島町' },
  { value: 38, label: '八重瀬町' },
  { value: 39, label: '多良間村' },
  { value: 40, label: '竹富町' },
  { value: 41, label: '与那国町' },
]

const AreaSelect = ({ location, onLocationChange }) => {

  const handleChange = (option) => {
    onLocationChange(option);
    localStorage.setItem('weatherArea', JSON.stringify(option));
  }

  return (
    <Select
      value={location}
      onChange={handleChange}
      options={options}
    />
  );
};

export default AreaSelect;