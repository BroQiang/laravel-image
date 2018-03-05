<?php
namespace BroQiang\LaravelImage;

use Intervention\Image\Facades\Image;

class ImageUpload
{
    protected $allowed_ext = ["png", "jpg", "gif", 'jpeg'];

    public function save($file, $folder, $fielPrefix, $maxWidth = false)
    {
        // 格式 uploads/image/$folder/2018/02/02/
        $folderName = '/uploads/image/' . $folder . '/' . date('Y/m/d', time());

        // 将 public 目录的绝对路径和 上面定义的路径拼接到一起
        // 格式: /www/www.broqiang.com/public/uploads/image/$folder/2018/02/02/
        $uploadPath = public_path() . $folderName;

        // 获取文件的后缀名，因图片从剪贴板里黏贴时后缀名为空，所以此处确保后缀一直存在
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'png';

        // 拼接文件名
        $filename = $fielPrefix . '_' . str_random(20) . '.' . $extension;

        // 如果上传的不是图片将终止操作
        if (!in_array($extension, $this->allowed_ext)) {
            return false;
        }

        // 将图片移动到我们的目标存储路径中
        $file->move($uploadPath, $filename);

        if ($maxWidth && $extension != 'gif') {
            $this->reduceSize($uploadPath . '/' . $filename, $maxWidth);
        }

        return [
            'path' => config('app.url') . "$folderName/$filename",
        ];
    }

    /**
     * 将超过尺寸的图片等比缩放
     * @param  [type] $filePath [description]
     * @param  [type] $maxWidth [description]
     * @return [type] [description]
     */
    protected function reduceSize($filePath, $maxWidth)
    {
        // 先实例化，传参是文件的磁盘物理路径
        $image = Image::make($filePath);

        // 进行大小调整的操作
        $image->resize($maxWidth, null, function ($constraint) {

            // 设定宽度是 $maxWidth，高度等比例双方缩放
            $constraint->aspectRatio();

            // 防止裁图时图片尺寸变大
            $constraint->upsize();
        });

        // 对图片修改后进行保存
        $image->save();
    }
}
