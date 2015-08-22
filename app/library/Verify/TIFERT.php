<?php
namespace Verify;
/**
 * 天津商职验证类
 */
class TJU extends Connect
{
	const ID = 3; //学校id

	const LOGIN_URL = 'http://jw.tifert.edu.cn/2003/Logon.do?method=logon'; //登录url
	const INFO_URL  = 'http://jw.tifert.edu.cn/2003/framework/main.jsp';  //抓取信息url

	public static function getName($number, $pwd, $code = null)
	{
		$data['USERNAME'] = $number;
		$data['PASSWORD'] = $pwd;
		$success_key = '<script language=\'javascript\'>window.location.href=\'http://jw.tifert.edu.cn/2003/framework/main.jsp\';</script>';
		if (strpos(self::getCode(self::LOGIN_URL, $data), $success_key) === false)
		{
			return false;
		}
		else
		{
			$result = self::post(self::INFO_URL);
			$start  = '当前用户：';
			$end    = '</td>';
			$s = strpos($results, $start) + strlen($start); //起始位置
			$e = strpos($results, $end, $s);
			$name = substr($results, $s, $e - $s);
			return trim($name);
		}
	}

	public static function getCode($url, $data = null)
	{
		$cookie_jar = dirname(__FILE__) . "/tmp.cookie";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_NOBODY, false);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	public static function post($url, $data = null)
	{
		$cookie_jar = dirname(__FILE__) . "/tmp.cookie";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_REFERER,'http://jw.tifert.edu.cn/2003/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
		$result = curl_exec($ch);
		return $result;

	}
}