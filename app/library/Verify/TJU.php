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
		$data['captchas'] = self::getCode(self::LOGIN_URL);
		$result = self::post(self::LOGIN_URL, $data);
		$start = '当前用户';
		$end = ')';
		$middlename = substr($result, strlen($start) + strpos($result, $start) + 14, strlen($start) + strpos($result, $start) + 18);
		return substr(trim($middlename), 0, (strpos(trim($middlename), $end)));
	}

	public static function getCode($url,$data = null)
	{
		/* 第一次请求获取cookie */
		$cookie_jar = dirname(__FILE__) . "/tmp.cookie";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_jar);
		curl_exec($ch);
		curl_close($ch);

		/* 第二次请求获取验证码 */
		/*$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::INFO_URL);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$img = curl_exec($ch);
		curl_close($ch);
		$fp = fopen(dirname(__FILE__)."/code.jpg","w");
		fwrite($fp,$img);
		fclose($fp);
		sleep(20);
		$code = file_get_contents(dirname(__FILE__)."/code.txt");*/
		return $code;
	}

	public static function post($url, $data = null)
	{
		$cookie_jar = dirname(__FILE__) . "/tmp.cookie";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$result = mb_convert_encoding(curl_exec($ch), 'UTF-8', 'GBK');
		curl_close($ch);
		return $result;
	}
}