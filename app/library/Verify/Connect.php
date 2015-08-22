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
		return null;
	}

	/**
	 * post数据
	 * @method post
	 * @param  [type] $url  [description]
	 * @param  [数组] $data [description]
	 * @author NewFuture
	 */
	public static function post($url, $data = null)
	{

	}

	/**
	 * ...其他函数
	 */
}