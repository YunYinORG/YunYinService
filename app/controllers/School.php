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
	 * 学校信息
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

	/**
	 * 获取学校验证码
	 * @method GET_codeAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function GET_codeAction($id = 1)
	{
		if ($img = Verify\TJU::getCode())
		{
			$this->response(1, 'data:image/png;base64,' . base64_encode($img));
		}
		else
		{
			$this->response(0, '无需验证码');

		}
	}
}