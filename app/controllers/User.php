<?php
class UserController extends Rest
{

	/*欢迎信息*/
	public function indexAction($name = '同学')
	{
		if (Auth::getUser())
		{
			$this->response(1, '亲爱的' . $name . ',已经成功登陆');
		}
		else
		{
			$this->response = ['status' => self::AUTH_FAIL, 'info' => '尚未登陆', 'url' => '/Auth/'];
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
		if (!Input::put('password', $password, 'password'))
		{
			$response['info'] = '新的密码格式不对';
		}
		elseif (!Input::put('old', $old_pwd, 'trim'))
		{
			$response['info'] = '请输入原密码';
		}
		else
		{
			/*数据库中读取用户数据*/
			$User = new Model('user');
			$data = $User->field('id,password,number')->find($id);
			if (!($data && Encrypt::encryptPwd($old_pwd, $data['number']) == $data['password']))
			{
				$response['info'] = '原密码错误';
			}
			elseif ($User->set('password', Encrypt::encryptPwd($password, $data['number']))->save()) //修改数据
			{
				$response['info']   = '修改成功';
				$response['status'] = 1;
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
		elseif ($try_times = Cache::get('snd_code_t' . $id) > 5)
		{
			$response['info'] = '发送此数过多,12小时之后重试';
		}
		else
		{
			/*手机有效，发送验证码*/
			$code = Random::code(6);
			session::set('code_phone', [$code => $phone]);
			if (Sms::sendCode($phone, $code))
			{
				$response['status'] = 1;
				$response['info']   = '发送成功[最多还可重发' . (5 - $try_times) . '次]';
				Cache::set('snd_code_t' . $id, $try_times + 1, 12 * 3600);
			}
			else
			{
				$response['info'] = '短信发送出错[最多还可重发' . (5 - $try_times) . '次]';
			}
		}
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
		if (!Input::post('code', $code, 'ctype_alnum')) //数字或者字母
		{
			$response['info'] = '验证码格式不对';
		}
		elseif ($verify = Session::get('code_phone'))
		{
			$response['info'] = '验证码已过期,请重新生成';
		}
		elseif ($try_times = Cache::get('try_code_t' . $id) > 3)
		{
			$response['info'] = '此验证码尝试次数过多,请重新发送短信';
			Session::del('code_phone');
		}
		elseif (key($verify) != $code)
		{
			$response['info'] = '验证码错误';
			Cache::set('try_code_t' . $id, $try_times + 1);
		}
		else
		{
			Cache::del('try_code_t' . $id);
			session::del('code_phone');
			$phone = $verify[$code]; //读取号码
			if (UserModel::SavePhone($id, $phone))
			{
				$response['info']   = '手机号已经更新';
				$response['status'] = 1;
			}
			else
			{
				$response['info'] = '手机号保存失败';
			}
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
		$id    = $this->auth($id);
		$email = UserModel::where('id', '=', $id)->get('email');
		$email = $email ? Encrypt::encryptEmail($email) : null;
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
		elseif ($try_times = Cache::get('snd_mail_t' . $id) > 8)
		{
			$response['info'] = '发送此数过多,12小时之后重试';
		}
		else
		{
			/*生成验证码*/
			$name = UserModel::where('id', $id)->get('name');
			$code = ['use_id' => $id, 'type' => 1];
			$Code = new Model('code');
			$Code->delete($code);
			$code['code'] = $id . '_' . Random::w(16);
			/*发送邮件*/
			if ($Code->insert($code) && Mail::sendVerify($email, $name, $code))
			{
				$response['status'] = 1;
				$response['info']   = '验证邮件成功发送至：' . $email . ($try_times ? '[最多还可重发' . (8 - $try_times) . '次]' : '');
				Cache::set('snd_mail_t' . $id, $try_times + 1, 12 * 3600);
			}
			else
			{
				$response['info'] = '邮件发送出错[最多还可重发' . (5 - $try_times) . '次]';
			}
		}
	}
}