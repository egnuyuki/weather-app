import { Settings, X } from "lucide-react";
import { useState } from "react";
import AreaSelect from "./AreaSelect";

const Setting = () => {
  const [isOpen, setIsOpen] = useState(false);

  const handleOpen = () => {
    setIsOpen(true);
    console.log("設定画面を開く");
  };

  const handleClose = () => {
    setIsOpen(false);
    console.log("設定画面を閉じる");
  };

  console.log(isOpen);

  return isOpen ? (
    <div className="fixed p-2 inset-0 bg-white bg-opacity-50 flex flex-col items-center justify-center gap-4">
      {/* 設定画面の内容 */}
      <div
        className="fixed top-4 right-4 flex items-center gap-2 cursor-pointer bg-stone-100 shadow p-2 rounded-full w-fit"
        onClick={handleClose}
      >
        <X className="text-stone-600" />
      </div>
      <div className="bg-white p-4 rounded shadow w-full max-w-md">
        <h2 className="text-xl font-bold mb-4">地域設定</h2>
        <AreaSelect />
      </div>
      <button onClick={handleClose} className="px-4 py-2 bg-stone-100 rounded">
        閉じる
      </button>
    </div>
  ) : (
    <div
      className="fixed top-4 right-4 flex items-center gap-2 cursor-pointer bg-stone-100 shadow p-2 rounded-full w-fit"
      onClick={handleOpen}
    >
      <Settings className="text-stone-600" />
    </div>
  );
};

export default Setting;
