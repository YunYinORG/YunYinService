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
				$this->register();
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

	public function registerAction()
	{
		if (Input::I('number', $number, 'card') && Input::I('password', $password, 'trim'))
		{
			if (Input::I('code', $code, 'ctype_alnum'))
			{
				$name = Verify\TJU::getName($number, $password, $code);
				var_dump($name);
			}
			else
			{
				$this->error('验证码错误');
			}
		}
		else
		{
			$this->error('账号密码格式不对');
		}
	}

	/**
	 * 临时注册
	 * @method register
	 * @return [type]   [description]
	 * @author NewFuture
	 */
	public function register()
	{
		if ($regInfo = Session::get('reg'))
		{
			Session::del('reg');
			$password            = $regInfo['password'];
			$regInfo['password'] = Encrypt::encryptPwd($password, $regInfo['number']);
			if ($id = UserModel::insert($regInfo))
			{
				$regInfo['id'] = $id;
				$token         = Auth::token($regInfo);
				Cookie::set('token', [$id => $token]);
				unset($regInfo['password']);
				Session::set('user', $regInfo);
				$this->forward('index', 'user', 'index');
			}
		}
	}

	/**
	 * 注销
	 * @method logout
	 * @return 重定向或者json字符
	 */
	public function logoutAction()
	{
		Input::I('url', $url, FILTER_VALIDATE_URL, '/');
		Cookie::flush();
		Session::flush();
		if ($this->_request->isXmlHttpRequest())
		{
			$response['status'] = 1;
			$response['info']   = '注销成功';
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
		}
		else
		{
			$this->_response->setHeader('HTTP/1.1 303 See Other', '');
			$this->redirect($url);
		}
	}

	/**
	 * 验证码
	 * @method codeAction
	 * @param  integer    $sch_id [description]
	 * @return [type]             [description]
	 * @author NewFuture
	 */
	public function codeAction($sch_id = 0)
	{
		$code = Verify\TJU::getCode();
		$this->_response->setHeader('Content-type', 'image/jpeg');
		echo $code;
	}

	/**
	 * 邮箱验证
	 * @method emailAction
	 * @param  $code
	 * @author NewFuture
	 */
	public function emailAction($code = '')
	{
		if ($code && Validate::char_num($code))
		{
			$Code = new Model('code');

			if (!$Code->where('code', $code)->field('use_id,type,content AS email')->find())
			{
				echo '验证信息不存在';
			}
			elseif (UserModel::getByEmail($Code->email))
			{
				echo '邮箱绑定过';
			}
			elseif (!UserModel::set('email', Encrypt::encryptEmail($email))->save($Code->use_id))
			{
				echo '邮箱设置失败';
			}
			else
			{
				$Code->delete();
				echo '绑定成功！';
			}

		}
		else
		{
			echo ('code有误');
		}
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
					// $user['token']  = Auth::token($user);
					// 保存cookie
					Cookie::set('token', [$user['id'] => Auth::token($user)]);
					unset($user['password']);
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
	//
	//
}

?>
