<?php
namespace Verify;
/**
 * 南开验证类
 */
class NKU extends Connect
{
	const ID = 1; //学校id

	const LOGIN_URL = '';
	const INFO_URL  = '';

	public static function getName($number, $pwd)
	{
		// $data['username'] = $number;
		// 	$result = self::post(self::LOGIN_URL, $data);
		if ($number && $pwd)
		{
			return '南开用户';
		}

	}
}