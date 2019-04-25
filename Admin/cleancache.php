<?php

header("Content-type: text/html; charset=utf-8");
//缓存路径
$dirs = '/data/www/shoufashi/admin/Admin/Runtime';
//清理缓存
rmdirr($dirs);
echo "<script>setTimeout('window.history.go(-1);',1000);</script><div style='border:2px solid green; background:#f1f1f1; padding:20px;margin:20px;width:800px;font-weight:bold;color:green;text-align:center;'>  文件缓存已清除！ </div> <br /><br />";

function rmdirr($path) {
    if (is_dir($path)) {
        //echo "cd... " . $path;
        $file_list = scandir($path);
        foreach ($file_list as $file) {
            if ($file != '.' && $file != '..' && $file != 'Logs') {
					rmdirr($path . '/' . $file);
            }
        }
        @rmdir($path);  //这种方法不用判断文件夹是否为空,  因为不管开始时文件夹是否为空,到达这里的时候,都是空的     
    } else {
        //echo "rm... " . $path;
        @unlink($path);    //这两个地方最好还是要用@屏蔽一下warning错误,看着闹心
    }
}

?>