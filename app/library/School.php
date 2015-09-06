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
	 * @param  array  $info   [description]
	 * @param  integer $sch_id [description]
	 * @return [mixed]          [description]
	 * @author NewFuture
	 */
	public static function verify($info, $sch_id = 0)
	{
		if ($sch_id > 0)
		{
			if ($school = SchoolModel::find($sch_id))
			{
				return call_user_func_array(array('Verify\\' . strtoupper($school['abbr']), 'getName'), $info);
			}
		}
		else
		{
			$list = self::guess($info['number']);
			foreach ($list as $i => $school)
			{
				$list[$i] = call_user_func_array(array('Verify\\' . strtoupper($school), 'getName'), $info);
			}
			return $list;
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
			$list = self::getList();
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
	 * 获取学校列表
	 * @method getList
	 * @return array
	 * @author NewFuture
	 */
	private static function getList()
	{
		if (!$list = Cache::get('v_school_list'))
		{
			$schools = SchoolModel::where('id', '>', 0)->select('id,abbr');
			foreach ($schools as $school)
			{
				$list[$school['id']] = strtolower($school['abbr']);
			}
			Cache::set('v_school_list', $list);
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