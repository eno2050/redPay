<?php
/**
 * Bank.php
 * - 处理企业付款到零钱
 *
 * @author       gaoming13 <gaoming13@yeah.net>
 * @link         https://github.com/gaoming13/wechat-php-sdk
 * @link         http://me.diary8.com/
 *
 * Class Bank
 * @package Gaoming13\WechatPhpSdk
 */

namespace redpay\wechatpay;

use redpay\wechatpay\Utils\HttpCurl;
use redpay\wechatpay\Utils\SHA1;
use redpay\wechatpay\Utils\Xml;

class Bank
{
    // 微信API域名
    const API_DOMAIN = 'https://api.mch.weixin.qq.com/';
    // 开发者中心-配置项-AppID(应用ID)
    protected $appId;
    // 开发者中心-配置项-AppSecret(应用密钥)
    protected $appSecret;
    // 微信支付商户号，商户申请微信支付后，由微信支付分配的商户收款账号
    protected $mchId;
    // API密钥,微信商户平台(pay.weixin.qq.com)-->账户设置-->API安全-->密钥设置
    protected $key;
    // 证书路径
    protected $sslCert;
    protected $sslKey;

    /**
     * 设定配置项
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->appId   = $config['appId'];
        $this->mchId   = isset($config['mchId']) ? $config['mchId'] : false;
        $this->key     = isset($config['key']) ? $config['key'] : false;
        $this->sslCert = $config['ssl_cert'];
        $this->sslKey  = $config['ssl_key'];
    }

    /**
     * 企业付款到零钱
     * wiki: https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
     * @param array $conf 配置数组
     * @return array | mixed
     */
    public function wxCompanyToChange($conf = [])
    {

        // [必填]申请商户号的appid或商户号绑定的appid
        $conf['mch_appid'] = $this->appId;
        // [必填]微信支付分配的商户号
        $conf['mchid'] = $this->mchid;
        // [非必填]微信支付分配的终端设备号
        // -device_info
        // [必填]随机字符串，不长于32位
        $conf['nonce_str'] = SHA1::get_random_str(32);
        // [必填]商户订单号
        // - partner_trade_no

        // [必填]用户openid
        // - openid

        // [必填]NO_CHECK：不校验真实姓名 FORCE_CHECK：强校验真实姓名
        $conf['check_name'] = 'NO_CHECK';

        // [非必填]收款用户姓名 如果check_name设置为FORCE_CHECK，则必填用户真实姓名
        // - re_user_name

        // [必填]总金额 单位为分
        // - amount

        // [必填]企业付款备注
        // - desc

        // [必填]终端IP
        $conf['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];

        // [必填]签名
        $conf['sign'] = SHA1::getSign2($conf, 'key=' . $this->key);

        // 生成xml
        $xml = Xml::toXml($conf);

        // 调用接口
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        try {
            $res = HttpCurl::post_ssl($url, $xml, $this->ssl_cert, $this->ssl_key);
            libxml_disable_entity_loader(true);
            return json_decode(json_encode(simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        } catch (\Exception $e) {
            return false;
        }

    }

    /**
     * 企业付款到零钱查询接口
     * wiki:https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_3
     * @param  array $conf 配置数组
     * @return bool | mixed
     */
    public function wxCompanyFindChangeOrder($conf = [])
    {
        // [必填]随机字符串
        $conf['nonce_str'] = SHA1::get_random_str(32);
        // [必填]商户订单号
        // -partner_trade_no
        // [必填]商户号
        $conf['mch_id'] = $this->mchId;
        // [必填]商户号的appid
        $conf['appid'] = $this->appId;
        // [必填]签名
        $conf['sign'] = SHA1::getSign2($conf, 'key=' . $this->key);
        // 生成xml
        $xml = XML::toXml($conf);
        // 调用接口
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';
        try {
            $res = HttpCurl::post_ssl($url, $xml, $this->ssl_path);
            libxml_disable_entity_loader(true);
            return json_decode(json_encode(simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        } catch (\Exception $e) {
            return false;
        }

    }

    /**
     * 企业付款到银行卡 30s/次
     * wiki: https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_2
     * @param array $conf 配置数组 
     * @return array | mixed
     */
    public function wxCompanyToBankCard($conf = [])
    {

        // [必填]微信支付分配的商户号
        $conf['mchid'] = $this->mchid;
        // [非必填]微信支付分配的终端设备号
        // -device_info
        // [必填]随机字符串，不长于32位
        $conf['nonce_str'] = SHA1::get_random_str(32);
        // [必填]商户企业付款单号
        // - partner_trade_no

        // [必填]收款方银行卡号 (加密处理)
        // - enc_bank_no
        $conf['enc_bank_no'] = $this->publicEncrypt($conf['enc_bank_no']);

        // [必填]收款方用户名(加密处理)
        // - enc_true_name
        $conf['enc_true_name'] = $this->publicEncrypt($conf['enc_true_name']);

        // [必填]收款方开户行 银行编号
        // wiki: https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_4
        // - bank_code

        // [必填]总金额 单位为分
        // - amount

        // [非必填]企业付款备注
        // - desc

        // [必填]签名
        $conf['sign'] = SHA1::getSign2($conf, 'key=' . $this->key);

        // 生成xml
        $xml = Xml::toXml($conf);

        // 调用接口
        $url = 'https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank';
        try {
            $res = HttpCurl::post_ssl($url, $xml, $this->ssl_path);
            libxml_disable_entity_loader(true);
            return json_decode(json_encode(simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * 企业付款到银行卡查询接口
     * wiki:https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_3
     * @param  array $conf 配置数组
     * @return bool | mixed
     */
    public function wxCompanyFindBankOrder($conf = [])
    {
        // [必填]随机字符串
        $conf['nonce_str'] = SHA1::get_random_str(32);
        // [必填]商户号
        $conf['mch_id'] = $this->mchId;
        // [必填]商户订单号
        // -partner_trade_no
        // [必填]签名
        $conf['sign'] = SHA1::getSign2($conf, 'key=' . $this->key);
        // 生成xml
        $xml = XML::toXml($conf);
        // 调用接口
        $url = 'https://api.mch.weixin.qq.com/mmpaysptrans/query_bank';
        try {
            $res = HttpCurl::post_ssl($url, $xml, $this->ssl_path);
            libxml_disable_entity_loader(true);
            return json_decode(json_encode(simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        } catch (\Exception $e) {
            return false;
        }

    }
    
    /**
     * 这个事先要直接运行一次，获取公钥 ？如果我把逻辑写在这里，路径如何处理？？？
     * 获取公钥,格式为PKCS#1 转PKCS#8
     * openssl rsa  -RSAPublicKey_in -in   <filename>  -out <out_put_filename>
     */
    public function get_pub_key()
    {

        $rsafile = __DIR__ . '/cert/' . $this->appId . '_publicrsa.pem';
        if (!is_file($rsafile) || empty(file_get_contents($rsafile))) {
            $data['mch_id']    = $this->mchId;
            $data['nonce_str'] = SHA1::get_random_str(32);
            $data['sign_type'] = 'MD5';
            $data['sign']      = SHA1::getSign2($data, 'key=' . $this->key);
            $xml               = XML::toXml($data);
            $url               = 'https://fraud.mch.weixin.qq.com/risk/getpublickey';

            $res = HttpCurl::post_ssl($url, $xml, $this->sslCert, $this->sslKey);
            libxml_disable_entity_loader(true);
            $res = json_decode(json_encode(simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

            if ($res['return_code'] == 'SUCCESS' && isset($res['pub_key'])) {
                file_put_contents($rsafile, $res['pub_key']);
                return $res['pub_key'];
            } else {
                return null;
            }
        } else {
            return file_get_contents($rsafile);
        }
    }

    /**
     * 公钥加密，银行卡号和姓名需要RSA算法加密
     * @param string $data  需要加密的字符串，银行卡/姓名
     * @return null|string  加密后的字符串
     */
    private function publicEncrypt($data)
    {
        // 获取公钥
        $pubkey       = openssl_pkey_get_public(file_get_contents(__DIR__ . '/cert/pkcs8.pem'));
        $encrypt_data = '';
        $encrypted    = '';
        // 公钥加密
        $r = openssl_public_encrypt($data, $encrypt_data, $pubkey, OPENSSL_PKCS1_OAEP_PADDING);
        //加密成功，返回base64编码的字符串
        if ($r) {
            return base64_encode($encrypted . $encrypt_data);
        } else {
            return false;
        }
    }

}
