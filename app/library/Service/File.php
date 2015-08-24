<?php
namespace Service;
/**
 * 上传文件管理
 * 封装七牛API
 */
class File
{
	const QINIU_RS = 'http://rs.qbox.me';

	private static $_config = null;
	// private static $_type   = null;

	/**
	 * 获取文件
	 * @method get
	 * @param  string $name  [文件名]
	 * @param  string $param [附加参数]
	 * @return string        url
	 * @author NewFuture
	 */
	public static function get($name, $param = '')
	{
		$config  = self::config();
		$duetime = $_SERVER['REQUEST_TIME'] + $config['expire']; //下载过期时间
		$url     = $config['domain'] . '/' . $name;
		$url     = $url . '?' . $param . '&e=' . $duetime;
		$token   = self::sign($url);
		return $url . '&token=' . $token;
	}

	/**
	 * 重命名
	 * @method set
	 * @param  [type] $file     [description]
	 * @param  [type] $new_file [description]
	 * @author NewFuture
	 */
	public static function set($file, $new_file)
	{
		$bucket   = self::config('bucket');
		$file     = self::qiniuEncode($bucket . ':' . trim($file));
		$new_file = self::qiniuEncode($bucket . ':' . trim($new_file));
		$op       = '/move/' . $file . '/' . $new_file;
		return self::opration($op);
	}

	/**
	 * 删除
	 * @method del
	 * @param  string $file [文件名]
	 * @return bool      [description]
	 * @author NewFuture
	 */
	public static function del($file)
	{
		$file = self::qiniuEncode(self::config('bucket') . ':' . trim($file));
		return self::opration('/delete/' . $file);
	}

	/**
	 * 获取上传token
	 * @method getToken
	 * @param  string   $name [description]
	 * @return [type]         [description]
	 * @author NewFuture
	 */
	public static function getToken($name)
	{
		$timeout = 600;
		$setting = array(
			'scope' => $config['bucket'] . ':' . $name,
			'deadline' => $timeout + $_SERVER['REQUEST_TIME'],
		);
		$setting = self::qiniuEncode(json_encode($setting));
		return self::sign($setting) . ':' . $setting;
	}

	/**
	 * 七牛操作
	 * @method opration
	 * @param  string   $op [操作命令]
	 * @return bool     	[操作结果]
	 * @author NewFuture
	 */
	private static function opration($op)
	{
		$token    = self::sign($op . PHP_EOL);
		$url      = self::QINIU_RS . $op;
		$header   = array('Expect:', 'Authorization: QBox ' . $token);
		$response = Api::connect($url, $header, 'POST');

		$response_header = $response[0]; //响应头判断状态
		if (substr($response_header, 0, 12) == 'HTTP/1.1 200')
		{
			return true;
		}
		else
		{
			/*操作出错*/
			if (\Config::get('isdebug'))
			{
				\PC::debug($response);
			}
			\Log::write('七牛错误' . $url . PHP_EOL . $response[0] . PHP_EOL . $response[1], 'ERROR');
			return false;
		}
	}

	/**
	 * 获取配置
	 * @method config
	 * @param  [type] $key [description]
	 * @return [type]      [description]
	 * @author NewFuture
	 */
	private static function config($key = null)
	{
		if (null === self::$_config)
		{
			$path          = \Config::get('secret_config_path');
			$type          = \Config::get('upload.type');
			self::$_type   = $type;
			self::$_config = (new \Yaf_Config_Ini($path, $type))->toArray();
		}
		return $key ? self::$_config[$key] : self::$_config;
	}

	/**
	 * 获取url签名
	 * @method sign
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 * @author NewFuture
	 */
	private static function sign($url)
	{
		$sign = hash_hmac('sha1', $url, self::config('secretkey'), true);
		$ak   = self::config('accesskey');
		return $ak . ':' . self::qiniuEncode($sign);
	}

	private static function qiniuEncode($str)
	{
		return strtr(base64_encode($str), ['+' => '-', '/' => '_']);
	}
}