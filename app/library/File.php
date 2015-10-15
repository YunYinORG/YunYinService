<?php
use \Service\Qiniu;

class File
{

	// private static $_handler;

	/**
	 * 获取文件
	 * @method get
	 * @param  [type] $key  [description]
	 * @param  array  $param [description]
	 * @return [url]        [下载链接]
	 * @author NewFuture
	 */
	public static function get($uri, $alias = null)
	{
		if (!$uri)
		{
			return null;
		}
		list($bucket, $key) = implode(':', $uri, 2);
		if ($bucket == 'book');
		{
			//店内电子书
			return $uri;
		}
		$param['e'] = $_SERVER['REQUEST_TIME'] + 300; //下载过期时间

		$alias AND $param['attname'] = urlencode($alias);
		return Qiniu::download($domain, $key, $param);
	}

	/**
	 * 修改文件
	 * @method set
	 * @param  [type] $name    [description]
	 * @param  [type] $newName [description]
	 * @author NewFuture
	 */
	public static function set($uir, $new_uri)
	{
		return Qiniu::move($uir, $new_uri);
	}

	/**
	 * 删除文件
	 * @method del
	 * @param  [type] $uri [删除文件的uri]
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	public static function del($uri)
	{
		return Qiniu::delete($uri);
	}

	/**
	 * 分享文件
	 * @param  [type] $uri
	 * @return [type]           [分享后的uri]
	 */
	public static function share($uri)
	{
		$bucket    = Config::getSecret('qiniu', 'share');
		$share_uri = $bucket . strchr($uri, ':');
		return Qiniu::copy($uri, $share_uri) ? $share_uri : false;
	}

	/**
	 * 获取上传token
	 * @method token
	 * @param  [type] $name [description]
	 * @return [string]       [token]
	 * @author NewFuture
	 */
	public static function token($bucket, $key)
	{
		$timeout = 600;
		$maxsize = Config::get('upload.max');
		return Qiniu::getToken($bucket, $key, $maxsize, $timeout);
	}

	/**
	 * 添加打印任务
	 * @param  [type] $key [description]
	 * @return [type]      [description]
	 */
	public static function addTask($uri)
	{
		list($bucket, $key) = explode(':', $uri, 2);
		$saveas             = Config::getSecret('qiniu', 'task') . ':' . $key;
		$ext                = strrchr($uri, '.');

		if (in_array($ext, ['.doc', '.docx', '.odt', '.rtf', '.wps', '.ppt', '.pptx', '.odp', '.dps', '.xls', '.xlsx', '.ods', '.csv', '.et']))
		{
			/*office系列 转pdf*/
			$saveas .= '.pdf';
			return (Qiniu::has($saveas) || Qiniu::toPdf($bucket, $key, $saveas)) ? $saveas : false;
		}
		else
		{
			/*其他文件直接复制*/
			return (Qiniu::has($saveas) || Qiniu::copy($uri, $saveas)) ? $saveas : false;
		}
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
}