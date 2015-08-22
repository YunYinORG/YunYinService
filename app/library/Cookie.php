<?php
/**
 * 安全cookie
 * 对cookie存取进行加密
 */
class Cookie
{
	private static $_config = null;

	/**
	 * 设置cookie
	 * @method set
	 * @param  [string] $name   [description]
	 * @param  [mixed] $value  [description]
	 * @param  string $path   [存取路径]
	 * @param  [int] $expire 有效时间
	 * @author NewFuture
	 */
	public static function set($name, $value, $path = '', $expire = null)
	{
		if ($value = Encrypt::aesEncode(json_encode($value), self::config('key'), true))
		{
			$path   = $path ?: self::config('path');
			$expire = $expire ? ($_SERVER['REQUEST_TIME'] + $expire) : null;
			return setcookie($name, $value, $expire, $path, self::config('domain'), self::config('secure'), self::config('httponly'));
		}
	}

	/**
	 * 获取cookie
	 * @method get
	 * @param  [type] $name [description]
	 * @author NewFuture
	 */
	public static function get($name)
	{
		if (isset($_COOKIE[$name]))
		{
			if ($data = Encrypt::aesDecode($_COOKIE[$name], self::config('key'), true))
			{
				return @json_decode($data);
			}
		}
	}

	/**
	 * 删除
	 * @method del
	 * @param  [type] $name [description]
	 * @author NewFuture
	 */
	public static function del($name, $path = null)
	{
		if (isset($_COOKIE[$name]))
		{
			$path = $path ?: self::config('path');
			return setcookie($name, '', 100, $path, self::config('domain'), self::config('secure'), self::config('httponly'));
			unset($_COOKIE[$name]);
		}
	}

	/**
	 * 清空cookie
	 */
	public static function flush()
	{
		if (empty($_COOKIE))
		{
			return null;
		}
		/*逐个删除*/
		foreach ($_COOKIE as $key => $val)
		{
			self::del($key);
		}
	}

	/**
	 * 获取cookie配置
	 * @method config
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	private static function config($name)
	{
		if (null === self::$_config)
		{
			$config        = Config::get('cookie');
			$path          = Config::get('secret_config_path');
			$secureConfig  = new Yaf_Config_Ini($path, 'cookie');
			self::$_config = array_merge($config, $secureConfig->toArray());
		}
		return isset(self::$_config[$name]) ? self::$_config[$name] : null;
	}
}