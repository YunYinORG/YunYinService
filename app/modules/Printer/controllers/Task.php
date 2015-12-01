<?php
/**
 * 打印店任务管理
 */
class TaskController extends Rest
{
	/**
	 * 获取任务列表
	 * @method indexAction
	 * @return [type]      [description]
	 * @author NewFuture
	 */
	public function GET_indexAction()
	{
		$pid = $this->authPrinter();
		Input::get('page', $page, 'int', 1);
		$Task = TaskModel::where('pri_id', '=', $pid)->belongs('user')->page($page)->order('id', true);
		if (Input::get('status', $status, 'int'))
		{
			$Task->where('status', '=', $status);
		}
		else
		{
			//所有未完成订单
			$Task->where('status', '>', 0);
		}
		$tasks = $Task->select('name,id,color,format,double,copies,status,name,time,requirements,payed');
		$this->response(1, $tasks);
	}

	/**
	 * 获取详情
	 * @param [type] $id [description]
	 */
	public function GET_infoAction($id)
	{
		$pid = $this->authPrinter();
		if (!$task = TaskModel::where('pri_id', '=', $pid)->find(intval($id)))
		{
			$this->response(0, '无此文件');
		}
		else
		{
			$task['url'] = File::get($task['url']);
			$this->response(1, $task);
		}

	}

	/**
	 * 修改状态
	 * @param [type] $id [description]
	 */
	public function PUT_infoAction($id)
	{
		$pid                = $this->authPrinter();
		$response['status'] = 0;
		if (!Input::put('status', $status, 'int'))
		{
			$response['info'] = '无效状态';
		}
		elseif ($status < -1 || $status > 5)
		{
			$response['info'] = '此状态不允许设置';
		}
		elseif($status==-1&&TaskModel::where('id',intval($id))->where('pri_id',$pri_id)->get('payed'))
		{//取消订单
			
			$response['info']='已支付暂不支持线上取消';
		}
		elseif (TaskModel::where('id', intval($id))->where('pri_id', $pid)->update(['status' => $status]))
		{
			$response['status'] = 1;
			$response['info']   = '修改成功';
		}
		else
		{
			$response['info'] = '状态设置失败';
		}
		$this->response = $response;
	}

	/**
	 * 确认支付
	 * @method POST_payAction
	 * @param  [type]         $id [description]
	 * @author NewFuture
	 */
	public function POST_payAction($id)
	{
		$pid = $this->authPrinter();
		if (TaskModel::where('id', intval($id))->where('pri_id', $pid)->update(['payed' => 1]))
		{
			$this->response(1, '已确认支付！');
		}
		else
		{
			$this->response(0, '状态修改失败');
		}
	}

	/**
	 * 获取源文件
	 * 在转码出问题可用此接口
	 * @param [type] $id [description]
	 */
	public function GET_fileAction($id)
	{
		$pid = $this->authPrinter();
		if ($url = TaskModel::where('id', intval($id))->where('pri_id', '=', $pid)->get('url'))
		{
			$this->response(1, File::source($url));
		}
		else
		{
			$this->response(0, '无此文件');
		}
	}
}
?>