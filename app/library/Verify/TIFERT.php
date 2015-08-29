<?php
namespace Verify;
/**
 * 天津商职验证类
 */
class TIFERT extends Connect
{
	const ID = 3; //学校id

	const LOGIN_URL = 'http://jw.tifert.edu.cn/2003/Logon.do?method=logon'; //登录url
	const INFO_URL  = 'http://jw.tifert.edu.cn/2003/framework/main.jsp';    //抓取信息url
	const SUCC_KEY  = 'window.location.href=\'http://jw.tifert.edu.cn/2003/framework/main.jsp';
	
	public static function getName($number, $pwd)
	{
		$data   = "USERNAME=$number&PASSWORD=$pwd";
		$result = parent::getHtml(self::LOGIN_URL, $data);
		if (strpos($result, self::SUCC_KEY) !== false)
		{
			if ($result = parent::getHtml(self::INFO_URL, $data))
			{
				$name = parent::parseName($result, '当前用户：', '</td>');
				return trim($name);
			}
		}
	}

}