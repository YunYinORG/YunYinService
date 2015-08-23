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
		parent::getCode(self::LOGIN_URL, $data);  //获取缓存
		$result = parent::post('GBK', self::INFO_URL);
		$name = substr($result, strpos($result, '姓名：') + 9, (strlen($result) - strpos($result, '</li>')) * (-1));
		return $name;

	}
}

