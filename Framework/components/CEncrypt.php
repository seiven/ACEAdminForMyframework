<?php

/**
 * 加密类
 */
class CEncrypt
{
    /**
     * @var string 默认加密解密KEY
     */
    CONST KEYSTRING = 'sdk~!@3`*(%f^&)';
    /**
     * 随机密匙长度
     *  随机密钥长度 取值 0-32;
     *  加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
     *  取值越大，密文变动规律越大，密文变化 = 16 的 $ckeyLength 次方
     *  当此值为 0 时，则不产生随机密钥
     * @var integer
     */
    public static $ckeyLength = 30;
    
    /**
     * 加密函数
     * 
     * @param string  $string   需要加密的明文
     * @param string  $key      加密KEY，如果为空，使用默认加密KEY
     * @param integer $expiry   加密有效期
     * @return string
     */
    public static function encode($string, $key = '', $expiry = 0) {
        
        if(!$string){return false;}
        
        $key = md5($key ? $key : CEncrypt::KEYSTRING);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = CEncrypt::$ckeyLength ?  substr(md5(microtime()), -CEncrypt::$ckeyLength) : '';

        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);

        $string = sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }


        return $keyc.str_replace(array('=','+','/'), array('','_','-'), base64_encode($result));

    }
    
    /**
     * 解密函数
     *  
     * @param string $string    需要解密的密文
     * @param string $key       解密用的KEY
     * @return string
     */
    public static function decode($string, $key = '') {

        if(!$string){return false;}
        
        $string = str_replace(array('_','-'), array('+','/'), $string);
        
        $key = md5($key ? $key : CEncrypt::KEYSTRING);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = CEncrypt::$ckeyLength ?  substr($string, 0, CEncrypt::$ckeyLength) : '';

        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);

        $string = base64_decode(substr($string, CEncrypt::$ckeyLength));
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }

    }
    
    /**
     * 加密，类似google cookie加密
     * @param mixed $value 
     * @param string $key 密钥
     * @return string 
     */
    public static function CEncryptG($value, $key = self::KEYSTRING)
    {
        if(!$value){return false;}
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        // windows 下不存在 /dev/urandom
        if( true ){
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND );
        }else{
            $iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
        }
        $crypttext = mcrypt_Encrypt(MCRYPT_RIJNDAEL_256, $key, $value, MCRYPT_MODE_ECB, $iv);
        return trim(self::safe_b64encode($crypttext)); //encode for cookie
    }
    
    /**
     * 类似google cookie的解密
     * @param string $value 
     * @param string $key 密钥
     * @return string 
     */
    public static function decryptG($value, $key = self::KEYSTRING)
    {
        if(!$value){return false;}
        $crypttext = self::safe_b64decode($value); //decode cookie
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);

        // windows 下不存在 /dev/urandom
        if( true ){
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND );
        }else{
            $iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
        }
        $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
        return trim($decrypttext);
    }
    
    /**
     * hmac sha1加密方式，from oauth 1.0 protocol
     * @param string $base_string 
     * @param string $key 
     * @return string 
     */
    public static function hmac_sha1($base_string, $key = self::KEYSTRING)
    {
        return CEncrypt::safe_b64encode(hash_hmac('sha1', $base_string, $key, true));
    }

    /**
     * 安全base64_encode
     *
     * 替换掉+ / = 字符，这样不用urldecode了
     * @param $string
     */
    public  static function safe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }

    /**
     * 安全的base64_encode
     *
     * @param $string
     */
    public static function safe_b64decode($string)
    {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4)
        {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    /**
     * XXTEA加密/解密算法
     * @param   $str: 原始字符串
     * @param   $key: 加密/解密的密钥
     * @return  string
     * @copyright   http://coolcode.org/?action=show&id=128
     */
    public static function CEncrypt_XXTEA($str, $key = NULL)
    {
        return self::_XXTEA($str, 'CEncrypt', ($key === NULL ? self::KEYSTRING : $key));
    }

    /**
     * XXTEA加密/解密算法
     * @param   $str: 待解密字符串
     * @param   $key: 加密/解密的密钥
     * @return  string
     * @copyright   http://coolcode.org/?action=show&id=128
     */
    public static function decrypt_XXTEA($str, $key = NULL)
    {
        return self::_XXTEA($str, 'DECRYPT', ($key === NULL ? self::KEYSTRING : $key));
    }

    private static function _XXTEA($str, $action, $key)
    {
        if (empty($str)) {
            return '';
        }

        $key = empty($key) ? self::KEYSTRING : $key;
        $str = $action == 'DECRYPT' ? CEncrypt::safe_b64decode($str) : $str;
        $v = self::_xxtea_str2long($str, $action == 'DECRYPT' ? false : true);
        $k = self::_xxtea_str2long($key, false);

        if (empty($v) || empty($k)) {
            return '';
        }

        $len = count($k);

        if ($len < 4) {
            for ($i = $len; $i < 4; $i++) {
                $k[$i] = 0;
            }
        }

        $n = count($v) - 1;
        $z = $v[$n];
        $y = $v[0];
        $delta = 0x9E3779B9;
        $q = floor(6 + 52 / ($n + 1));

        if ($action == 'DECRYPT') {
            $sum = self::_xxtea_int32($q * $delta);
            while ($sum != 0) {
                $e = $sum >> 2 & 3;
                for ($p = $n; $p > 0; $p--) {
                    $z = $v[$p - 1];
                    $mx = self::_xxtea_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::_xxtea_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
                    $y = $v[$p] = self::_xxtea_int32($v[$p] - $mx);
                }
                $z = $v[$n];
                $mx = self::_xxtea_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::_xxtea_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
                $y = $v[0] = self::_xxtea_int32($v[0] - $mx);
                $sum = self::_xxtea_int32($sum - $delta);
            }
            return self::_xxtea_long2str($v, true);
        } else {
            $sum = 0;
            while (0 < $q--) {
                $sum = self::_xxtea_int32($sum + $delta);
                $e = $sum >> 2 & 3;
                for ($p = 0; $p < $n; $p++) {
                    $y = $v[$p + 1];
                    $mx = self::_xxtea_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::_xxtea_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
                    $z = $v[$p] = self::_xxtea_int32($v[$p] + $mx);
                }
                $y = $v[0];
                $mx = self::_xxtea_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::_xxtea_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
                $z = $v[$n] = self::_xxtea_int32($v[$n] + $mx);
            }
            return CEncrypt::safe_b64encode(self::_xxtea_long2str($v, false));
        }
    }

    private static function _xxtea_long2str($v, $w)
    {
        $len = count($v);
        $n = ($len - 1) << 2;
        if ($w) {
            $m = $v[$len - 1];
            if (($m < $n - 3) || ($m > $n)) {
                return false;
            }
            $n = $m;
        }
        $s = array();
        for ($i = 0; $i < $len; $i++) {
            $s[$i] = pack("V", $v[$i]);
        }

        return $w ? substr(implode('', $s), 0, $n) : implode('', $s);
    }

    private static function _xxtea_str2long($s, $w)
    {
        $v = unpack("V*", $s. str_repeat("\0", (4 - strlen($s) % 4) & 3));
        $v = array_values($v);
        if ($w) {
            $v[count($v)] = strlen($s);
        }
        return $v;
    }

    private static function _xxtea_int32($n)
    {
        while ($n >= 2147483648) $n -= 4294967296;
        while ($n <= -2147483649) $n += 4294967296; 
        return (int)$n;
    }
}
// End Core_CEncrypt
