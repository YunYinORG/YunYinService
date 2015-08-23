<?php
namespace Verify;
/**
 * 天大验证类
 */
class TJU extends Connect
{
	const ID = 2; //学校id

	const LOGIN_URL = 'http://e.tju.edu.cn/Main/logon.do'; //登录url
	const INFO_URL  = 'http://e.tju.edu.cn/Kaptcha.jpg';  //获取图片url

	public static function getName($number, $pwd, $code = null)
	{
		$data['uid'] = $number;
		$data['password'] = $pwd;
		$data['captchas'] = $code;
		$result = self::post('GBK', self::LOGIN_URL, $data);
		$start = '当前用户';
		$end = ')';
		$middlename = substr($result, strlen($start) + strpos($result, $start) + 14, strlen($start) + strpos($result, $start) + 18);
		return substr(trim($middlename), 0, (strpos(trim($middlename), $end)));
	}

	public static function getCode($url, $data = null)
	{
		parent::getCode(self::LOGIN_URL); //获取缓存
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIE, parent::$_cookies);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$img = curl_exec($ch);
		curl_close($ch);
		return base64_encode($img);
	}

	public static function post($encode, $url, $data = null)
	{
		return parent::post($encode, $url, $data);
	}
}