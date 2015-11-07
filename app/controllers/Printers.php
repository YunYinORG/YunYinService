<?php

class PrintersController extends Rest
{

	/**
	 * 获取打印店列表
	 * @method GET_indexAction
	 * @author NewFuture
	 */
	public function GET_indexAction()
	{
		Input::post('page', $page, 'int', 1);
		$Printer = (new Model('printer'))->where('status', '>', 0)->order('rank', 'DESC')->page($page);
		if (Input::get('sch_id', $sch_id, 'is_numeric'))
		{
			$Printer->where('sch_id', $sch_id);
		}
		Input::get('page', $page, 'int', 1);
		$printers = $Printer->select('id,name,sch_id,address');
		$this->response(1, $printers);
	}

	/**
	 * 获取打印店详情
	 * @method GET_infoAction
	 * @param  integer          $id [description]
	 * @author NewFuture
	 */
	public function GET_infoAction($id = 0)
	{
		$this->auth();
		if ($printer = PrinterModel::field('name,sch_id,address,phone,email,qq,profile,image,open,price,other')->find($id))
		{
			if (isset($printer['price']))
			{
				$printer['price'] = json_decode($printer['price']);
			}
			$this->response(1, $printer);
		}
		else
		{
			$this->response(0, '不存在');
		}
	}

	/**
	 * 获取价格
	 * GET /printers/123/price
	 * @method GET_priceAction
	 * @param  integer         $id [description]
	 * @author NewFuture
	 */
	public function GET_priceAction($id = 0)
	{
		$this->auth();
		if ($printer = PrinterModel::field('price,other')->find($id))
		{
			$price        = json_decode($printer['price']);
			$price->other = $printer['other'];
			$this->response(1, $price);
		}
		else
		{
			$this->response(0, '不存在此店');
		}
	}
}