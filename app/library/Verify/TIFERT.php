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
			$result = self::post('UTF-8', self::INFO_URL);
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
		return parent::getCode($url, $data);
	}

	public static function post($encode, $url, $data = null)
	{
		return parent::post($url);
	}
}