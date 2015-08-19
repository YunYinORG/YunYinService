<?php

class UserModel extends FacadeModel
{
	protected $pk    = 'id';
	protected $table = 'user';
	private $error   = '';

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

	public function getError()
	{
		return $this->error;
	}

}