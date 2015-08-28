<?php

class SchoolController extends Rest
{

	/**
	 * 学校列表
	 * @method GET_indexAction
	 * @author NewFuture
	 */
	public function GET_indexAction()
	{
		if ($schools = SchoolModel::all())
		{
			$this->response(1, $schools);
		}
		else
		{
			$thsi->response(0, '无法查看学校信息');
		}

	}

	/**
	 * 获取打印店详情
	 * @method GET_infoAction
	 * @param  integer          $id [description]
	 * @author NewFuture
	 */
	public function GET_infoAction($id = 0)
	{
		if ($school = SchoolModel::find($id))
		{
			$school['number'] = Config::get('regex.number.' . strtolower($school['abbr']));
			$this->response(1, $school);
		}
		else
		{
			$this->response(0, $id);
		}
	}
}