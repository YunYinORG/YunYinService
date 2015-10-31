<?php
class UserController extends Rest
{

	/*欢迎信息*/
	public function indexAction()
	{
		if ($id = Auth::id())
		{
			$user = UserModel::field('name,sch_id,id')->find($id);
			$info = ['msg' => '您已经成功登录', 'user' => $user];
			$this->response(1, $info);
		}
		else
		{
			$this->response(0, ['info' => '尚未登录', 'url' => '/auth/']);
		}
	}

	/**
	 * 注册
	 * @method POST_indexAction
	 * @param  string      $name [description]
	 * @return [type]            [description]
	 * @author NewFuture
	 */
	public function POST_indexAction()
	{
		if (!$regInfo = Session::get('reg'))
		{
			$this->response(0, '注册信息失效');
		}
		else
		{
			/*检查密码*/
			if (Input::post('password', $password, 'isMD5'))
			{
				$msg = '成功设置了新的密码作为云印密码！';
			}
			else
			{
				$msg      = '使用刚才的验证密码作为运用登陆密码！';
				$password = $regInfo['password'];
			}

			/*开始注册*/
			$regInfo['password'] = Encrypt::encryptPwd($password, $regInfo['number']);

			if ($id = UserModel::insert($regInfo))
			{
				/*注册成功*/
				$msg .= '(如果下次忘记密码后可以通过 手机,邮箱或者再次认证找回密码)';
				$regInfo['id'] = $id;
				$token         = Auth::token($regInfo);
				Cookie::set('token', $token);
				unset($regInfo['password']);
				Session::del('reg');
				Session::set('user', $regInfo);
				$this->response(1, ['user' => $regInfo, 'token' => $token, 'msg' => $msg]);
			}
			else
			{
				$this->response(0, '注册失败');
			}
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
		$id   = $this->auth($id);
		$user = UserModel::belongs('school')->field('name,number,phone,email')->find($id);
		$user = UserModel::mask($user);
		$this->response(1, $user);
	}

	/**
	 * 修改用户信息[密码]
	 * PUT /user/1
	 * @method PUT_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function PUT_infoAction($id = 0)
	{
		$id = $this->auth($id);

		$response['status'] = 0;
		if (!Input::put('password', $password, 'isMd5'))
		{
			$response['info'] = '新的密码格式不对';
		}
		elseif (!Input::put('old', $old_pwd, 'isMd5'))
		{
			$response['info'] = '请输入原密码';
		}
		else
		{
			/*数据库中读取用户数据*/
			$user   = UserModel::field('password,number')->find($id);
			$number = $user->number;
			if (!$user || Encrypt::encryptPwd($old_pwd, $number) != $user['password'])
			{
				$response['info'] = '原密码错误';
			}
			elseif (UserModel::set('password', Encrypt::encryptPwd($password, $number))->save($id) >= 0) //修改数据
			{
				$response['info']   = '修改成功';
				$response['status'] = 1;
			}
			else
			{
				$response['info'] = '修改失败';
			}
		}
		$this->response = $response;
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
		$id    = $this->auth($id);
		$user  = UserModel::field('number,phone')->find($id);
		$phone = $user ? Encrypt::decryptPhone($user['phone'], $user['number'], $id) : null;
		$this->response(1, $phone);
	}

	/**
	 * 绑定用户手机，发送验证码
	 * PUT /user/1/phone {phone:"13888888888"}
	 * @method GET_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function POST_phoneAction($id = 0)
	{
		$id = $this->auth($id);

		$response['status'] = 0;
		if (!Input::post('phone', $phone, 'phone'))
		{
			$response['info'] = '手机号码无效';
		}
		elseif (UserModel::getByPhone($phone))
		{
			$response['info'] = '已经绑定过用户';
		}
		elseif (!Safe::checkTry('bind_phone_' . $id))
		{
			$response['info'] = '发送次数过多,稍后重试';
		}
		else
		{
			/*手机有效，发送验证码*/
			$code = Random::number(6);
			Session::set('code_phone', [$code => $phone]);
			if (Sms::bind($phone, $code))
			{
				$response['status'] = 1;
				$response['info']   = '发送成功[最多可重发5次]';
			}
			else
			{
				$response['info'] = '短信发送出错[最多可重发5次]';
			}
		}
		$this->response = $response;
	}

	/**
	 * 修改用户手机
	 * PUT /user/1/phone {code:"C09E"}
	 * @method GET_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function PUT_phoneAction($id = 0)
	{
		$id = $this->auth($id);

		$response['status'] = 0;
		if (!Input::put('code', $code, 'ctype_alnum')) //数字或者字母
		{
			$response['info'] = '验证码格式不对';
		}
		elseif (!$verify = Session::get('code_phone'))
		{
			$response['info'] = '验证码已过期,请重新生成';
		}
		elseif (!Safe::checkTry('phone_code_' . $id))
		{
			$response['info'] = '此验证码尝试次数过多,请重新发送短信';
			Session::del('code_phone');
		}
		elseif (key($verify) != strtoupper($code))
		{
			$response['info'] = '验证码错误';
		}
		else
		{
			session::del('code_phone');
			Safe::del('phone_code_' . $id);
			$phone = $verify[strtoupper($code)]; //读取号码
			if (UserModel::SavePhone($phone))
			{
				$response['info']   = '手机号已经更新';
				$response['status'] = 1;
			}
			else
			{
				$response['info'] = '手机号保存失败';
			}
		}
		$this->response = $response;
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
		$id    = $this->auth($id);
		$email = UserModel::where('id', '=', $id)->get('email');
		$email = $email ? Encrypt::decryptEmail($email) : null;
		$this->response(1, $email);
	}

	/**
	 * 绑定邮箱，发送邮箱验证信息
	 * PUT /user/1/email {email:"xx@mail.yunyin.org"}
	 * @method GET_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function POST_emailAction($id = 0)
	{
		$id = $this->auth($id);

		$response['status'] = 0;
		if (!Input::post('email', $email, 'email'))
		{
			$response['info'] = '无效邮箱';
		}
		elseif (UserModel::getByEmail($email))
		{
			$response['info'] = '已经绑定过用户';
		}
		elseif (!Safe::checkTry('bind_email_' . $id))
		{
			$response['info'] = '发送次数过多,12小时之后重试';
		}
		else
		{
			/*生成验证码*/
			$name = UserModel::where('id', $id)->get('name');
			$code = ['use_id' => $id, 'type' => 1];
			$Code = new Model('code');
			$Code->delete($code);
			$code['code']    = $id . '_' . Random::word(16);
			$code['content'] = $email;
			/*发送邮件*/
			if ($Code->insert($code) && Mail::sendVerify($email, $code['code'], $name))
			{
				$response['status'] = 1;
				$response['info']   = '验证邮件成功发送至：' . $email;
			}
			else
			{
				$response['info'] = '邮件发送出错[最多还可重发' . Config::get('try.times') . '次]';
			}
		}
		$this->response = $response;
	}

	/**
	 * 修改用户邮箱
	 * PUT /user/1/phone {code:"C09Eaf"}
	 * @method GET_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function PUT_emailAction($id = 0)
	{
		$id = $this->auth($id);

		$response['status'] = 0;
		$Code               = new Model('code');
		if (!Input::put('code', $code, 'ctype_alnum')) //数字或者字母
		{
			$response['info'] = '验证码格式不对';
		}
		elseif (!$Code->where('use_id', $id)->where('type', 1)->field('id,time,code,content')->find())
		{
			$response['info'] = '验证信息不存在';
		}
		elseif (!Safe::checkTry('email_code_' . $id))
		{
			$Code->delete();
			Safe::del('email_code_' . $id);
			$response['info'] = '尝试次数过多,请重新验证';
		}
		elseif (strtoupper($code) != strtoupper(substr($Code['code'], 2, 6)))
		{
			$response['info'] = '验证码不匹配';
		}
		elseif (!UserModel::saveEmail($Code['content'], $Code['use_id']))
		{
			$response['info'] = '邮箱绑定失败';
		}
		else
		{
			$Code->delete();
			Safe::del('email_code_' . $id);
			$response['info']   = $Code['content'];
			$response['status'] = 1;
		}
		$this->response = $response;
	}
}