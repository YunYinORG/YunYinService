<?php

class UserController extends Rest
{

	public function indexAction()
	{
		$this->response = (UserModel::order('id', true)->select('name AS user,number,last_login'));
	}

	/**
	 * 注册新用户
	 * @method POST_indexAction
	 * POST /user/
	 * @param
	 * number 学号
	 * password 密码
	 * sch_id 学校
	 * @author NewFuture
	 */
	public function POST_indexAction()
	{
		$post = $this->_request->getPost();

		$user = new UserModel;
		if ($user->create($post))
		{
			$response['status'] = 1;
			$response['msg']    = '注册成功';
		}
		else
		{
			$response['status'] = 0;
			$response['msg']    = $user->getError();
		}

		$this->response = $response;
	}

	/**
	 * 获取用户信息
	 * GET /user/1
	 * @method GET_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function GET_infoAction($id = 0)
	{
		$user = UserModel::field('name,number,sch_id,phone,email')->find($id);
		if ($user)
		{
			$user['school'] = SchoolModel::getName($user['sch_id']);

		}
		$this->response = $user;
	}

	/**
	 * 修改用户信息
	 * PUT /user/1
	 * @method PUT_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function PUT_infoAction($id = 0)
	{
	}
}