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
		$Printer = new Model('printer');
		if (Input::get('sch_id', $sch_id, 'is_numeric'))
		{
			$Printer->where('sch_id', $sch_id);
		}
		$printers = $Printer->order('rank', 'DESC')->select('id,name,sch_id,address');
		$this->response(1, $printers);
	}

	/**
	 * 获取打印店详情
	 * @method GET_detailAction
	 * @param  integer          $id [description]
	 * @author NewFuture
	 */
	public function GET_detailAction($id = 0)
	{
		$this->auth();
		if ($printer = PrinterModel::field('name,sch_id,address,phone,email,qq,profile,image,open,price,other')->find($id))
		{
			$this->response(1, $printer);
		}
		else
		{
			$this->response(0, '不存在');
		}
	}
}