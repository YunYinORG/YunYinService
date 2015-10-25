<?php
/**
 * 打印店获取用户信息接口
 * 必须有文件在此打印方可获取用户信息
 */
class UserController extends Rest
{

	/**
	 * 获取用户信息
	 * GET /user/1
	 * @method GET_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function GET_infoAction($id = 0)
	{
		$pid = $this->authPrinter();
		if (TaskModel::where('use_id', $id)->where('pri_id', $pid)->find())
		{
			$user = UserModel::field('name,number,phone,email,sch_id')->find($id);
			$user = UserModel::mask($user);
			$this->response(1, $user);
		}
		else
		{
			$this->response(0, '此同学未在此打印过');
		}
	}

	/**
	 * 获取用户真实手机
	 * GET /user/1/phone
	 * @method GET_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function GET_phoneAction($id = 0)
	{
		$pid = $this->authPrinter();
		if (TaskModel::where('use_id', $id)->where('pri_id', $pid)->get('id'))
		{
			$user  = UserModel::field('number,phone')->find($id);
			$phone = $user ? Encrypt::decryptPhone($user['phone'], $user['number'], $id) : null;
			$this->response(1, $phone);
		}
		else
		{
			$this->response(0, '此同学未在此打印过');
		}

	}

	/**
	 * 获取用户真实手机
	 * GET /user/1/email
	 * @method GET_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function GET_emailAction($id = 0)
	{
		$pid = $this->authPrinter();
		if (TaskModel::where('use_id', $id)->where('pri_id', $pid)->get('id'))
		{
			$email = UserModel::where('id', '=', $id)->get('email');
			$email = $email ? Encrypt::decryptEmail($email) : null;
			$this->response(1, $email);
		}
		else
		{
			$this->response(0, '此同学未在此打印过');
		}
	}
}