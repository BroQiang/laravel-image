<?php

return [
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
    // 返回的路径是否需要有 url
    'is_url'      => true,
    // 返回的 url 地址，如：http://www.broqiang.test，如果没有配置返回的是 app.php 中的 app.url
    'url'         => '',
];
