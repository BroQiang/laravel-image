<?php
namespace BroQiang\LaravelImage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class BroImage
{
    protected $allowed_ext;

    protected $root_folder;

    protected $folder;

    protected $file_prefix = '';

    protected $max_width = false;

    protected $upload_type = 'local';

    protected $date_format = 'Y/m/d';

    protected $max_size;

    protected $is_url;

    protected $url;

    protected $filename;

    protected $extension;

    protected $qiniu_access_key;

    protected $qiniu_secret_key;

    protected $qiniu_bucket;

    protected $qiniu_domain;

    protected $random_length = 20;

    public function __construct()
    {
        $this->initConfig();
    }

    public function initConfig()
    {
        $configs = config('bro_image', []);
        $this->setConfig($configs);
        if (array_key_exists('root_folder', $configs)) {
            $this->root_folder = '/' . trim($configs['root_folder'], '/') . '/';
        }
    }

    public function upload($file, $config = [])
    {
        // 将自定义配置初始化
        $this->setConfig($config);
        switch ($this->upload_type) {
            case 'local':

                return $this->uploadLocal($file);
                break;
            case 'qiniu':
                return $this->uploadQiniu($file);
                break;
        }
    }

    /**
     * 上传文件到本地
     * @param  [type] $file [description]
     * @return [type] [description]
     */
    public function uploadLocal($file)
    {
        // 格式 uploads/image/$folder/2018/02/02/
        $folderName = $this->root_folder . $this->folder . '/' . date($this->date_format, time());

        // 将 public 目录的绝对路径和 上面定义的路径拼接到一起
        // 格式: /www/www.broqiang.com/public/uploads/image/$folder/2018/02/02/
        $uploadPath = public_path() . $folderName;

        // 拼接文件名
        $filename = $this->getFilename($file);

        // 将图片移动到我们的目标存储路径中
        $file->move($uploadPath, $filename);

        if ($this->max_width && $this->extension != 'gif') {
            $this->reduceSize($uploadPath . '/' . $filename, $this->max_width);
        }

        return $this->getUrlPath($folderName, $filename);
    }

    /**
     * 上传到七牛云
     * 详细的内容见官方文档：https://developer.qiniu.com/kodo/sdk/1241/php
     * @param  [type] $file [description]
     * @return [type] [description]
     */
    public function uploadQiniu($file)
    {
        $info = [
            'success' => 1,
            'message' => '上传成功',
            'url'     => '',
        ];

        $filename = $this->getFilename($file);

        $auth  = new Auth($this->qiniu_access_key, $this->qiniu_secret_key);
        $token = $auth->uploadToken($this->qiniu_bucket);

        $upManager = new UploadManager();

        list($code, $error) = $upManager->putFile($token, $filename, $file);

        if ($error) {
            $info['success'] = 0;
            $info['message'] = $error->message();
            return $info;
        }

        $info['url'] = $this->qiniu_domain . '/' . $filename;
        return $info;
    }

    public function validateUpload(Request $request, $field)
    {
        $rules = [
            $field => 'bail|required|file|mimes:' . implode($this->allowed_ext, ',') . '|max:' . $this->max_size,
        ];

        $messages = [
            $field . '.file'  => '文件上传失败',
            $field . '.mimes' => '上传失败，文件类型不允许，请上传常规的图片 ' . implode($this->allowed_ext, ',') . '文件',
            $field . '.max'   => '上传失败，文件过大，文件大小不得超出 ' . $this->max_size . ' KB。',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $fail = [
                'success' => 0,
                'message' => $validator->errors()->first($field),
                'url'     => '',
            ];
            return $fail;
        }
    }

    public function setConfig($config)
    {
        if (isset($config) && count($config)) {
            foreach ($config as $key => $value) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    public function getUrlPath($folderName, $filename)
    {
        $info = [
            'success' => 1,
            'message' => '上传成功',
            'url'     => '',
        ];

        if ($this->is_url) {
            $url         = $this->url ?: config('app.url');
            $info['url'] = $url . $folderName . '/' . $filename;
            return $info;
        }

        $info['url'] = $folderName . '/' . $filename;

        return $info;
    }

    /**
     * 组合文件名
     * @return [type] [description]
     */
    public function getFilename($file)
    {
        // 获取文件的后缀名，因图片从剪贴板里黏贴时后缀名为空，所以此处确保后缀一直存在
        $this->extension = strtolower($file->getClientOriginalExtension()) ?: 'png';

        $randomName = str_random($this->random_length);

        if ($this->filename) {
            return $this->filename . $this->extension;
        }

        if ($this->file_prefix) {
            return $this->file_prefix . '_' . $randomName . '.' . $this->extension;
        }

        return $randomName . '.' . $this->extension;
    }

    public function setFilename($filename = null)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * 将超过尺寸的图片等比缩放
     * @param  [type] $filePath [description]
     * @param  [type] $max_width [description]
     * @return [type] [description]
     */
    public function reduceSize($filePath, $max_width)
    {
        // 先实例化，传参是文件的磁盘物理路径
        $image = Image::make($filePath);

        // 进行大小调整的操作
        $image->resize($max_width, null, function ($constraint) {

            // 设定宽度是 $max_width，高度等比例缩放
            $constraint->aspectRatio();

            // 防止裁图时图片尺寸变大
            $constraint->upsize();
        });

        // 对图片修改后进行保存
        $image->save();
    }
}
