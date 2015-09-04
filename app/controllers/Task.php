<?php
/**
 * 任务管理
 */

class TaskController extends Rest
{
	/**
	 * 文件列表
	 * GET /task/
	 * @method GET_index
	 * @author NewFuture
	 */
	public function GET_indexAction()
	{
		$userid = $this->auth();
		$tasks  = TaskModel::where('use_id', '=', $userid)->belongs('flie')->belongs('printer')->select();
		$this->response(1, $tasks);
	}

	/**
	 * 上传文件
	 * POST /task/
	 * @method POST_index
	 * @param fid 文件id
	 * @param pid 打印店id
	 * @param
	 * @todo  文件状态验证，共享文件验证
	 */
	public function POST_indexAction()
	{
		$userid             = $this->auth();
		$response['status'] = 0;

		if (!Input::post('fid', $fid, 'int'))
		{
			$response['info'] = '未选择文件';
		}
		elseif (!Input::post('pid', $pid, 'int'))
		{
			$response['info'] = '未选择打印店';
		}
		elseif (!$file = FileModel::where('use_id', $userid)->where('status', '>', 0)->filed('url,status')->find($fid))
		{
			$response['info'] = '没有该文件或者此文件已经删除';
		}
		else
		{
			$task           = TaskModel::create('post');
			$task['url']    = $file['url'];
			$task['use_id'] = $userid;
			$task['pri_id'] = $pid;

			if (!$tid = TaskModel::insert($task))
			{
				$response['info'] = '任务添加失败';
			}
			else
			{

				$response['status'] = 1;
				$response['info']   = ['msg' => '打印任务添加成功', 'id' => $tid];
			}
		}
		$this->response = $response;
	}

	/**
	 * 任务详情
	 * GET /task/1
	 * @method GET_info
	 * @author NewFuture
	 * @todo 更详细的信息
	 */
	public function GET_infoAction($id)
	{
		$userid = $this->auth();
		if ($task = TaskModel::where('use_id', '=', $userid)->find($id))
		{
			$this->response(1, $task);
		}
		else
		{
			$this->response(0, '你没有设定此任务');
		}
	}

	/**
	 * 任务状态修改
	 * PUT /task/1
	 * @method PUT_info
	 * @author NewFuture
	 */
	public function PUT_infoAction($id = 0)
	{
		$userid = $this->auth();
		if ($Task = TaskModel::where('use_id', $userid)->where('status', 1)->find($id))
		{
			$task = TaskModel::create('put');
			if ($Task->update($task))
			{
				$this->response(1, '成功修改');
			}
			else
			{
				$this->response(0, '修改失败');
			}
		}
		else
		{
			$this->response(0, '该任务不存在');
		}
	}

	/**
	 * 删除
	 * @method DELETE_indexAction
	 * @param  [type]             $id [description]
	 * @author NewFuture
	 */
	public function DELETE_infoAction($id = 0)
	{
		$userid             = $this->auth();
		$response['status'] = 0;
		if (!($Task = TaskModel::where('use_id', $userid)->where('status', '>', 0)->find($id)))
		{
			$response['info'] = '该任务不存在';
		}
		elseif ($Task->status > 1 && $Task->status < 5)
		{
			$response['info'] = '改任务目前状态不允许删除';
		}
		elseif ($Task->set('status', 0)->save($id))
		{
			$response['status'] = 1;
			$response['info']   = '删除成功';
		}
		else
		{
			$response['info'] = '删除成功';
		}
		$this->response = $response;
	}
}