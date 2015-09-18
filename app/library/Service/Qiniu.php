<?php
namespace Service;
/**
 * 上传文件管理
 * 封装七牛API
 */
class Qiniu
{
	const QINIU_RS = 'http://rs.qbox.me';

	private $_config = null;

	public function __construct($config)
	{
		$this->_config = $config;
	}

	/**
	 * 获取文件
	 * @method download
	 * @param  string $name  [文件名]
	 * @param  string $param [附加参数]
	 * @return string        url
	 * @author NewFuture
	 */
	public function download($name, $param = [])
	{
		$url   = $this->_config['domain'] . '/' . $name . '?' . http_build_query($param);
		$token = $this->sign($url);
		return $url . '&token=' . $token;
	}

	/**
	 * 重命名【移动】
	 * @method move
	 * @param  [type] $from     [description]
	 * @param  [type] $to [description]
	 * @author NewFuture
	 */
	public function move($from, $to)
	{
		$bucket = $this->_config['bucket'];
		$op     = '/move/' . self::qiniuEncode($bucket . ':' . $from) . '/' . self::qiniuEncode($bucket . ':' . $to);
		return self::opration($op);
	}

	/**
	 * 复制文件
	 * @method copy
	 * @param  [type] $file     [description]
	 * @param  [type] $copyName [description]
	 * @return [type]           [description]
	 * @author NewFuture
	 */
	public function copy($file, $copyName)
	{
		$bucket = $this->_config['bucket'];
		$op     = '/copy/' . self::qiniuEncode($bucket . ':' . $file) . '/' . self::qiniuEncode($bucket . ':' . $copyName);
		return self::opration($op);
	}

	/**
	 * 获取token
	 * @method getToken
	 * @param  [type]   $key     [description]
	 * @param  integer  $timeout [description]
	 * @return [type]            [description]
	 * @author NewFuture
	 */
	public function getToken($key, $timeout = 600)
	{
		$setting = array(
			'scope' => $this->_config['bucket'] . ':' . $key,
			'deadline' => $timeout + $_SERVER['REQUEST_TIME'],
			'fsizeLimit' => \Config::get('upload.max'),
		);
		$setting = self::qiniuEncode(json_encode($setting));
		return $this->sign($setting) . ':' . $setting;
	}

	/**
	 * 删除
	 * @method delete
	 * @param  string $file [文件名]
	 * @return bool      [description]
	 * @author NewFuture
	 */
	public function delete($file)
	{
		$file = self::qiniuEncode($this->_config['bucket'] . ':' . trim($file));
		return self::opration('/delete/' . $file);
	}

	/**
	 * 七牛操作
	 * @method opration
	 * @param  string   $op [操作命令]
	 * @return bool     	[操作结果]
	 * @author NewFuture
	 */
	private function opration($op)
	{
		$token  = $this->sign($op . PHP_EOL);
		$url    = self::QINIU_RS . $op;
		$header = array('Authorization: QBox ' . $token);

		if ($ch = curl_init($url))
		{
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POST, true);
			// curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$response = curl_exec($ch);
			$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if ($status == 200)
			{
				return true;
			}
			elseif (\Config::get('isdebug'))
			{
				/*操作出错*/
				\PC::debug($response, '七牛请求出错');
			}
		}
		\Log::write('[QINIU]七牛错误' . $url . ':' . ($response ?: '请求失败'), 'ERROR');
		return false;
	}

	/**
	 * 获取url签名
	 * @method sign
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 * @author NewFuture
	 */
	private function sign($url)
	{
		$sign = hash_hmac('sha1', $url, $this->_config['secretkey'], true);
		$ak   = $this->_config['accesskey'];
		return $ak . ':' . self::qiniuEncode($sign);
	}

	/**
	 * 七牛安全编码
	 */
	private static function qiniuEncode($str)
	{
		return strtr(base64_encode($str), ['+' => '-', '/' => '_']);
	}
}