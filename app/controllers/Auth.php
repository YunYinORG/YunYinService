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
			$login  = null;
			/*尝试登陆*/
			if (!Input::post('code', $code, 'ctype_alnum'))
			{
				$login = $this->_login($number, md5($password), $sch_id);
				if ($login === true)
				{
					/*登录成功*/
					$this->forward('index', 'user', 'index');
				}
			}
			else
			{
				$student['code'] = $code;
			}

			/*学校验证*/
			$student['number']   = $number;
			$student['password'] = $password;
			$student['sch_id']   = $sch_id;
			$verify              = $this->verify($student, $login);

			if ($verify === true)
			{
				/*注册验证通过*/
				$this->register();
			}
			else
			{
				echo '验证失败';
			}
		}
		else
		{
			return $this->error('账号或者密码错误');
		}
	}

	/**
	 * 注册
	 * @method registerAction
	 * @return [type]         [description]
	 * @author NewFuture
	 */
	public function registerAction()
	{
		if ($this->verify($_POST))
		{
			/*注册验证通过*/
			$this->register();
		}
		else
		{
			$this->error('验证失败');
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
			elseif (!UserModel::saveEmail($email))
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
			$password    = Encrypt::encryptPwd($password, $number);
			$reg_schools = [];
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
					$sid               = $users['sch_id'];
					$reg_schools[$sid] = School::getAbbr($sid);
				}
			}
			$this->msg = '密码错误!';
			return $reg_schools;
		}
	}

	/**
	 * 验证准备注册
	 * @method _verify
	 * @access private
	 * @author NewFuture[newfuture@yunyin.org]
	 * @param  array      $user        [$sch_id]
	 * @return [bool/int] [用户id]
	 */
	private function verify($info, $except = null)
	{
		if ($result = School::verify($info, $except))
		{
			foreach ($result as $sid => $name)
			{
				if ($name)
				{
					$regInfo = ['number' => $info['number'], 'password' => md5($info['password']), 'name' => $name];
					Session::set('reg', $regInfo);
					return true;
				}
			}
			return $result;
		}
		return false;
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
