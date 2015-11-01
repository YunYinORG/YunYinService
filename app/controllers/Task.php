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
		Input::get('page', $page, 'int', 1);
		$tasks = TaskModel::where('use_id', '=', $userid)
			->where('status', '>', 0)
			->belongs('printer')
			->page($page)
			->order('id', 'DESC')
			->select('id,color,copies,double,format,name,payed,pri_id,status,time');
		$this->response(1, $tasks);
	}

	/**
	 * 打印任务
	 * POST /task/
	 * @method POST_index
	 * @param fid 文件id
	 * @param pid 打印店id
	 * @param
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
		elseif (!$file = FileModel::where('use_id', $userid)->where('status', '>', 0)->field('url,name,status')->find($fid))
		{
			$response['info'] = '没有该文件或者此文件已经删除';
		}
		else
		{
			$task           = TaskModel::create('post');
			$task['name']   = $file['name'];
			$task['use_id'] = $userid;
			$task['pri_id'] = $pid;

			if (!$task['url'] = File::addTask($file['url']))
			{
				$response['info'] = '文件转换出错';
			}
			elseif (!$tid = TaskModel::insert($task))
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
		if ($task = TaskModel::where('use_id', '=', $userid)->belongs('printer')->find(intval($id)))
		{
			$task['url'] = File::get($task['url']);
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
		if ($Task = TaskModel::where('use_id', $userid)->where('status', 1)->find(intval($id)))
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
		$Task               = TaskModel::where('use_id', $userid)->where('status', '>', 0)->field('status,payed,url')->find(intval($id));
		if (!$Task)
		{
			$response['info'] = '该任务不存在';
		}
		elseif ($Task['status'] > 1 && $Task['status'] < 3)
		{
			$response['info'] = '打印店已经在处理，不可删除，请联系打印店取消订单任务';
		}
		elseif ($Task['status'] == 4 && !$Task['payed'])
		{
			$response['info'] = '打印店尚未确认付款';
		}
		elseif ($Task->set('status', 0)->save($id))
		{
			$response['status'] = 1;
			$response['info']   = '删除成功';
		}
		else
		{
			$response['info'] = '删除失败';
		}
		$this->response = $response;
	}

	/**
	 *  获取下载url   
	 * @method GET_urlAction
	 * @param     $id [description]
	 * @author NewFuture
	 */
	public function GET_urlAction($id = 0)
	{
		$userid = $this->auth();
		$url    = TaskModel::where('id', '=', $id)->where('use_id', '=', $userid)->get($url);
		Input::get('alias', $alias, 'title');
		if ($url && $url = File::get($url, $alias))
		{
			$this->response(1, $url);
		}
		else
		{
			$this->response(0, '你没有设定此任务');
		}
	}
}