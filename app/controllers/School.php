<?php

class SchoolController extends Rest
{

	/**
	 * 学校列表
	 * @method GET_indexAction
	 * @author NewFuture
	 */
	public function GET_indexAction()
	{
		$fileds = 'id,name,abbr,verify,verifyurl';
		if (Input::get('key', $key))
		{
			$key     = '%' . strtr($key, ' ', '%') . '%';
			$schools = SchoolModel::where('name', 'LIKE', $key)->select($fileds);
			$this->response(1, $schools);
		}
		elseif ($schools = SchoolModel::all($fileds))
		{
			$this->response(1, $schools);
		}
		else
		{
			$this->response(0, '无法查看学校信息');
		}

	}

	/**
	 * 学校信息
	 * @method GET_infoAction
	 * @param  integer          $id [description]
	 * @author NewFuture
	 */
	public function GET_infoAction($id = 0)
	{
		if ($school = SchoolModel::find($id))
		{
			$school['number'] = Config::get('regex.number.' . strtolower($school['abbr']));
			$this->response(1, $school);
		}
		else
		{
			$this->response(0, $id);
		}
	}

	/**
	 * 获取学校验证码
	 * @method GET_codeAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function GET_codeAction($id = 0)
	{
		if ($code = School::code($id))
		{
			$code['img'] = 'data:image/png;base64,' . base64_encode($code['img']);
			$this->response(1, $code);
		}
		elseif ($code === false)
		{
			$this->response(0, '无需验证码');
		}
		else
		{
			Log::write('验证码获取失败' . $id, 'ERROR');
			$this->response(-3, '验证码获取失败');
		}
	}

	/**
	 * 分析学号所在学校
	 * @method POST_numberAction
	 * @author NewFuture
	 */
	public function POST_numberAction()
	{
		if (Input::post('number', $number, 'card'))
		{
			/*排除学校*/
			if (Input::post('black', $black))
			{
				$this->parse($black);
			}
			/*限定学校*/
			if (Input::post('white', $white))
			{
				$this->parse($white);
			}

			if ($schools = School::guess($number, $black, $white))
			{
				if ($reg = UserModel::where('number', $number)->select('sch_id'))
				{
					foreach ($reg as $user)
					{
						$schools[$user['sch_id']] = 0;
					}
				}
				$this->response(1, $schools);
			}
			else
			{
				$this->response(0, '无相关学校,请检查学号是否正确');
			}
		}
		else
		{
			$this->response(0, '学号格式有误');
		}
	}

	private function parse(&$s)
	{
		/*如果是字符切成数组*/
		if (is_string($s))
		{
			$s = explode(',', $s);
		}
		/*id转成缩写*/
		if (is_numeric(end($s)))
		{
			array_walk($s, function (&$v)
			{
				$v = School::getAbbr($v);
			});
		}
		return $s;
	}
}