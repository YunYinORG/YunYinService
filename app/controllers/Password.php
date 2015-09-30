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
		$respone['status'] = 0;
		if (!Input::post('phone', $phone, Config::get('regex.phone ')))
		{
			$respone['info'] = '手机号格式有误或者不支持！';
		}
		elseif (!Input::post('number', $number, 'card '))
		{
			$respone['info'] = '学号格式有误！';
		}
		elseif (!Safe::checkTry('pwd_phone_' . $number))
		{
			$respone['info'] = '尝试次数过多，临时封禁！';
		}
		elseif (!$user = UserModel::where('number', $number)->field('id,phone')->find())
		{
			$respone['info'] = '尚未注册,或者学号错误';
		}
		elseif (empty($user['phone']))
		{
			$respone['info'] = '未绑定手机号,或者学号错误';
		}
		elseif (Encrypt::encryptPhone($phone, $number, $user['id']) != $user['phone'])
		{
			$respone['info'] = '绑定手机不一致,或者手机号错误';
		}
		elseif (!Sms::findPwd($phone, $code = Random::code(6)))
		{
			$respone['info'] = '短信发送出错,请联系我们！';
		}
		else
		{
			/*发送成功*/
			$findPwd = ['id' => $user['id'], 'number' => $number, 'code' => strtoupper($code)];
			Session::set('find_info', $find);
			Safe::del('pwd_phone_' . $number);
			$respone['status'] = 1;
			$respone['info']   = '短信已发送';
		}
		$this->respone = $respone;
	}

	/**
	 * 通过邮箱找回密码
	 * @method POST_emailAction
	 * @author NewFuture
	 */
	public function POST_emailAction()
	{

		$respone['status'] = 0;
		if (!Input::post('email', $email, 'email'))
		{
			$respone['info'] = '邮箱格式有误或者不支持！';
		}
		elseif (!Input::post('number', $number, 'card '))
		{
			$respone['info'] = '学号格式有误！';
		}
		elseif (!Safe::checkTry('pwd_email_' . $number))
		{
			$respone['info'] = '尝试次数过多，临时封禁！';
		}
		elseif (!$user = UserModel::where('number', $number)->field('id,email')->find())
		{
			$respone['info'] = '尚未注册,或者学号错误';
		}
		elseif (empty($user['email']))
		{
			$respone['info'] = '未绑定邮箱,或者学号错误';
		}
		elseif (Encrypt::encryptEmail($email) != $user['email'])
		{
			$respone['info'] = '绑定邮箱不一致,或者邮箱错误';
		}
		elseif (!Mail::findPwd($email, $code = Random::code(6)))
		{
			$respone['info'] = '邮件发送出错,请联系我们！';
		}
		else
		{
			/*发送成功*/
			$findPwd = ['id' => $user['id'], 'number' => $number, 'code' => strtoupper($code)];
			Session::set('find_info', $find);
			Safe::del('pwd_email_' . $number);
			$respone['status'] = 1;
			$respone['info']   = '邮件已发送';
		}
		$this->respone = $respone;
	}

	/**
	 * 验证验证码
	 * @method POST_codeAction
	 * @author NewFuture
	 */
	public function POST_codeAction()
	{
		$respone['status'] = 0;
		if (!Input::post('code', $code, 'char_num'))
		{
			$respone['info'] = '验证码无效';
		}
		elseif (!$info = Session::get('find_info'))
		{
			$respone['info'] = '验证信息已失效,请重新发送验证码';
		}
		elseif ($info['code'] != strtoupper($code))
		{
			$respone['info'] = '验证码错误';
			$times           = isset($info['t']) ? $info['t'] + 1 : 1;
			if ($times > 3)
			{
				/*一个验证码尝试超过三次强制过期*/
				Session::del('find_info');
			}
			else
			{
				$info['t'] = $times;
				Session::set('find_info', $times);
			}
		}
		else
		{
			Session::del('find_info');
			Session::set('find_user', ['id' => $info['id'], 'number' => $info['number']]);
			$respone['status'] = 1;
			$respone['info']   = '验证成功,请重置密码';
		}
		$this->respone = $respone;
	}

	/**
	 * 重置密码
	 * @method POST_indexAction
	 * @author NewFuture
	 */
	public function POST_indexAction()
	{
		$respone['status'] = 0;
		if (!Input::post('password', $password, 'isMd5'))
		{
			$respone['info'] = '密码无效';
		}
		elseif (!$user = Session::get('find_user'))
		{
			$respone['info'] = '未验证或者验证信息过期';
		}
		else
		{
			$user['password'] = Encrypt::encryptPwd($password, $number);
			if (UserModel::update($user) >= 0)
			{
				$respone['status'] = 1;
				$respone['info']   = '重置成功';
			}
			else
			{
				$respone['info'] = '新密码保存失败';
			}
		}
		$this->respone = $respone;
	}

	/**
	 * 验证找回密码
	 * @method POST_verifyAction
	 * @author NewFuture
	 */
	public function POST_verifyAction()
	{
		$respone['status'] = 0;
		if (!Input::post('number', $number, 'card'))
		{
			$respone['info'] = '学号格式有误';
		}if (!Input::post('password', $password, 'trim'))
		{
			$respone['info'] = '密码无效';
		}
		elseif (!Input::post('sch_id', $sch_id, 'int'))
		{
			$respone['info'] = '学校ID无效';
		}
		elseif (!$id = UserModel::where('number', $number)->get('id'))
		{
			$respone['info'] = '学号错误或者尚未注册过';
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
				$respone['status'] = 1;
				$respone['info']   = '验证成功';
			}
			else
			{
				$respone['info'] = '验证失败';
			}
		}
		$this->respone = $respone;
	}
}
?>