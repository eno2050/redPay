<?php
namespace redpay\wechatpay;
/**
 * HttpCurl Curl模拟Http工具类
 *
 * @author      gaoming13 <gaoming13@yeah.net>
 * @link        https://github.com/gaoming13/wechat-php-sdk
 * @link        http://me.diary8.com/
 */



class HttpCurl
{

    /**
     * 模拟GET请求
     *
     * @param string $url
     * @param string $data_type
     *
     * @return mixed
     *
     * Examples:
     * ```
     * HttpCurl::get('http://api.example.com/?a=123&b=456', 'json');
     * ```
     */
    public static function get($url, $data_type = 'text')
    {
        $cl = curl_init();
        if (stripos($url, 'https://') !== false) {
            curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($cl, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($cl, CURLOPT_URL, $url);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($cl);
        $status  = curl_getinfo($cl);
        curl_close($cl);
        if (isset($status['http_code']) && $status['http_code'] == 200) {
            if ($data_type == 'json') {
                $content = json_decode($content);
            }
            return $content;
        } else {
            return false;
        }
    }

    /**
     * 模拟POST请求
     *
     * @param string $url
     * @param array $fields
     * @param string $data_type
     *
     * @return mixed
     *
     * Examples:
     * ```
     * HttpCurl::post('http://api.example.com/?a=123', array('abc'=>'123', 'efg'=>'567'), 'json');
     * HttpCurl::post('http://api.example.com/', '这是post原始内容', 'json');
     * 文件post上传
     * HttpCurl::post('http://api.example.com/', array('abc'=>'123', 'file1'=>'@/data/1.jpg'), 'json');
     * ```
     */
    public static function post($url, $fields, $data_type = 'text')
    {
        $cl = curl_init();
        if (stripos($url, 'https://') !== false) {
            curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($cl, CURLOPT_SSLVERSION, 1);
        }
        if (class_exists('\CURLFile')) {
            if (isset($fields['media'])) {
                $fields = array('media' => new \CURLFile(realpath(ltrim($fields['media'], '@'))));
            }
        } else {
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($cl, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        curl_setopt($cl, CURLOPT_URL, $url);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cl, CURLOPT_POST, true);
        curl_setopt($cl, CURLOPT_POSTFIELDS, $fields);
        $content = curl_exec($cl);
        $status  = curl_getinfo($cl);
        curl_close($cl);
        if (isset($status['http_code']) && $status['http_code'] == 200) {
            if ($data_type == 'json') {
                $content = json_decode($content);
            }
            return $content;
        } else {
            return false;
        }
    }

    /**
     * 模拟POST请求
     *
     * @param string $url
     * @param array $fields
     * @param array 证书和密钥数组
     * @return mixed
     *
     */
    public static function post_ssl($url, $fields, $cert, $key)
    {
        $cl = curl_init();
        if (stripos($url, 'https://') !== false) {
            curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($cl, CURLOPT_SSLVERSION, 1);
        }
        if (class_exists('\CURLFile')) {
            if (isset($fields['media'])) {
                $fields = array('media' => new \CURLFile(realpath(ltrim($fields['media'], '@'))));
            }
        } else {
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($cl, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        curl_setopt($cl, CURLOPT_SSLCERT, $cert);
        curl_setopt($cl, CURLOPT_SSLKEY, $key);
        curl_setopt($cl, CURLOPT_URL, $url);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cl, CURLOPT_POST, true);
        curl_setopt($cl, CURLOPT_POSTFIELDS, $fields);
        $content = curl_exec($cl);
        $status  = curl_getinfo($cl);
        curl_close($cl);
        if (isset($status['http_code']) && $status['http_code'] == 200) {
            return $content;
        } else {
            return false;
        }
    }
}
