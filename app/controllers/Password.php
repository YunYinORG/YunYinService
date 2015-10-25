<?php
/**
 * 密码管理
 */
class PasswordController extends REST
{

	/**
	 * 发送手机验证码
	 * @method POST_phoneAction
	 * @author NewFuture
	 */
	public function POST_phoneAction()
	{
		$response['status'] = 0;
		if (!Input::post('phone', $phone, Config::get('regex.phone')))
		{
			$response['info'] = '手机号格式有误或者不支持！';
		}
		elseif (!Input::post('number', $number, 'card'))
		{
			$response['info'] = '学号格式有误！';
		}
		elseif (!Safe::checkTry('pwd_phone_' . $number))
		{
			$response['info'] = '尝试次数过多，临时封禁！';
		}
		elseif (!$user = UserModel::where('number', $number)->field('id,phone')->find())
		{
			$response['info'] = '尚未注册,或者学号错误';
		}
		elseif (empty($user['phone']))
		{
			$response['info'] = '未绑定手机号,或者学号错误';
		}
		elseif (Encrypt::encryptPhone($phone, $number, $user['id']) != $user['phone'])
		{
			$response['info'] = '绑定手机不一致,或者手机号错误';
		}
		elseif (!Sms::findPwd($phone, $code = Random::code(6)))
		{
			$response['info'] = '短信发送出错,请联系我们！';
		}
		else
		{
			/*发送成功*/
			$find = ['id' => $user['id'], 'number' => $number, 'code' => strtoupper($code)];
			Session::set('find_info', $find);
			Safe::del('pwd_phone_' . $number);
			$response['status'] = 1;
			$response['info']   = '短信已发送';
		}
		$this->response = $response;
	}

	/**
	 * 通过邮箱找回密码
	 * @method POST_emailAction
	 * @author NewFuture
	 */
	public function POST_emailAction()
	{

		$response['status'] = 0;
		if (!Input::post('email', $email, 'email'))
		{
			$response['info'] = '邮箱格式有误或者不支持！';
		}
		elseif (!Input::post('number', $number, 'card'))
		{
			$response['info'] = '学号格式有误！';
		}
		elseif (!Safe::checkTry('pwd_email_' . $number))
		{
			$response['info'] = '尝试次数过多，临时封禁！';
		}
		elseif (!$user = UserModel::where('number', $number)->field('id,name,email')->find())
		{
			$response['info'] = '尚未注册,或者学号错误';
		}
		elseif (empty($user['email']))
		{
			$response['info'] = '未绑定邮箱,或者学号错误';
		}
		elseif (Encrypt::decryptEmail($email) != $email)
		{
			$response['info'] = '绑定邮箱不一致,或者邮箱错误';
		}
		elseif (!Mail::findPwd($email, $code = Random::code(6), $user['name']))
		{
			$response['info'] = '邮件发送出错,请联系我们！';
		}
		else
		{
			/*发送成功*/
			$findPwd = ['id' => $user['id'], 'number' => $number, 'code' => strtoupper($code)];
			Session::set('find_info', $findPwd);
			Safe::del('pwd_email_' . $number);
			$response['status'] = 1;
			$response['info']   = '找回验证码已发送到' . $email;
		}
		$this->response = $response;
	}

	/**
	 * 验证验证码
	 * @method POST_codeAction
	 * @author NewFuture
	 */
	public function POST_codeAction()
	{
		$response['status'] = 0;
		if (!Input::post('code', $code, 'char_num'))
		{
			$response['info'] = '验证码无效';
		}
		elseif (!$info = Session::get('find_info'))
		{
			$response['info'] = '验证信息已失效,请重新发送验证码';
		}
		elseif ($info['code'] != strtoupper($code))
		{
			$response['info'] = '验证码错误';
			$times            = isset($info['t']) ? $info['t'] + 1 : 1;
			if ($times > 3)
			{
				/*一个验证码尝试超过三次强制过期*/
				Session::del('find_info');
			}
			else
			{
				$info['t'] = $times;
				Session::set('find_info', $info);
			}
		}
		else
		{
			Session::del('find_info');
			Session::set('find_user', ['id' => $info['id'], 'number' => $info['number']]);
			$response['status'] = 1;
			$response['info']   = '验证成功,请重置密码';
		}
		$this->response = $response;
	}

	/**
	 * 重置密码
	 * @method POST_indexAction
	 * @author NewFuture
	 */
	public function POST_indexAction()
	{
		$response['status'] = 0;
		if (!Input::post('password', $password, 'isMd5'))
		{
			$response['info'] = '密码无效';
		}
		elseif (!$user = Session::get('find_user'))
		{
			$response['info'] = '未验证或者验证信息过期';
		}
		else
		{
			$user['password'] = Encrypt::encryptPwd($password, $user['number']);
			if (UserModel::update($user) >= 0)
			{
				$response['status'] = 1;
				$response['info']   = '重置成功';
			}
			else
			{
				$response['info'] = '新密码保存失败';
			}
		}
		$this->response = $response;
	}

	/**
	 * 原密码重置密码
	 * @method PUT_indexAction
	 * @author NewFuture
	 */
	public function PUT_indexAction($id)
	{
		$this->auth($id);
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
			$number = $user['number'];
			if (!$user || Encrypt::encryptPwd($old_pwd, $number) != $user['password'])
			{
				$response['info'] = '原密码错误';
			}
			elseif ($user->update(['password' => Encrypt::encryptPwd($password, $number)]) >= 0) //修改数据
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
	 * 验证找回密码
	 * @method POST_verifyAction
	 * @author NewFuture
	 */
	public function POST_verifyAction()
	{
		$response['status'] = 0;
		if (!Input::post('number', $number, 'card'))
		{
			$response['info'] = '学号格式有误';
		}if (!Input::post('password', $password, 'trim'))
		{
			$response['info'] = '密码无效';
		}
		elseif (!Input::post('sch_id', $sch_id, 'int'))
		{
			$response['info'] = '学校ID无效';
		}
		elseif (!$id = UserModel::where('number', $number)->get('id'))
		{
			$response['info'] = '学号错误或者尚未注册过';
		}
		else
		{
			$info = ['number' => $number, 'password' => $password, 'sch_id' => $sch_id];
			if (Input::post('code', $code, 'ctype_alnum'))
			{
				/*验证码*/
				$info['code'] = $code;
			}

			/*学校系统验证*/
			if (School::verify($info))
			{
				/*验证成功*/
				$user['id']     = $id;
				$user['number'] = $number;
				Session::set('find_user', $user);
				$response['status'] = 1;
				$response['info']   = '验证成功，请重置密码';
			}
			else
			{
				$response['info'] = '验证失败';
			}
		}
		$this->response = $response;
	}
}
?>