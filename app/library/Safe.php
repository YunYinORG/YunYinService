<?php
/**
 * 安全防护
 */
Class Safe
{
	private static $_key = [];

	/**
	 * 检查尝试次数是否超限
	 * @method checkTry
	 * @param  [type]   $key        [description]
	 * @param  integer  $timesLimit [description]
	 * @return [type]               [description]
	 * @author NewFuture
	 */
	public static function checkTry($key, $timesLimit = 0)
	{
		$name       = 'Safe_try_' . $key;
		$times      = Cache::get($name);
		$timesLimit = $timesLimit ?: Config::get('try.times');
		if ($times >= $timesLimit)
		{
			$msg = '多次尝试警告:' . $key . 'IP信息:' . self::ip();
			Log::write($msg, 'WARN');
			return false;
		}
		else
		{
			Cache::set($name, ++$times, Config::get('try.expire'));
			return self::$_key[$key] = $times;
		}
	}

	public static function del($key)
	{
		Cache::del('Safe_try_' . $key);
	}

	public static function ip()
	{
		$request_ip = getenv('REMOTE_ADDR');
		$orign_ip   = getenv('HTTP_X_FORWARDED_FOR') ?: getenv('HTTP_CLIENT_IP');
		return $request_ip . '[client：' . $orign_ip . ']';
	}
}