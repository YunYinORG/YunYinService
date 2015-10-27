<?php
/**
 * 密码管理
 */
class PasswordController extends Rest
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
		elseif (!Input::post('account', $account, Config::get('regex.account')))
		{
			$response['info'] = '账号有误，如果账号忘记请联系云印工作人员';
		}
		elseif (!$Printer = PrinterModel::where('account', $account)->field('id,phone')->find())
		{
			$response['info'] = '尚未注册';
		}
		elseif (!Safe::checkTry('pwd_phone_' . $account))
		{
			$response['info'] = '尝试次数过多，临时封禁！';
		}
		elseif (!$Printer['phone'] || ($Printer['phone'] != $phone))
		{
			$response['info'] = '绑定手机不一致,或者手机号错误';
		}
		elseif (!Sms::findPwd($phone, $code = Random::number(6)))
		{
			$response['info'] = '短信发送出错,请联系我们！';
		}
		else
		{
			/*发送成功*/
			$find = ['id' => $Printer['id'], 'account' => $account, 'code' => strtoupper($code)];
			Session::set('find_info_p', $find);
			Safe::del('pwd_phone_' . $account);
			$response['status'] = 1;
			$response['info']   = '验证短信已发送';
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
		elseif (!Input::post('account', $account, Config::get('regex.account')))
		{
			$response['info'] = '学号格式有误！';
		}
		elseif (!Safe::checkTry('pwd_email_' . $account))
		{
			$response['info'] = '尝试次数过多，临时封禁！';
		}
		elseif (!$Printer = PrinterModel::where('account', $account)->field('id,email')->find())
		{
			$response['info'] = '尚未注册,或者账号错误';
		}
		elseif (empty($Printer['email']))
		{
			$response['info'] = '未绑定邮箱,或邮箱不存在';
		}
		elseif ($Printer['email'] != $email)
		{
			$response['info'] = '绑定邮箱不一致,或者邮箱错误';
		}
		elseif (!Mail::findPwd($email, $code = Random::code(6)))
		{
			$response['info'] = '邮件发送出错,请联系我们！';
		}
		else
		{
			/*发送成功*/
			$find = ['id' => $user['id'], 'account' => $account, 'code' => strtoupper($code)];
			Session::set('find_info_p', $find);
			Safe::del('pwd_email_' . $account);
			$response['status'] = 1;
			$response['info']   = '验证邮件已发送!';
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
		elseif (!$info = Session::get('find_info_p'))
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
				Session::del('find_info_p');
			}
			else
			{
				$info['t'] = $times;
				Session::set('find_info_p', $info);
			}
		}
		else
		{
			Session::del('find_info_p');
			Session::set('find_printer', ['id' => $info['id'], 'account' => $info['account']]);
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
		elseif (!$printer = Session::get('find_printer'))
		{
			$response['info'] = '未验证或者验证信息过期';
		}
		else
		{
			$printer['password'] = Encrypt::encryptPwd($password, $printer['account']);
			if (PrinterModel::where('id', $printer['id'])->update($printer) >= 0)
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
	 * 重置密码
	 * @method POST_printerAction
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
			$printer = PrinterModel::field('password,account')->find($id);
			$account = $printer['account'];
			if (!$printer || Encrypt::encryptPwd($old_pwd, $account) != $printer['password'])
			{
				$response['info'] = '原密码错误';
			}
			elseif ($printer->update(['password' => Encrypt::encryptPwd($password, $account)]) >= 0) //修改数据
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
}
?>