<?php

class TaskModel extends FacadeModel
{
	protected $table = 'task';

	/**
	 * 创建task数组
	 * @method create
	 * @param  string $method [POST或者PUT]
	 * @return [array]         [description]
	 * @author NewFuture
	 */
	public static function create($method = '')
	{
		$method = $method ? $method . '.' : 'post.';
		$task   = array();
		if (Input::I($method . 'copies', $copies, 'int'))
		{
			$task['copies'] = $copies;
		}
		if (Input::I($method . 'color', $color, FILTER_VALIDATE_BOOLEAN))
		{
			$task['color'] = $color;
		}
		if (Input::I($method . 'isdouble', $is_double, FILTER_VALIDATE_BOOLEAN))
		{
			$task['isdouble'] = $is_double;
		}
		if (Input::I($method . 'ppt', $ppt, 'int'))
		{
			$task['ppt'] = $ppt;
		}
		if (Input::I($method . 'requirements', $requirements, 'text'))
		{
			$task['requirements'] = $requirements;
		}
		return $task;
	}
}