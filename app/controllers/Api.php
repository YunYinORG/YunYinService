<?php

/**
 * 待完善
 */
class ApiController extends Yaf_Controller_Abstract
{

	public function indexAction()
	{
	}

	/**
	 * 登录注册验证
	 * @method indexAction
	 * @return [type]     [description]
	 * @author NewFuture
	 */
	public function authAction()
	{
		if (Input::post('number', $this->number, 'card') && Input::post('password', $this->password, 'trim'))
		{
			// Input::I('redirect', $url, FILTER_VALIDATE_URL);
			Input::post('sch_id', $this->sch_id, 'int');

			/*尝试登录*/
			$result = $this->login(md5($this->password));
			if ($result)
			{
				/*登录成功*/
				$this->success('登录成功', '/user/index');
			}
			elseif ($this->sch_id && false === $result)
			{
				/*登录失败*/
				$this->error('登录失败', '/auth/');
			}
			elseif ($this->verify()) //尝试验证
			{
				/*验证成功*/
				$response         = ['status' => 0];
				$response['info'] = ['msg' => '验证成功,继续完成注册', 'url' => '/auth/register'];
				$this->json($response);
			}
			else
			{
				/*验证失败*/
				$this->error('验证出错', '/auth/');
			}
		}
		else
		{
			$this->error('账号或者密码错误', '/');
		}
	}

	/**
	 * 确认注册【设定密码】
	 * @method registerAction
	 * @return [type]         [description]
	 * @author NewFuture
	 */
	public function registerAction()
	{
		if ($regInfo = Session::get('reg'))
		{
			Session::del('reg');

			if (Input::post('password', $password, 'isMD5') === false)
			{
				/*密码未md5*/
				$this->error('密码通讯不安全', '/auth/register');
			}
			elseif (!$password)
			{
				/*未设置密码*/
				$password = $regInfo['password'];
			}
			$regInfo['password'] = Encrypt::encryptPwd($password, $regInfo['number']);
			if ($id = UserModel::insert($regInfo))
			{
				/*注册成功*/
				$regInfo['id'] = $id;
				$token         = Auth::token($regInfo);
				Cookie::set('token', [$id => $token]);
				unset($regInfo['password']);
				Session::set('user', $regInfo);
				$this->success('注册成功!', '/user/index');
			}
			else
			{
				$this->error('注册失败!');
			}
		}
		else
		{
			$this->error('注册信息失效', '/auth/');
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
		$this->success('注销成功', $url);
	}

	/**
	 * 验证码
	 * @method codeAction
	 * @param  integer    $sch_id [学校id]
	 * @return [image]            [description]
	 * @author NewFuture
	 */
	public function codeAction($sch_id = 0)
	{
		$code = School::code($sch_id);
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

			if (!$Code->where('code', $code)->field('use_id,type,content')->find())
			{
				$this->jump('/', '验证信息不存在', 10);
			}
			elseif (!UserModel::saveEmail($Code['content'], $Code['use_id']))
			{
				$this->jump('/', '邮箱设置失败', 10);
			}
			else
			{
				$Code->delete();
				$this->jump('/', '邮箱绑定成功', 3);
			}
		}
		else
		{
			$this->jump('/', '验证码信息有误', 10);
		}
	}

	/**
	 * 登录函数
	 * @method login
	 * @access private
	 * @author NewFuture[newfuture@yunyin.org]
	 * @param  [string]   $password    [md5密码]
	 * @return [bool/int] [用户id]
	 */
	private function login($password)
	{
		$conditon = ['number' => $this->number];
		//指定学校
		$this->sch_id AND $conditon['sch_id'] = $this->sch_id;

		$users = UserModel::where($conditon)->select('id,password,sch_id');
		if (empty($users))
		{
			/*未注册*/
			return null;
		}
		else
		{
			/*验证结果*/
			$password    = Encrypt::encryptPwd($password, $this->number);
			$reg_schools = [];
			foreach ($users as &$user)
			{
				if ($user['password'] == $password)
				{
					/*登录成功*/
					$user['number'] = $this->number;
					Cookie::set('token', [$user['id'] => Auth::token($user)]);
					unset($user['password']);
					Session::set('user', $user);
					return true;
				}
				else
				{
					/*验证失败*/
					$sid               = $user['sch_id'];
					$reg_schools[$sid] = School::getAbbr($sid);
				}
			}
			$this->reg_schools = $reg_schools;
			return false;
		}
	}

	/**
	 * 验证准备注册
	 * @method verify
	 * @access private
	 * @author NewFuture[newfuture@yunyin.org]
	 * @return bool|null
	 */
	public function verify()
	{
		$info = array(
			'number' => $this->number,
			'password' => $this->password,
			'sch_id' => $this->sch_id,
		);

		if (Input::post('code', $code, 'ctype_alnum'))
		{
			/*验证码*/
			$info['code'] = $code;
		}
		$black = isset($this->reg_schools) ? $this->reg_schools : [];
		if ($result = School::verify($info, $black))
		{
			foreach ($result as $sid => $name)
			{
				if ($name)
				{
					/*验证成功*/
					$regInfo = array(
						'number' => $info['number'],
						'password' => md5($info['password']),
						'name' => $name,
						'sch_id' => $sid,
					);
					Session::set('reg', $regInfo);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 错误提示
	 * @method error
	 * @param  [string] $msg [信息]
	 * @param  string $url [跳转url]
	 * @return [type]      [description]
	 * @author NewFuture
	 */
	protected function error($msg, $url = '/')
	{
		if ($this->_request->isXmlHttpRequest())
		{
			$response['status'] = -1;
			$response['info']   = ['msg' => $msg, 'url' => $url];
			$this->json($response);
		}
		else
		{
			$this->jump($url, $msg);
		}
	}

	/**
	 * 成功提示
	 * @method success
	 * @param  [string]  $msg [信息]
	 * @param  [string]  $url [url]
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	protected function success($msg, $url)
	{
		if ($this->_request->isXmlHttpRequest())
		{
			$response['status'] = 1;
			$response['info']   = ['msg' => $msg, 'url' => $url];
			$this->json($response);
		}
		else
		{
			$this->jump($url, $msg);
		}
	}

	/**
	 * 输出json
	 * @method dump
	 * @param  array $response [输出信息]
	 * @author NewFuture
	 */
	protected function json($response)
	{
		header('Content-type: application/json');
		die(json_encode($response, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * 页面跳转
	 * @method jump
	 * @param  string  $url  [url]
	 * @param  string  $msg  [消息]
	 * @param  integer $time [跳转时间]
	 * @author NewFuture
	 */
	protected function jump($url, $msg = null, $time = 1)
	{
		Yaf_Dispatcher::getInstance()->autoRender(true);
		if ($msg)
		{
			$this->_view->assign('time', $time)->assign('url', $url)->assign('msg', $msg)->display('jump.phtml');
		}
		else
		{
			$this->_response->setHeader('HTTP/1.1 303 See Other', '');
			$this->redirect($url);
		}
	}
}
?>