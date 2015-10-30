<?php
/**
 *登录和验证
 */
class AuthController extends Rest
{
	/**
	 * 登录注册验证
	 * @method indexAction
	 * @return [type]     [description]
	 * @author NewFuture
	 */
	public function indexAction()
	{
		if (Input::post('number', $number, 'card') && Input::post('password', $password, 'trim'))
		{
			Input::post('sch_id', $sch_id, 'int');
			$safekey = $sch_id . 'auth_' . $number;
			if (!Safe::checkTry($safekey, 5))
			{
				$this->response(0, '尝试次过度,账号临时封禁');
			}
			elseif (Input::post('code', $code, 'ctype_alnum'))
			{
				/*输入验证码直接验证*/
				if ($this->verify($number, $password, $sch_id, $code))
				{
					/*验证通过*/
					Safe::del($safekey);
				}
				else
				{
					$this->response(-1, '学校账号验证失败,请检查密码是否正确,您也可尝试登录该系统!');
				}
			}
			elseif ($result = $this->login($number, md5($password), $sch_id))
			{
				/*登录成功*/
				Safe::del($safekey);
			}
			elseif ($sch_id && false === $result)
			{
				/*指定学校后登录失败*/
				$this->response(-1, '登录失败！请检查学号和密码是否正确，或者找回密码！');
			}
			elseif ($this->verify($number, $password, $sch_id)) //尝试验证
			{
				/*验证成功*/
				Safe::del($safekey);
			}
			else
			{
				/*注册验证失败*/
				$this->response(-1, '验证出错,请检查学号或者密码是否正确!');
			}
		}
		else
		{
			$this->response(-1, '学号或者密码无效!');
		}
	}

	/**
	 * 注销
	 * @method logout
	 * @return 重定向或者json字符
	 */
	public function logoutAction()
	{
		Cookie::flush();
		Session::flush();
		$this->response(1, '注销成功!');
	}

	/**
	 * 登录函数
	 * @method login
	 * @access private
	 * @author NewFuture[newfuture@yunyin.org]
	 * @param  [string]   $password    [md5密码]
	 * @return [bool/int] [用户id]
	 */
	private function login($number, $password, $sch_id = null)
	{
		$conditon = ['number' => $number];
		//指定学校
		$sch_id AND $conditon['sch_id'] = $sch_id;

		$users = UserModel::where($conditon)->select('id,password,sch_id,name');
		if (empty($users))
		{
			/*未注册*/
			return null;
		}
		else
		{
			/*验证结果*/
			$password    = Encrypt::encryptPwd($password, $number);
			$reg_schools = [];
			foreach ($users as &$user)
			{
				if ($user['password'] == $password)
				{
					/*登录成功*/
					unset($user['password']);
					$user['number'] = $number;
					$token          = Auth::token($user);
					$sessionid      = Session::start();
					Session::set('user', $user);
					Cookie::set('token', $token);
					$result = ['sid' => $sessionid, 'user' => $user, 'msg' => '登录成功！', 'token' => $token];
					$this->response(1, $result);
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
	 * @access public
	 * @author NewFuture[newfuture@yunyin.org]
	 * @return bool|null
	 */
	public function verify($number, $password, $sch_id = null, $code = null)
	{
		$info = array(
			'number' => $number,
			'password' => $password,
			'sch_id' => $sch_id,
		);
		$code AND $info['code'] = $code; //验证码

		/*黑名单*/
		$black = isset($this->reg_schools) ? $this->reg_schools : [];

		if (!$result = School::verify($info, $black))
		{
			return false;
		}
		elseif ($result = array_filter($result))
		{
			/*验证成功*/
			$reg = array(
				'number' => $info['number'],
				'password' => md5($info['password']),
				'name' => current($result),
				'sch_id' => key($result));
			$sid = Session::start();
			Session::set('reg', $reg);
			unset($reg['password']);
			$this->response(2, ['sid' => $sid, 'user' => $reg, 'msg' => '验证成功', 'url' => '/user/']);
			return true;
		}
	}
}
?>