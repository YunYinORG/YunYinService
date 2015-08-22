<?php
namespace Verify;
/**
 * 南开验证类
 */
class NKU extends Connect
{
	const ID = 1; //学校id

	const LOGIN_URL = 'http://222.30.60.9/meol/homepage/common/login.jsp';
	const INFO_URL  = 'http://222.30.60.9/meol/welcomepage/student/index.jsp';

	public static function getName($number, $pwd, $code = null)
	{
		$data['IPT_LOGINUSERNAME'] = $number;
		$data['IPT_LOGINPASSWORD'] = $pwd;
		self::getCode($data);
		$result = self::post(self::LOGIN_URL, $data);
		$name = substr($result, (strlen('<li>'.'   ') + strpos($result, '<li>')) + 3, (strlen($result) - strpos($result, '</li>')) * (-1));
		return iconv('GBK', 'UTF-8//IGNORE', $name);
	}

	public static function getCode($data)
	{
		$cookie_jar = dirname(__FILE__) . "/tmp.cookie";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::LOGIN_URL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_NOBODY, false);
		curl_exec($ch);
		curl_close($ch);
		return null;
	}

	public static function post($url, $data)
	{
		$cookie_jar = dirname(__FILE__) . "/tmp.cookie";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::INFO_URL);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
		$result = curl_exec($ch);
		return $result;
	}
}

