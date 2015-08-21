<?php
/**
 * 数据格式验证
 *  - card()
 * - email()
 * - phone()
 * Classes list:
 * - Validate
 */
class Validate
{
	/**
	 * 校园卡格式验证
	 * @method card
	 * @param  [type] &$number [description]
	 * @param  string $school  [学校，nku,tju,默认全部]
	 * @return [type]          [description]
	 * @author NewFuture
	 */
	public static function card(&$number, $school = 'all')
	{
		if ($regex = Config::get('regex.number.' . strtolower($school)))
		{
			return preg_match($regex, $number);
		}
		else
		{
			throw new Exception('位置学校', 1);

		}
	}

	/**
	 * 验证邮箱格式
	 * @method email
	 * @param  [type]  &$email       [description]
	 * @param  boolean $ignore_mx [是否忽略对mx记录的检查]
	 * @return [type]                [description]
	 * @author NewFuture
	 */
	public static function email(&$email, $ignore_mx = false)
	{
		return preg_match(Config::get('regex.email'), $email)
		&& ($ignore_mx || checkdnsrr(substr(strrchr($email, '@'), 1)));
	}

	/*验证手机格式*/
	public static function phone(&$phone)
	{
		return preg_match(Config::get('regex.phone'), $phone);
	}

	/*验证账号格式*/
	public static function account(&$account)
	{
		return preg_match(Config::get('regex.account'), $account);
	}

	/*验证姓名格式*/
	public static function name(&$name)
	{
		return preg_match(Config::get('regex.name'), $name);
	}
}
