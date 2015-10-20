<?php
/**
 * Schoolmodel
 * 此处数据缓存为主
 * 做了特殊数量
 */
class SchoolModel
{
	/**
	 * 获取校名
	 * @method getName
	 * @param  [type]  $id [description]
	 * @return [type]      [description]
	 * @author NewFuture
	 */
	public static function getName($id)
	{
		if ($school = self::find($id))
		{
			return $school['name'];
		}
	}

	/**
	 * 获取全部学校
	 * @method all
	 * @param  boolean $cached [description]
	 * @return [type]          [description]
	 * @author NewFuture
	 */
	public static function all($field = null, $cached = true)
	{
		$t = md5($field);
		if ($cached && $schools = Cache::get('sch_all'.$t))
		{
			return $schools;
		}
		else
		{
			if ($schools = (new Model('school', 'id'))->select($field))
			{
				$school_array = [];
				foreach ($schools as $school)
				{
					$id = $school['id'];
					if ($id > 0)
					{
						$school_array[$id] = $school;
					}
				}
				Cache::set('sch_all'.$t, $school_array, 259200);
				return $school_array;
			}
			else
			{
				//TO DO exception
				return null;
			}
		}
	}

	/**
	 * 查找学校
	 * @method find
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 * @author NewFuture
	 */
	public static function find($id)
	{
		$id = intval($id);
		if ((($schools = self::all()) && isset($schools[$id]))
			|| (($schools = self::all(false)) && isset($schools[$id])))
		{
			return $schools[$id];
		}
		else
		{
			return false;
		}
	}

	/**
	 * 静态调用model的操作
	 * @author NewFuture
	 */
	public static function __callStatic($method, $params)
	{
		return call_user_func_array(array(new Model('school'), $method), $params);
	}
}