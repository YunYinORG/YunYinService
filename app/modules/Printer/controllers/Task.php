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
		$Task = TaskModel::where('pri_id', '=', $pid)->belongs('user')->page($page);
		if (Input::get('status', $status, 'int'))
		{
			$Task->where('status', '=', $status);
		}
		else
		{
			//所有未完成订单
			$Task->where('status', '>', 0);
		}
		$tasks = $Task->select('name,id,color,format,double,copies,status,name,time,requirements');
		$this->response(1, $tasks);
	}

	/**
	 * 获取详情
	 * @param [type] $id [description]
	 */
	public function GET_infoAction($id)
	{
		$pid = $this->authPrinter();
		if (!$task = TaskModel::where('pri_id', '=', $pid)->find($id))
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
		if (!Input::get('status', $status, 'int'))
		{
			$response['info'] = '无效状态';
		}
		elseif ($status < 0 || $status > 5)
		{
			$response['info'] = '此状态不允许设置';
		}
		elseif (TaskModel::where('id', $id)->where('pri_id', $pid)->update(['status' => $status]))
		{
			$response['status'] = 0;
			$response['info']   = '修改成功';
		}
		else
		{
			$response['info'] = '状态设置失败';
		}
		$this->response = $response;
	}

	/**
	 * 获取源文件
	 * @param [type] $id [description]
	 */
	public function GET_fileAction($id)
	{
		$pid = $this->authPrinter();

		$response['status'] = 0;

		if (!$id)
		{
			$response['info'] = 'id错误';
		}
		elseif (!$task = TaskModel::where('pri_id', '=', $pid)->find($id))
		{
			$response['info'] = '无此文件';
		}
		else
		{
			$task['url']      = File::get($task['url']);
			$response['info'] = $task;
		}
		$this->response = $response;
	}
}
?>