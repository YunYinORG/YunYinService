<?php
class CardController extends Rest
{

	protected function init()
	{
		if (Input::post('key', $apikey, 'string') && ($apikey == Config::getSecret('api', 'card')))
		{
			parent::init();
		}
		else
		{
			$this->response(-3, 'API key无效');
			exit();
		}
	}

	/**
	 * 校园卡信息验证接口
	 */
	public function indexAction()
	{
		if (!Input::post('name', $name, 'name'))
		{
			//姓名输入过滤
			$this->response(0, '姓名无效');
		}
		elseif (!Input::post('number', $number, 'card'))
		{
			//学号输入过滤
			$this->response(0, '学号无效');
		}
		else
		{
			$User = UserModel::where('number', $number)->field('id,name,sch_id,number,phone,email');
			if (Input::post('school', $sch_id, 'int') && $User->where('sch_id', $sch_id)->find())
			{
				if ($User['name'] != $name)
				{
					$this->response(parent::AUTH_BAN, '姓名不匹配');
				}
				else
				{
					$user = UserModel::decrypt($User);
					$this->response(1, $user);
				}
			}
			elseif ($User->where('name', $name)->find())
			{
				$user = UserModel::decrypt($User);
				$this->response(1, $user);
			}
			else
			{
				$this->response(0, '未找到此人信息');
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