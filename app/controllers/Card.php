<?php
class CardController extends rest
{

	protected function init()
	{
		if (!Input::post('key', $apikey, 'string'))
		{
			$this->response(-3, 'API key无效');
			exit();
		}
		parent::init();
	}

	/**
	 * 校园卡信息验证接口
	 */
	public function indexAction()
	{
		$uid = $this->auth();

		if (!Input::post('name', $name, 'name'))
		{
			$this->response(0, '姓名无效');
		}
		elseif (!Input::post('number', $number, 'card'))
		{
			$this->response(0, '学号无效');
		}
		else
		{
			$User = UserModel::where('number', $number)->field('id,name,sch_id,number,phone,email');
			if (Input::post('school', $sch_id, 'int'))
			{
				//限制学校则直接查询学校
				$User->where('sch_id', $sch_id);
			}

			if (!$User->find())
			{
				$this->response(0, '未找到此人信息');
			}
			elseif (!$User['name'] != $name)
			{
				$this->response(parent::AUTH_BAN, '姓名不匹配');
			}
			else
			{
				$user = UserModel::decrypt($User);
				$this->response(1, $user);
			}

		}
	}

	/**
	 * 绑定用户手机
	 * @method post_phoneAction
	 * @return [type]           [description]
	 * @author NewFuture
	 */
	public function post_phoneAction()
	{
		$uid = $this->auth();
		if (!Input::post('phone', $phone, 'phone'))
		{
			$this->response(0, '无效手机号');
		}
		elseif (UserModel::getByPhone($phone))
		{
			$this->response(0, '此手机已经绑定过用户');
		}
		elseif (UserModel::where('id', $uid)->savePhone($phone))
		{
			$this->response(1, '修改成功');
		}
		else
		{
			$this->response(0, '修改出错');
		}

	}

}