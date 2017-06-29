<?php

/*
 * ------------------------------------------------------------------------
 * 自定义快速使用方法
 * 需要在 bootstrap\autoload.php中引入
 * ------------------------------------------------------------------------
 */
if (!function_exists('date_only')) {

    /**
     * 返回日期时间格式(2016-12-12
     *
     * @param  timespan $value default time()
     * @return string
     */
    function date_only($time = null)
    {
        if (empty($time)) $time = time();
        return date("Y-m-d", $time);
    }
}

if (!function_exists('date_time')) {
    /**
     * 返回日期时间格式(2016-12-12 12:00:00
     * @return mixed
     */
    function date_time()
    {
        return date("Y-m-d H:i:s");
    }
}

if (!function_exists('parse_apk_icon')) {
    /**
     * @param $apkPath  apk文件路径
     * @param $dir  目标放置路径
     * @return string   相对于目标路径,文件的路径 | 为空时即解析失败
     */
    function parse_apk_icon($apkPath, $dir)
    {
        $aapt = base_path("../bin/aapt");

        $cmd = sprintf("%s d badging %s 2>&1 | grep application-icon | tail -1 | awk -F\"'\" '{ print $2}'", $aapt, $apkPath);
        Log::debug($cmd);
        $filename = exec($cmd);
        if ($filename) {
            $zip_cmd = sprintf("unzip -o %s %s -d %s", $apkPath, $filename, $dir);
            exec($zip_cmd);
        }
        return $filename;
    }
}


if (!function_exists('video_info')) {

    /**
     * 将一个秒数，转换成 00:00:02的格式
     * @param $sec
     * @return string
     */
    function formatTime($sec)
    {
        $sec = $sec % (24 * 3600);
        $hours = floor($sec / 3600);
        $remainSeconds = $sec % 3600;
        $minutes = floor($remainSeconds / 60);
        $seconds = intval($sec - $hours * 3600 - $minutes * 60);

        return sprintf("%s:%s:%s", str_pad($hours, 2, "0", STR_PAD_LEFT), str_pad($minutes, 2, "0", STR_PAD_LEFT), str_pad($seconds, 2, "0", STR_PAD_LEFT));
    }

    /**
     * 使用ffmpeg工具，获取视频的信息
     * @param $path
     * @return array [Type, Duration, DisplayDuration, Resolution]
     */
    function video_info($path)
    {
        $bin = "/opt/runtime/ffmpeg/ffprobe";

        $result = array("Type" => "", "Duration" => "", "DisplayDuration" => "", "Resolution" => "");
        //2016-07-15 zxl 更换了mp4获取时长的方式
        $cmd = sprintf('%s -v quiet -print_format json -show_format -show_streams "%s" 2>&1', $bin, $path);
        exec($cmd, $arr);

        $obj = json_decode(implode($arr));

        if ($obj && property_exists($obj, "format")) {
            $type = $obj->format->format_name;
            $result["Type"] = $type;
            if (false === stripos($type, "_pipe")) {
                $result["Duration"] = (int)$obj->format->duration;
                $result["DisplayDuration"] = formatTime($obj->format->duration);
            }

            if ("mp3" != $type) {
                foreach ($obj->streams as $stream) {    //视频流和音频流
                    if ($stream->codec_type == "video") {
                        $result["Resolution"] = sprintf("%sx%s", @$stream->width, @$stream->height);
                        break;
                    }
                }
            }
        }
        return $result;
    }
}

if (!function_exists('parse_apk_version_name')) {
    /**
     * @param $apkPath  apk文件路径
     * @return string   相对于目标路径,文件的路径 | 为空时即解析失败
     */
    function parse_apk_version_name($apkPath)
    {
        $aapt = base_path("../bin/aapt");

        $cmd = sprintf("%s d badging %s 2>&1 | grep package | tail -1 | awk -F= '{ print \$NF}'", $aapt, $apkPath);
        Log::debug($cmd);
        $version = exec($cmd);
        $version = substr($version, 1, strlen($version) - 2);
        return $version;
    }

    function parse_apk_version_code($apkPath)
    {
        $aapt = base_path("../bin/aapt");

        $cmd = sprintf("%s d badging %s 2>&1 | grep package | tail -1 | awk -F \"[= ]\" '{ print $5}'", $aapt, $apkPath);
        Log::debug($cmd);
        $code = exec($cmd);
        $code = substr($code, 1, strlen($code) - 2);
        return $code;
    }

    function parse_apk_package_name($apkPath)
    {
        $aapt = base_path("../bin/aapt");

        $cmd = sprintf("%s d badging %s 2>&1 | grep package | tail -1 | awk -F \"[= ]\" '{ print $3}'", $aapt, $apkPath);
        Log::debug($cmd);
        $code = exec($cmd);
        $code = substr($code, 1, strlen($code) - 2);
        return $code;
    }
}

if (!function_exists("random_mac")) {
    function random_mac()
    {
        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, '0', 'a', 'b', 'c', 'd', 'e', 'f'];
        $new = [];
        while (count($new) < 12) {
            $new[] = $arr[random_int(0, 15)];
        }
        return implode("", $new);
    }
}

if (!function_exists("delete_dir")) {
    function delete_dir($dir)
    {
        if (is_dir($dir)) {
            $dh = opendir($dir);
            while ($file = readdir($dh)) {
                if ($file != "." && $file != "..") {
                    $fullpath = $dir . "/" . $file;
                    if (!is_dir($fullpath)) {
                        unlink($fullpath);
                    } else {
                        delete_dir($fullpath);
                    }
                }
            }
            closedir($dh);
            //删除当前文件夹：
            if (rmdir($dir)) {
                return true;
            } else {
                return false;
            }
        }
    }
}

