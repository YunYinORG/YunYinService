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
		Input::put('name', $var, 'title') AND $info['name'] = $var;
		/*邮箱*/
		Input::put('email', $var, 'email') AND $info['email'] = $var;
		/*手机*/
		Input::put('phone', $var, 'phone') AND $info['phone'] = $var;
		/*qq号*/
		Input::put('qq', $var, 'int') AND $info['qq'] = $var;
		/*微信号*/
		Input::put('wechat', $var, 'char_num') AND $info['wechat'] = $var;
		/*地址*/
		Input::put('address', $var, 'text') AND $info['address'] = $var;
		/*简介*/
		Input::put('profile', $var, 'text') AND $info['profile'] = $var;
		/*营业时间*/
		Input::put('open', $var, 'text') AND $info['open'] = $var;
		/*营业时间*/
		Input::put('other', $var, 'text') AND $info['other'] = $var;
		/*价格*/
		$price = [];
		Input::put('price_s', $price['s'], 'float');
		Input::put('price_d', $price['d'], 'float');
		Input::put('price_c_s', $price['c_s'], 'float');
		Input::put('price_c_d', $price['c_d'], 'float');
		if (!empty(array_filter($price)))
		{
			if ($oldprice = PrinterModel::where('id', $id)->get('price'))
			{
				if ($oldprice = @json_decode($oldprice, true))
				{
					$price = array_merge($oldprice, array_filter($price));
				}
			}
			$info['price'] = json_encode($price);
		}
		if (empty($info))
		{
			$this->response(0, '无有效参数');
		}
		elseif (PrinterModel::where('id', $id)->update($info) !== false)
		{
			$this->response(1, $info);
		}
		else
		{
			$this->response(0, '修改失败');
		}
	}
}