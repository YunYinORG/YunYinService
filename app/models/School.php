<?php

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
	public static function all($cached = true)
	{
		if ($cached && $schools = Cache::get('school_all'))
		{
			return $schools;
		}
		else
		{
			if ($schools = (new Model('school', 'id'))->select())
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
				Cache::set('school_all', $school_array, 259200);
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
}