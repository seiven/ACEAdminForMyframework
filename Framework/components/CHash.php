<?php
/**
 * CMyFrame Hash
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/

class CHash
{

	/**
	 * 随机值
	 * @param 长度
	 * @param 随机码类型：0，数字+大小写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
	 */
	public static function rand($length = 5, $type = 0) {
	    $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
	    if ($type == 0) {
	        array_pop($arr);
	        $string = implode("", $arr);
	    } elseif ($type == "-1") {
	        $string = implode("", $arr);
	    } else {
	        $string = $arr[$type];
	    }
	    $count = strlen($string) - 1;
	    $code = '';
	    for ($i = 0; $i < $length; $i++) {
	        $code .= $string[rand(0, $count)];
	    }
	    return $code;
	}
}