<?php
namespace Verify;
use \Cookie;

/**
 * 天大验证类
 * TJU::getCode()获取验证码
 * TJU::getName($name,$pwd,$code);验证姓名
 */
class HEBUT extends Connect
{
	const ID = 4; //学校id

	const LOGIN_URL = 'http://115.24.160.162/loginAction.do';        //登录url
	const CODE_URL  = 'http://115.24.160.162/validateCodeAction.do'; //获取图片url

	const INFO_URL    = 'http://115.24.160.162/menu/top.jsp'; //信息抓取页
	const SUCCESS_MSG = '/menu/top.jsp';                      //登录成功的消息

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
		$data['zjh']   = $number;
		$data['mm']    = $pwd;
		$data['v_yzm'] = $code;

		if ($cookie = Cookie::get('verify_cookie'))
		{
			Cookie::del('verify_cookie', null, $_SERVER['HTTP_HOST']);
		}
		else
		{
			if (\Input::I('verify_cookie', $cookie, 'trim'))
			{
				$cookie = @base64_decode($cookie);
			}
		}
		parent::$_cookie = $cookie;

		$result = parent::getHtml(self::LOGIN_URL, $data, 'gb2312');
		if (strpos($result, self::SUCCESS_MSG) > 0)
		{
			$result = parent::getHtml(self::INFO_URL,null,'GBK');
			if ($result)
			{
				$name = parent::parseName($result, "当前用户:$number(", ')');
				return trim($name);
			}
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
			Cookie::set('verify_cookie', self::$_cookie, null, null, $_SERVER['HTTP_HOST']);
		}
		return ['img' => $img, 'verify_cookie' => base64_encode(self::$_cookie)];
	}
}