if (!function_exists("url2path")) {
    /**
     * 将url转换本地绝路径
     * 为兼容,替换APP_RUL和RESOURCE_RUL
     * @param $url
     * @return string
     */
    function url2path($url)
    {
        $find = [config("app.url"), env("RESOURCE_URL")];
        $relative = str_replace($find, "", $url);
        return public_path() . $relative;
    }

    /**
     * 本地路径转换成url形式
     * @param $path
     * @return string
     */
    function path2url($path)
    {
        $relative = str_replace(public_path(), "", $path);
        return env("RESOURCE_URL", config("app.url")) . $relative;
    }
}

if (!function_exists('get_weekday')) {
    /**
     * 获取指定日期，当周的所有日期, 以 day_of_week为key 以周日开始 和通常的日历差一个周日 why?
     * @param \Carbon\Carbon $date
     * @return array
     */
    function get_weekday(Carbon\Carbon $date)
    {
        $days = [];
        for ($i = 1; $i < 8; $i++) {
            $d = \Carbon\Carbon::instance($date)->addDays($i - $date->dayOfWeek);

            $days[$d->dayOfWeek] = $d->toDateString();
        }
        return $days;
    }
}

if (!function_exists('url_replace')) {
    /**
     * 替换指定字符串中的IP及端口信息
     * @param string $string
     * @param string $replace_url
     * @return string mixed
     */
    function url_replace($string, $replace_url)
    {
        $search = '/http:\/\/((\d{1,3}.){4}(:{0,1}\d{1,4})?)\//i';
        preg_match_all($search, $string, $matches);

        return preg_replace($search, $replace_url, $string);
    }
}

if (!function_exists('modify_env')) {

    /**
     * 试图将一些配置动态写入.env文件
     * @param array $data
     */
    function modify_env(array $data)
    {
        $envPath = base_path() . DIRECTORY_SEPARATOR . '.env';

        $contentArray = collect(file($envPath, FILE_IGNORE_NEW_LINES));

        $contentArray->transform(function ($item) use ($data) {
            foreach ($data as $key => $value) {
                if (str_contains($item, $key)) {
                    return $key . '=' . $value;
                }
            }

            return $item;
        });

        $content = implode($contentArray->toArray(), "\n");

        File::put($envPath, $content);
    }
}

/**
 * 计算一个区域，在拼接屏的网格中，占据了几块屏幕。每块屏固定1920x1080
 * @param $region
 * @return array
 */
function get_marix($region)
{
    $width = 1920;
    $height = 1080;

    //区域的左上角
    $x = $region["left"];
    $y = $region["top"];

    // 新的 left 值计算：列数xRegionWidth ，计算列数，如果差值小于0.1，忽略
    $colIndex = $x / $width;
    $colIndex = ($colIndex - floor($colIndex)) > 0.9999 ? round($colIndex) : floor($colIndex);
    $rowIndex = $y / $height;
    $rowIndex = ($rowIndex - floor($rowIndex) > 0.9999 ? round($rowIndex) : floor($rowIndex));

    $nx = $colIndex * $width;
    $ny = $rowIndex * $height;

    $drx = $x + $region["width"];    //dr 右下角，drx，即left值
    $dry = $y + $region["height"];   //dr 右下角，dry， 即top 值

    $rows = ($dry - $ny) / $height;    //需要占几行，0.1 忽略
    $cols = ($drx - $nx) / $width;  //需要占几行，0.1 忽略
    $rows = ($rows - floor($rows)) < 0.0002 ? round($rows) : ceil($rows);
    $cols = ($cols - floor($cols)) < 0.0002 ? round($cols) : ceil($cols);

    return [$rows, $cols];
}

function get_pistions($rows, $cols, $region)
{
    $data = [];

    for ($i = 0; $i < $rows; $i++) {    // 行
        for ($j = 0; $j < $cols; $j++) {  // 列
            $t = (0 == $i) ? $region["top"] : 1080 * (floor($region["top"] / 1080) + $i);
            $l = (0 == $j) ? $region["left"] : 1920 * (floor($region["left"] / 1920) + $j);
            $data[] = [
                "top" => $t, "left" => $l
            ];
        }
        $data[] = [
            "top" => $region["top"], "left" => $region["width"] + $region["left"]
        ];
    }

    return $data;
}

function getPointIndex($point, $tpl_columns)
{
    //计算这个点，在哪块屏幕上
    $y = ceil(($point["top"] + 1) / 1080);
    $x = ceil(($point["left"] + 1) / 1920);
    $index = ($y - 1) * $tpl_columns + $x;
    return $index;
}

function getInfo($data, $region, $rows, $cols, $tpl_columns)
{
    $a = 0;
    $b = 1;
    $info = [];

    for ($i = 0; $i < count($data); $i++) {
        if ($a++ == $cols) {
            $a = 0;
            $b++;
        } else {
            $top = $data[$i]["top"];
            $left = $data[$i]["left"];

            $temp = [
                'top' => $top,
                'left' => $left,
                'width' => $data[$i + 1]["left"] - $data[$i]["left"]
            ];

            if (1 == $rows) $temp['height'] = $region["height"];
            else if ($b == 1)
                $temp['height'] = (1080 * ceil($data[$i]["top"] / 1080)) - $region["top"];
            else if ($b < $rows && $b > 0)
                $temp['height'] = 1080;
            else if ($b == $rows) {
                $temp['height'] = ($region["height"] + $region["top"]) - $data[$i]["top"];
            } else
                throw new Exception('condition is not available');

            $index = getPointIndex($temp, $tpl_columns);
//      if (!real) {
//          $temp . $top -= $region . $top;
//          $temp . $left -= $region . $left;
//      }
            $info[$index] = $temp;
        }
    }

    return $info;
}


?>