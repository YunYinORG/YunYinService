<?php
/**
 * 打印店任务管理
 */
class TaskController extends PrinterRest
{
	/**
	 * 打印店登录
	 * @method loginAction
	 * @return [type]      [description]
	 * @author NewFuture
	 */
	public function GET_newAction()
	{
		$pid = $this->auth();
		Input::get('page', $page, 'int', 1);
		$tasks = TaskModel::where('pri_id', '=', $pid)->where('status', '>', 0)->belongs('user')->page($page)->select('name,id,color,ppt,double,copies,status,name,time,requirements');
		$this->response(1, $tasks);
	}

	public function GET_infoAction($id)
	{
		$pid = $this->auth();

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
	}

	public function PUT_infoAction($id)
	{
		$pid    = $this->auth();
		$status = I('status');
		$this->where();
	}
}
?>