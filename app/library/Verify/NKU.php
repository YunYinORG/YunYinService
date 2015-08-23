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
		self::getCode(self::LOGIN_URL, $data);
		$result = self::post('GBK', self::INFO_URL);
		$name = substr($result, strpos($result, '姓名：') + 9, (strlen($result) - strpos($result, '</li>')) * (-1));
		return $name;

	}

	public static function getCode($url,$data = null)
	{
		return parent::getCode($url, $data);
	}

	public static function post($encode, $url, $data = null)
	{
		return parent::post($encode, $url);
	}
}

