<?php
namespace Verify;

/**
 * 湖南理工学院验证类http://hnist.cn/
 * 61.187.92.235 【http://info.hnist.cn/】服务器访问慢;
 * HNIST::getName($name,$pwd);验证姓名
 */
class HNIST extends Connect
{
	const ID = 4; //学校id

	const LOGIN_URL = 'http://61.187.92.235/loginAction.do';                  //登录url
	const ERROR_MSG = '错误信息';                                         //错误提示
	const INFO_URL  = 'http://61.187.92.235/roamingAction.do?appId=BKS_CJCX'; //跳转页
	/**
	 * 获取真实姓名
	 * @method getName
	 * @param  [type]  $number [description]
	 * @param  [type]  $pwd    [description]
	 * @return [type]          [description]
	 */
	public static function getName($number, $pwd)
	{
		$data['userName'] = $number;
		$data['userPass'] = $pwd;

		$result = parent::getHtml(self::LOGIN_URL, $data, 'gb2312');
		if ($result && strpos($result, self::ERROR_MSG) == FALSE)
		{

			//由于跳转链接有BUG，需要修改后跳转
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, self::INFO_URL);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); //不直接重定向
			curl_setopt($ch, CURLOPT_COOKIE, self::$_cookie);
			$result = curl_exec($ch);
			curl_close($ch);
			if (!($result && ($url = parent::parseName($result, 'href="', '"'))))
			{
				return NULL;
			}
			$bill = substr($url, -36);
			$url  = 'http://61.187.92.238:7778/pls/wwwbks/bks_login2.loginbill?nAppType=3&p_bill=' . $bill;
			$ch   = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_NOBODY, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$ret = curl_exec($ch);
			curl_close($ch);
			if (!preg_match('/Set-Cookie:(.*);/iU', $ret, $str))
			{
				return NULL;
			}
			$cookie = $str[1]; //获得COOKIE（SESSIONID）
			$cookie = trim($cookie);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://61.187.92.238:7778/pls/wwwbks/bks_xj.xjcx');
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
			$ret = curl_exec($ch);
			curl_close($ch);
			if (!$ret)
			{
				return NULL;
			}
			//姓名截取
			$html = mb_convert_encoding($ret, 'UTF-8', 'GBK');
			if ($html = parent::parseName($html, '姓名</strong></p></td>', '</p></td>'))
			{
				return substr($html, strrpos($html, '>') + 1);
			}

		}
		return FALSE;
	}
}