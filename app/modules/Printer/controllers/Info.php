<?php
/**
 * 打印店信息管理
 */
class InfoController extends Rest
{

	/**
	 * 获取打印店详情
	 * @param [type] $id [description]
	 */
	public function GET_infoAction($id)
	{
		$this->authPrinter($id);
		$field = 'id,name,sch_id,account,address,email,phone,qq,wechat,profile,image,open,status,price,other';
		if ($printer = PrinterModel::field($field)->find($id))
		{
			$printer['price'] = json_decode($printer['price']);
			$this->response(1, $printer);
		}
		else
		{
			$this->response(0, '无效用户');
		}
	}

	/**
	 * 修改信息
	 * @param [type] $id [description]
	 * @todo 价格
	 */
	public function PUT_infoAction($id)
	{
		$this->authPrinter($id);
		$info = [];
		/*店名*/
		Input::put('name', $name, 'title') AND $info['name'] = $name;
		/*邮箱*/
		Input::put('email', $email, 'email') AND $info['email'] = $email;
		/*手机*/
		Input::put('phone', $phone, 'phone') AND $info['phone'] = $phone;
		/*qq号*/
		Input::put('qq', $qq, 'int') AND $info['qq'] = $qq;
		/*微信号*/
		Input::put('wechat', $wechat, 'char_num') AND $info['wechat'] = $wechat;
		/*简介*/
		Input::put('profile', $profile, 'text') AND $info['profile'] = $profile;
		/*营业时间*/
		Input::put('open', $open, 'text') AND $info['open'] = $open;
		/*营业时间*/
		Input::put('other', $other, 'text') AND $info['other'] = $other;

		/*价格*/
		$price = [];
		if (Input::put('price.s', $price['s'], 'float')
			|| Input::put('price.d', $price['d'], 'float')
			|| Input::put('price.c_s', $price['c_s'], 'float')
			|| Input::put('price.c_d', $price['c_d'], 'float'))
		{
			if ($oldprice = PrinterModel::where('id', $id)->get('price'))
			{
				$price = array_merge($oldprice, array_filter($price));
			}
			$info['price'] = $price;
		}

		if (empty($info))
		{
			$this->response(0, '无有效参数');
		}
		elseif (PrinterModel::where('id', $id)->update($info))
		{
			$this->response(1, $info);
		}
		else
		{
			$this->response(0, '修改失败');
		}
	}
}