<?php

class UserController extends Rest
{

	public function indexAction()
	{
		$this->response = (UserModel::belongs('school')->order('name')->order('id', 1)->select('id,name AS user,number,last_login'));
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
		$post     = $this->_request->getPost();
		$number   = $this->_request->getPost('number');
		$password = $this->_request->getPost('password');
		$sch_id   = $this->_request->getPost('sch_id');
		$name     = '';
		if ($sch_id == 1)
		{
			$name = Verify\Nku::getName($number, $password);
		}
		if (!$name)
		{
			$this->response['msg'] = '验证无效';
		}
		else
		{
			$user             = new UserModel;
			$post             = $this->_request->getPost();
			$post['name']     = $name;
			$post['password'] = md5($password);
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
		$user           = UserModel::belongs('school')->field('name,number,phone,email')->find($id);
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