<?php

class AuthController extends Yaf_Controller_Abstract
{

	public function init()
	{
		Yaf_Dispatcher::getInstance()->disableView(); //关闭视图模板引擎
	}

	/**
	 * 验证
	 * @method indexAction
	 * @return [type]     [description]
	 * @author NewFuture
	 */
	public function indexAction()
	{
		if (Input::I('number', $number, 'card') && Input::I('password', $password, 'trim'))
		{
			Input::I('redirect', $url, FILTER_VALIDATE_URL);
			$sch_id = $this->_request->getPost('sch_id', 0);
			// if($sch_id){}
			// TODO 学号冲突

			/*尝试登陆*/
			$login = $this->_login($number, md5($password), $sch_id);
			if ($login === true)
			{
				/*登录成功*/
				$this->forward('index', 'user', 'index');
			}
			elseif ($verify = $this->_verify($number, $password, $sch_id, $login))
			{
				/*注册验证通过*/
			}
			else
			{

			}
		}
		else
		{
			return $this->error('账号或者密码错误');
		}
	}

	/**
	 * 注销
	 * @method logout
	 * @return 重定向
	 */
	public function logoutAction()
	{
		Input::I('url', $url, FILTER_VALIDATE_URL, '/');
		Cookie::flush();
		Session::flush();
		$this->redirect($url);
	}

	/**
	 * 登录函数
	 * @method _login
	 * @access private
	 * @author NewFuture[newfuture@yunyin.org]
	 * @param  [number]   $number      [学号]
	 * @param  [string]   $password    [md5密码]
	 * @param  array      $user        [$sch_id]
	 * @return [bool/int] [用户id]
	 */
	private function _login($number, $password, $sch_id = 0)
	{

		$conditon = ['number' => $number];
		if ($sch_id)
		{
			$conditon['sch_id'] = $sch_id;
		}

		$users = UserModel::where($conditon)->field('id,password,sch_id')->select();
		if (empty($users))
		{
			return null; //未注册
		}
		else
		{
			$password        = Encrypt::encryptPwd($password, $number);
			$reg_school_list = [];
			foreach ($users as &$user)
			{
				if ($user['password'] == $password)
				{
					$user['number'] = $number;
					$user['token']  = Auth::token($user);
					// unset($user['password']);
					// $code = Auth::createCode($user);
					Session::set('user', $user);
					return true; //登录成功
				}
				else
				{
					$reg_school_list[] = $users['sch_id'];
				}
			}
			$this->msg = '密码错误!';
			return $reg_school_list;
		}
	}

	/**
	 * 验证准备注册
	 * @method _verify
	 * @access private
	 * @author NewFuture[newfuture@yunyin.org]
	 * @param  [number]   $number      [学号]
	 * @param  [string]   $password    [md5密码]
	 * @param  array      $user        [$sch_id]
	 * @return [bool/int] [用户id]
	 */
	private function _verify($number, $password, $sch_id = 0, $except = [])
	{
		if ($name = Verify\Connect::getName($number, $password, $sch_id, $except))
		{
			$regInfo = ['number' => $number, 'password' => md5($password), 'name' => $name];
			Session::set('reg', $regInfo);
			return true;
		}
		// if ($result = Verify\Connect::getName($number, $password, $sch_id, $except))
		// {
		// 	/*验证结果*/
		// 	foreach ($result as $sch => $name)
		// 	{
		// 		if ($name)
		// 		{
		// 			$regInfo = ['number' => $number, 'password' => md5($password), 'name' => $name];
		// 			Session::set('reg', $regInfo);
		// 			return true;
		// 		}
		// 	}
		// 	return array_keys($result);
		// }
	}

	protected function error($msg = '')
	{
		die($msg);
	}

	// protected function success($url)
	// {
	// 	die($url . '[OK]');
	// }
}

?>
