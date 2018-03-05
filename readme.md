# Laravel Image

用于 Laravel 的图片处理

计划支持本地图片和七牛云，暂时先只实现本地上传，等后续有时间再完善

## 使用方法

#### 安装扩展包

```bash
composer require broqiang/laravel-image "^1.0"
```

#### 发布配置文件

如果默认的配置文件可以满足，跳过这步也可以

默认的配置文件

```php
    // 允许的后缀名
    'allowed_ext' => ["png", "jpg", "gif", 'jpeg'],
    // 上传图片的根目录
    'root_folder' => '/uploads/image',
    // 上传图片的目录
    'folder'      => 'default',
    // 时间格式 =>
    'date_format' => 'Y/m/d',
    // 文件前缀
    'file_prefix' => '',
```

如果不满足，需要自定义配置，可以执行下面命令，然后到 config/bro_image.php 中去修改

```bash
php artisan vendor:publish --provider="BroQiang\LaravelImage\LaravelImageProvider"
```

## 使用

```php
$imagePath = $image->upload($request->filename, 'avatar', 'image_prefix', 260);
```

- 第一个参数是 Laravel 的一个 file 对象

- 第二个参数是保存目录，可以为空，如果为空就会使用配置文件中的默认值

- 第三个参数是图片保存文件的前缀

- 第四个参数是剪裁的尺寸，这里传入的是图片的高度，然后按照高度等比例缩放，如果不传这个参数不进行缩放

- 返回值，返回的是图片保存后的 url 地址
