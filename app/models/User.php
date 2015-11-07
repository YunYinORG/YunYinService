<?php

class UserModel extends FacadeModel
{
	protected $pk    = 'id';
	protected $table = 'user';

	/**
	 * 创建用户
	 * @method create
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 * @author NewFuture
	 */
	public function create($data)
	{
		$userInfo = array();
		/*姓名*/
		if (!(isset($data['name']) && $data['name']))
		{
			$this->error = '姓名有误！';
			return false;
		}
		else
		{
			$userInfo['name'] = $data['name'];
		}
		/*学号*/
		if (!(isset($data['number']) && is_numeric($data['number'])))
		{
			$this->error = '学号必须';
			return false;
		}
		else
		{
			$userInfo['number'] = $data['number'];
		}
		/*密码*/
		if (!isset($data['password']))
		{
			$this->error = '密码必须';
			return false;
		}
		else
		{
			$userInfo['password'] = Encrypt::encryptPwd($data['password'], $data['number']);
		}
		/*学校*/
		if (isset($data['sch_id']))
		{
			$userInfo['sch_id'] = intval($data['sch_id']);
		}

		/*存入数据库*/
		if ($uid = parent::getModel()->insert($userInfo))
		{
			return $uid;
		}
		else
		{
			$this->error = '保存失败';
			return false;
		}
	}

	/*关联用户学校*/
	public function school()
	{
		/*用户属于一所学校*/
		parent::getModel()->belongs('school');
		return $this;
	}

	/**
	 * 用户数据打码
	 * @method mask
	 * @param  [type] &$user [description]
	 * @return [type]        [description]
	 * @author NewFuture
	 */
	public static function mask(&$user)
	{
		if (isset($user['phone']) && $phone = $user['phone'])
		{
			$user['phone'] = substr_replace($phone, '********', -8);
		}
		if (isset($user['email']) && $email = $user['email'])
		{
			$user['email'] = $email[0] . '***' . strrchr($email, '@');
		}
		return $user;
	}

	/**
	 * 用户数据解码
	 * @method mask
	 * @param  [type] &$user [description]
	 * @return [type]        [description]
	 * @author NewFuture
	 */
	public static function decrypt(&$user)
	{
		if (isset($user['phone']) && $user['phone'] && $user['number'] && $user['id'])
		{
			$user['phone'] = Encrypt::decryptPhone($user['phone'], $user['number'], $user['id']);
		}
		
		if (isset($user['email']) && $email = $user['email'])
		{
			$user['email'] = Encrypt::decryptEmail($email);
		}
		return $user;
	}

	/**
	 * 保存手机
	 * @method savePhone
	 * @param  [type]    $id     [description]
	 * @param  [type]    $number [description]
	 * @param  [type]    $phone  [description]
	 * @return [type]            [description]
	 * @author NewFuture
	 */
	public static function savePhone($phone)
	{
		if (!$user = Auth::getUser())
		{
			//无法获取用户
			return false;
		}
		elseif (self::getByPhone($phone))
		{
			//手机绑定过
			return false;
		}
		else
		{
			//加密
			$phone = Encrypt::encryptPhone($phone, $user['number'], $user['id']);
			return self::set('phone', $phone)->save($user['id']);
		}
	}

	/**
	 * 根据手机获取用户
	 * @method getByPhone
	 * @param  [type]     $phone [description]
	 * @return [type]            [description]
	 * @author NewFuture
	 */
	public static function getByPhone($phone)
	{
		$tail = Encrypt::encryptPhoneTail(substr($phone, -4));
		if ($users = self::where('phone', 'LIKE', '%%' . $tail)->select('id,number,phone'))
		{
			foreach ($users as $user)
			{
				if ($phone == Encrypt::decryptPhone($user['phone'], $user['number'], $user['id']))
				{
					return $user;
				}
			}
		}
	}

	public static function saveEmail($email, $id)
	{

		if (!($id || $id = Auth::id()))
		{
			//无法获取用户
			return false;
		}
		elseif (self::getByEmail($email))
		{
			//手机绑定过
			return false;
		}
		else
		{
			//加密
			$email = Encrypt::encryptEmail($email);
			return self::set('email', $email)->save($id);
		}
	}

	/**
	 * 邮箱获取用户
	 * @method getByEmail
	 * @param  [type]     $email [description]
	 * @return [type]            [description]
	 * @author NewFuture
	 */
	public static function getByEmail($email)
	{
		if (strrpos($email, '@') == 1)
		{
			/*单字母邮箱*/
			return self::where('email', 'LIKE', substr_replace($email, '%', 0, 1))
				->where('length(`email`)<' . (strlen($email) + 23))
				->find();
		}
		else
		{
			/*正常邮箱*/
			$email = Encrypt::encryptEmail($email);
			return self::where('email', $email)->find();
		}
	}
}