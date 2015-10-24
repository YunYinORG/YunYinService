<?php
/**
 * 打印店登陆
 */
class AuthController extends Rest
{
	/**
	 * 打印店登录
	 * @method loginAction
	 * @return [type]      [description]
	 * @author NewFuture
	 */
	public function POST_indexAction()
	{
		$response['status'] = 0;
		if (!Input::post('account', $account, Config::get('regex.account')))
		{
			$response['info'] = '账号格式错误';
		}
		elseif (!Input::post('password', $password, 'isMd5'))
		{
			$response['info'] = '密码未加密处理';
		}
		elseif (!Safe::checkTry('printer_auth_' . $account))
		{
			$response['info'] = '尝试次数过多账号临时封禁,稍后重试或者联系我们';
		}
		elseif (!$Printer = PrinterModel::where('account', $account)->field('id,sch_id,password,status,name')->find())
		{
			$response['info'] = '账号错误';
		}
		elseif (Encrypt::encryptPwd($password, $account) != $Printer['password'])
		{
			$response['info'] = '密码错误';
		}
		else
		{
			Safe::del('printer_auth_' . $account);
			unset($Printer['password']);
			$sid                = Session::start();
			$response['status'] = 1;
			$response['info']   = ['sid' => $sid, 'printer' => $Printer];
		}
		$this->response = $response;
	}

	/**
	 * 注销
	 * @method logout
	 */
	public function logoutAction()
	{
		Cookie::flush();
		Session::flush();
		$this->response(1, '注销成功!');
	}
}
?>