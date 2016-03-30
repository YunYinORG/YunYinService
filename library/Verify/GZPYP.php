<?php
namespace Verify;
/**
 * 广州番禺职业学院验证类
 */
class GZPYP extends Connect
{
	const ID = 5; //学校id

	const LOGIN_URL = 'http://jw.gzpyp.edu.cn/userPasswordValidate.portal'; //登录url
	const INFO_URL  = 'http://jw.gzpyp.edu.cn/index.portal';                //抓取信息url
	const SUCC_KEY  = '<script type="text/javascript">(opener || parent).handleLoginSuccessed();</script>';

	public static function getName($number, $pwd)
	{
		$data   = 'Login.Token1=' . $number . '&Login.Token2=' . $pwd;
		$result = parent::getHtml(self::LOGIN_URL, $data);
		if ($result && strpos($result, self::SUCC_KEY) !== false)
		{
			if ($result = parent::getHtml(self::INFO_URL))
			{
				return parent::parseName($result, '欢迎您：', '</li>');
			}
		}
		return false;
	}
}