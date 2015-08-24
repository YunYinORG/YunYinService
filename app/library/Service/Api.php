<?php
namespace Service;
/**
 * 第三方服务接口
 */
class Api
{

	/**
	 * 连接远程服务器
	 * curl扩展未开启时尝试file_get_contents
	 * @method connect
	 * @param  string  $url    [服务器url地址]
	 * @param  array   $header [请求头]
	 * @param  string  $method [请求方式POST,GET]
	 * @param  string  $data   [附加数据，body]
	 * @return array($header,$body)[请求响应结果]
	 * @author NewFuture
	 */
	public static function connect($url, $header = array(), $method = 'POST', $data = '')
	{
		if (function_exists('curl_init'))
		{
			if ($ch = curl_init($url))
			{
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
				if ($method == 'POST')
				{
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				}
				curl_setopt($ch, CURLOPT_HEADER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				$response = curl_exec($ch);
				// $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				if ($response)
				{
					return explode("\r\n\r\n", $response, 2);
				}
			}
		}
		else
		{
			$opts              = array();
			$opts['http']      = array();
			$headers           = array('method' => strtoupper($method));
			$headers['header'] = $header ?: array();
			if (!empty($data))
			{
				$headers['header'][] = 'Content-Length:' . strlen($data);
				$headers['content']  = $data;
			}
			$opts['http']  = $headers;
			$response_body = @file_get_contents($url, false, stream_context_create($opts));
			if ($response_body)
			{
				return array($http_response_header, $response_body);
			}
		}

		return null;
	}

}