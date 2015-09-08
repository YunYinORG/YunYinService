<?php
namespace Verify;
use \Cookie;

/**
 * 天大验证类
 * TJU::getCode()获取验证码
 * TJU::getName($name,$pwd,$code);验证姓名
 */
class TJU extends Connect
{
	const ID = 2; //学校id

	const LOGIN_URL = 'http://e.tju.edu.cn/Main/logon.do'; //登录url
	const CODE_URL  = 'http://e.tju.edu.cn/Kaptcha.jpg';   //获取图片url

	// const INFO_URL  = 'http://e.tju.edu.cn/Main/index.jsp'; //信息抓取页
	// const SUCCESS_MSG = 'It\'s now at http://e.tju.edu.cn/Main/index.jsp'; //登录成功的消息

	/**
	 * 获取真实姓名
	 * @method getName
	 * @param  [type]  $number [description]
	 * @param  [type]  $pwd    [description]
	 * @param  [type]  $code   [description]
	 * @return [type]          [description]
	 */
	public static function getName($number, $pwd, $code = null)
	{
		if (!$code)
		{
			return 0;
		}
		$data['uid']      = $number;
		$data['password'] = $pwd;
		$data['captchas'] = $code;
		parent::$_cookie  = Cookie::get('verify_cookie');
		Cookie::del('verify_cookie');
		$result = parent::getHtml(self::LOGIN_URL, $data, 'GBK');
		if ($result)
		{
			$name = parent::parseName($result, "当前用户：$number(", ')');
			return $name;
		}
		return false;
	}

	/**
	 * 获取验证码
	 * @method getCode
	 * @return [type]  [description]
	 */
	public static function getCode()
	{
		$img = parent::request(self::CODE_URL);
		if ($img)
		{
			\Cookie::set('verify_cookie', self::$_cookie);
		}
		return $img;
	}
}