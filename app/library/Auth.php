<?php
class Auth
{
	/**
	 * 获取用户ID
	 * @method getId
	 * @return [type] [description]
	 * @author NewFuture
	 */
	public static function id()
	{
		if ($user = self::getUser())
		{
			return isset($user['id']) ? $user['id'] : null;
		}
	}

	/**
	 * 获取学号
	 * @method getNumber
	 * @return [type]    [description]
	 * @author NewFuture
	 */
	public static function number()
	{
		if ($user = self::getUser())
		{
			return isset($user['number']) ? $user['number'] : null;
		}
	}

	/**
	 * 获取当前用户
	 * @method getUser
	 * @return [type]  [description]
	 * @author NewFuture
	 */
	public static function getUser()
	{
		if ($user = Session::get('user'))
		{
			/*session中的信息*/
			return $user;
		}
		elseif ($tokenInfo = Cookie::get('token'))
		{
			/*解析cookie*/
			$uid = key($tokenInfo);
			if ($user = self::checkToken($uid, $tokenInfo[$uid]))
			{
				/*token有效*/
				Session::set('user', $user);
				return $user;
			}
		}
		elseif (Input::I('SERVER.HTTP_TOKEN', $token, 'token'))
		{
			/*http头中的请求*/

		}
	}

	/**
	 * 生成token
	 * @method token
	 * @param  [type] $user    [id或者包括用户id,number,password(加密后的),$sch_id的数组]
	 * @return [type]           [description]
	 * @author NewFuture
	 */
	public static function token($user)
	{

		if ($user &&
			(is_numeric($user) && $data = UserModel::field('id,number,password,sch_id')->find($user))
			|| (isset($user['id']) && $data['id'] = $user['id']
				&& isset($user['number']) && $data['number'] = $user['number']
				&& isset($user['password']) && $data['password'] = $user['password']
				&& isset($user['sch_id']) && $data['sch_id'] = $user['sch_id']))
		{
			return self::createBaseToken($data);
		}
	}

	/**
	 * 验证token
	 * @method checkToken
	 * @param  [type]     $uid   [description]
	 * @param  [type]     $token [description]
	 * @return [type]            [description]
	 * @author NewFuture
	 */
	public static function checkToken($uid, $token)
	{
		if ($user = UserModel::field('id,number,password,sch_id')->find(intval($uid)))
		{
			$base_token = self::createBaseToken($user);
			return $token == $base_token ? $user : null;
		}
	}

	/**
	 * 获取当前登录的打印店
	 * @method getPrinter
	 * @return [type]     [description]
	 * @author NewFuture
	 */
	public static function getPrinter()
	{
		if ($printer = Session::get('printer'))
		{
			/*session中的信息*/
			return $printer;
		}
		elseif ($tokenInfo = Cookie::get('token'))
		{
			/*解析cookie*/
			$id = key($tokenInfo);
			if ($printer = self::checkPritnerToken($id, $tokenInfo[$id]))
			{
				/*token有效*/
				Session::set('printer', $printer);
				return $printer;
			}
		}
		elseif (Input::I('SERVER.HTTP_TOKEN', $token, 'token'))
		{
			/*http头中的请求*/

		}
	}

	/**
	 * 获取打印店ID
	 * @method priId
	 * @return [type] [description]
	 * @author NewFuture
	 */
	public static function priId()
	{
		if ($printer = self::getPrinter())
		{
			return $printer['id'];
		}
	}

	/**
	 * printer 生成token
	 * @method printerToken
	 * @param  [type]       $printer [description]
	 * @return [type]                [description]
	 * @author NewFuture
	 */
	public static function printerToken($printer)
	{
		if ($printer &&
			(is_numeric($printer) && $data = PrinterModel::field('id,name,password,sch_id')->find($printer))
			|| (isset($printer['id']) && $data['id'] = $printer['id']
				&& isset($printer['name']) && $data['name'] = $printer['name']
				&& isset($printer['password']) && $data['password'] = $printer['password']
				&& isset($printer['sch_id']) && $data['sch_id'] = $printer['sch_id']))
		{
			return self::createBaseToken($data);
		}
	}

	/**
	 * checkPrinterToken description
	 * @method checkPrinterToken
	 * @param  [type]            $id    [description]
	 * @param  [type]            $token [description]
	 * @return [type]                   [description]
	 * @author NewFuture
	 */
	public static function checkPrinterToken($id, $token)
	{
		if ($p = UserModel::field('id,name,password,sch_id')->find(intval($uid)))
		{
			$base_token = self::createBaseToken($p);
			return $token == $base_token ? $p : null;
		}
	}

	/*根据用户信息生成基础token*/
	private static function createBaseToken(&$user)
	{
		$token = hash_hmac('md5', implode('|', $user), $user['password'], true);
		return Encrypt::base64Encode($token);
	}

	///**
	//  * @method code
	//  * @param  [type] $uid [description]
	//  * @return [type]      [description]
	//  * @author NewFuture
	//  */
	// public static function createCode($session)
	// {
	// 	$code = $session['id'] . 'C' . Random::word(10);
	// 	Cache::set('auth_' . $code, $session, 300); //5分钟有效
	// 	return $code;
	// }

	// /**
	//  * 验证code码
	//  * @method checkCode
	//  * @param  [type]    $code [description]
	//  * @return [type]          [description]
	//  * @author NewFuture
	//  */
	// public static function checkCode($code)
	// {
	// 	$key = 'auth_' . $code;
	// 	if ($session = Cache::get($key))
	// 	{
	// 		Cache::del($key);
	// 		return $session;
	// 	}
	// }
}