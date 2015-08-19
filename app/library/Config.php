<?php
/**
 * 对应用配置的封装，方便读取
 * Config::get('config')
 */
class Config
{
	private static $_config = null;

	/**
	 * 获取配置
	 * @method get
	 * @param  [type] $key     [description]
	 * @param  [type] $default [默认值]
	 * @return [type]          [description]
	 * @author NewFuture
	 */
	public static function get($key = null, $default = null)
	{
		if ($value = self::getConfig()->get($key))
		{
			return is_object($value) ? $value->toArray() : $value;
		}
		else
		{
			return $default;
		}
	}

	/*h获取配置*/
	private static function getConfig()
	{
		if (null === self::$_config)
		{
			self::$_config = Yaf_Application::app()->getConfig();
		}
		return self::$_config;
	}
}
/**
 * 设置配置
 * @method set
 * @param  [type] $key   [description]
 * @param  [type] $value [description]
 * @author NewFuture
 */
// public static function set($key, $value)
// {
// 	if (is_array($key))
// 	{
// 		self::$_config = array_merge(self::$_config, $value);
// 	}
// 	elseif (strpos($key, '.'))
// 	{
// 		//多维数组
// 		$name   = explode('.', $key);
// 		$config = &self::$_config;
// 		foreach ($name as $k)
// 		{

// 			if (!isset($config[$k]))
// 			{
// 				$config[$k] = [];
// 			}
// 			$config = &$config;
// 		}
// 		return $config;
// 	}
// 	else
// 	{
// 		self::$_config[$key] = $value;
// 	}
// }