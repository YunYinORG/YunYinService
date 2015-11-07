<?php

/**
 * 待完善
 */
class ApiController extends Yaf_Controller_Abstract
{

	public function indexAction()
	{
		if (Input::get('redirect', $redirect, 'url'))
		{
			Session::set('redirect', $redirect);
		}

		if ($user = Auth::getUser())
		{
			//已经登录
			if (!$redirect)
			{
				//读取seesion中记录的地址
				$redirect = Session::get('redirect');
				Session::del('redirect');
			}

			if ($redirect)
			{
				//需要重定向
				$this->redirect($redirect);
				exit();
			}
			else
			{
				//显示成功页面
				Yaf_Dispatcher::getInstance()->autoRender(false);
				$this->_view->display('choice.phtml');
			}

		}
		elseif (Session::get('reg'))
		{
			//正在注册
			$this->_view->assign('msg', '还差一步，请设置密码')->assign('reg', 1);
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
		$msg = '信息注册失败!';
		if ($regInfo = Session::get('reg'))
		{
			Session::del('reg');
			if (Input::post('password', $password, 'trim') === false)
			{
				/*密码未md5*/
				$this->error('密码错误', '/');
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
				$msg = '信息注册成功!';
			}
		}
		$this->jump('/', $msg);
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
	 * 页面跳转
	 * @method jump
	 * @param  string  $url  [url]
	 * @param  string  $msg  [消息]
	 * @param  integer $time [跳转时间]
	 * @author NewFuture
	 */
	protected function jump($url, $msg = null, $time = 1)
	{
		Yaf_Dispatcher::getInstance()->autoRender(false);
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