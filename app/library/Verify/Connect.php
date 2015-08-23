<?php
namespace Verify;
/**
 * 链接类
 */
abstract class Connect
{
	// protected const $login_url = '';
	// protected const $info_url  = '';
	protected static $_cookies = null;

	/**
	 * 获取用户姓名
	 * @method getName
	 * @param  [type]  $number [学号]
	 * @param  [type]  $pwd    [密码]
	 * @param  [type]  $code   [验证码，可选]
	 * @return [type]          [姓名]
	 */
	public static function getName($number, $pwd, $code = null)
	{
		return '姓名';
	}

	/**
	 * 获取验证码
	 * @method getCode
	 * @return [type]  [description]
	 */
	public static function getCode($url, $data = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		if ($data)
		{
			curl_setopt($ch,CURLOPT_POSTFIELDS, $data); 
		}
		$result = curl_exec($ch);
		preg_match('/Set-Cookie:(.*);/iU', $result, $matchs);
		self::$_cookies = $matchs[1];
		curl_close($ch);
		return $result;
	}

	/**
	 * post数据
	 * @method post
	 * @param  [type] $url  [description]
	 * @param  [数组] $data [description]
	 * @author NewFuture
	 */
	public static function post($encode, $url, $data = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIE, self::$_cookies);
		if ($data)
		{
			curl_setopt($ch,CURLOPT_POSTFIELDS, $data); 
		}
		$result = curl_exec($ch);
		curl_close($ch);
		return iconv($encode, 'UTF-8//IGNORE', $result);
	}

	/**
	 * ...其他函数
	 */
}