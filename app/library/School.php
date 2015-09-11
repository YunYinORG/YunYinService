<?php
/**
 * 验证
 */
class School
{
	private static $_ip = null;

	/**
	 * 获取客户端IP
	 * @method getIP
	 * @return array [请求的ip,头信息中ip]
	 * @author NewFuture
	 */
	public static function getIP()
	{
		if (null == self::$_ip)
		{
			$request_ip = getenv('REMOTE_ADDR');
			$orign_ip   = getenv('HTTP_X_FORWARDED_FOR') ?: getenv('HTTP_CLIENT_IP');
			self::$_ip  = [ip2long($request_ip), ip2long($orign_ip)];
		}
		return self::$_ip;
	}

	/**
	 * 验证学校
	 * @method verify
	 * @param  array  $student   [description]
	 * @return [mixed]          [description]
	 * @author NewFuture
	 */
	public static function verify($student, $except = [])
	{
		$param = [$student['number'], $student['password'], isset($student['code']) ? $student['code'] : null];
		if (isset($student['sch_id']) && $sch_id = $student['sch_id'])
		{
			if ($school = self::getAbbr($sch_id))
			{
				return [$sch_id => call_user_func_array(array('Verify\\' . strtoupper($school), 'getName'), $param)];
			}
		}
		else
		{
			$list = self::guess($student['number'], $except);
			foreach ($list as $i => $school)
			{
				$list[$i] = call_user_func_array(array('Verify\\' . strtoupper($school), 'getName'), $param);
			}
			return $list;
		}
	}

	/**
	 * 获取验证码
	 * @method code
	 * @param  [type] $id [学校id]
	 * @return [type]     [description]
	 * @author NewFuture
	 */
	public static function code($id)
	{
		if ($school = self::getAbbr($id))
		{
			return call_user_func(array('Verify\\' . strtoupper($school), 'getCode'));
		}
	}

	/**
	 * 猜测用户学校
	 * @method guess
	 * @param  [type] $number     [description]
	 * @param  array  $black_list [排除的黑名单]
	 * @param  array  $white_list [过滤范围]
	 * @return [type]             [description]
	 * @author NewFuture
	 */
	public static function guess($number, $black_list = array(), $white_list = null)
	{
		if (!Validate::card($number))
		{
			return false;
		}

		if (is_array($white_list))
		{
			$list = $white_list;
		}
		else
		{
			$list = self::_getList();
		}

		if (!empty($black_list))
		{
			$list = array_diff($list, $black_list);
		}

		if (!$list = self::filtByNumber($number, $list))
		{
			return false;
		}
		$list = self::filtByIP($list);
		return $list;
	}

	/**
	 * 获取学校的缩写
	 * @method getAbbr
	 * @param  [type]  $id [description]
	 * @return [type]      [description]
	 * @author NewFuture
	 */
	public static function getAbbr($id)
	{
		$list = self::_getList();
		return isset($list[$id]) ? $list[$id] : false;
	}

	/**
	 * [_getList description]
	 * @method _getList
	 * @return [type]   [description]
	 * @access private
	 * @author NewFuture
	 */
	private static function _getList()
	{
		if (!$list = Cache::get('v_school_list'))
		{
			$schools = SchoolModel::where('id', '>', 0)->select('id,abbr');
			foreach ($schools as $school)
			{
				$list[$school['id']] = strtolower($school['abbr']);
			}
			Cache::set('v_school_list', $list, 864000);
		}
		return $list;
	}

	/**
	 * 根据学号格式过滤
	 * @method filtByNumber
	 * @param  [type]       $number [description]
	 * @param  [type]       &$list  [description]
	 * @return [type]               [description]
	 * @author NewFuture
	 */
	private static function filtByNumber($number, &$list)
	{
		$regex = Config::get('regex.number');
		foreach ($list as $i => $school)
		{
			if (!preg_match($regex[$school], $number))
			{
				unset($list[$i]);
			}
		}
		return $list;
	}

	/**
	 * 根据IP过滤
	 * @method filtByIP
	 * @param  [type]   &$list [description]
	 * @return [type]          [description]
	 * @author NewFuture
	 */
	private static function filtByIP(&$list)
	{
		return $list;
	}
}