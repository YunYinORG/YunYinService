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
		if (Input::I($method . 'color', $color))
		{
			$task['color'] = $color ? 1 : 0;
		}
		if (Input::I($method . 'isdouble', $is_double))
		{
			$task['isdouble'] = $is_double ? 1 : 0;
		}
		if (Input::I($method . 'format', $ppt, 'int'))
		{
			$task['format'] = $ppt;
		}
		if (Input::I($method . 'requirements', $requirements, 'text'))
		{
			$task['requirements'] = $requirements;
		}
		return $task;
	}
}