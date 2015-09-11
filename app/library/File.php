<?php

class File
{

	private static $_handler;

	/**
	 * 获取文件
	 * @method get
	 * @param  [type] $name  [description]
	 * @param  array  $param [description]
	 * @return [url]        [下载链接]
	 * @author NewFuture
	 */
	public static function get($name, $param = [])
	{
		$param['e'] = $_SERVER['REQUEST_TIME'] + 300; //下载过期时间
		return self::handler()->download(trim($name), $param);
	}

	/**
	 * 修改文件
	 * @method set
	 * @param  [type] $name    [description]
	 * @param  [type] $newName [description]
	 * @author NewFuture
	 */
	public static function set($name, $newName)
	{
		return self::handler()->move($name, $newName);
	}

	/**
	 * 删除文件
	 * @method del
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	public static function del($name)
	{
		return self::handler()->delete($name);
	}

	/**
	 * 获取上传token
	 * @method token
	 * @param  [type] $name [description]
	 * @return [string]       [token]
	 * @author NewFuture
	 */
	public static function token($key)
	{
		$timeout = 600;
		return self::handler()->getToken($key, $timeout);
	}

	/**
	 * 文件名过滤
	 * @method filterName
	 * @param  [type]     $name [description]
	 * @return [type]           [description]
	 * @author NewFuture
	 */
	public static function filterName($name)
	{
		if ($name = self::parseName($name))
		{
			static $exts;
			empty($exts) AND $exts = explode(',', Config::get('upload.exts'));
			return in_array($name['ext'], $exts) ? (implode('.', $name)) : false;
		}
	}

	/**
	 * 文件名解析
	 * @method parseName
	 * @param  string   $name [文件名]
	 * @return array         [url]
	 * @author NewFuture
	 */
	public static function parseName($name)
	{
		if (!$ext = strrchr($name, '.'))
		{
			return false;
		}
		$end = min(32, mb_strlen($name)) - strlen($ext);
		return array(
			'base' => mb_substr($name, 0, $end),
			'ext' => substr($ext, 1),
		);
	}

	/**
	 * 获取操作
	 * @method handler
	 * @author NewFuture
	 */
	private static function handler()
	{
		if (!self::$_handler)
		{
			$type           = Config::get('upload.type');
			$uploader       = 'Service\\' . $type;
			self::$_handler = new $uploader(Config::getSecret($type));
		}
		return self::$_handler;
	}
}