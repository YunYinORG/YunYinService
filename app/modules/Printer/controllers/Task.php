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
		$tasks = TaskModel::where('pri_id', '=', $pid)->where('status', '>', 0)->belongs('user')->page($page)->select('name,id,color,format,double,copies,status,name,time,requirements');
		$this->response(1, $tasks);
	}

	/**
	 * 获取详情
	 * @param [type] $id [description]
	 */
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
		$this->response = $response;
	}

	/**
	 * 修改状态
	 * @param [type] $id [description]
	 */
	public function PUT_infoAction($id)
	{
		$pid                = $this->auth();
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

}
?>