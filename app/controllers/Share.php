<?php
/**
 * 文件共享
 *
 */
// use Service\File;

class ShareController extends Rest
{
	const SHARED_FLAG=2
	/**
	 * 文件列表
	 * GET /share/
	 * @method GET_index
	 * @author NewFuture
	 */
	public function GET_indexAction()
	{
		$userid = $this->auth();
		Input::get('page', $page, 'int', 1);
		$shares = ShareModel::where('use_id', '=', $userid)->page($page)->select('id,name,time');
		$this->response(1, $shares);
	}

	/**
	 * 分享文件
	 * POST /share/
	 * @method POST_index
	 * @param key 获取token时返回的key
	 */
	public function POST_indexAction()
	{
		$userid             = $this->auth();
		$response['status'] = 0;
		if (!Input::post('fid', $fid, 'int'))
		{
			$this->response['info'] = '未选择文件';
		}
		elseif (!$File = FileModel::field('name,url')
				->where('use_id', '=', $userid)
				->where('status', '>', 0)
				->find($fid))
		{
			/*数据库中查询的文件*/
			$this->response['info'] = '文件无效';
		}
		elseif ($File['status']&self::SHARED_FLAG)//位标记
		{
			/*是否已经共享*/
			$this->response['info'] = '文件已分享';
		}
		else
		{
			/*验证完成，开始插入*/

			$url             = $File->url;
			$url             = substr_replace($url, 'share', 0, 4);
			$share['url']    = File::share($url);
			$share['fil_id'] = $fid;
			$share['use_id'] = $userid;
			$share['name']   = Input::post('name', $name, 'title') ? $name : $File->name;

			if (Input::post('detail', $detail, 'text'))
			{
				$share['detail'] = $detail;
			}
			if (Input::post('anonymous', $anonymous))
			{
				$share['anonymous'] = boolval($anonymous);
			}

			if ($sid = ShareModel::Insert($share))
			{
				//插入成功
				//文件状态,更新为已分享
				$File->save(['status' => $File['status']|self::SHARED_FLAG]);
				//TODO
				//分享文件预处理
				$response['status'] = 1;
				$response['info']   = ['msg' => '分享成功', 'id' => $fid];
			}
			else
			{
				$response['info'] = '分享失败';
			}
		}
		$this->response = $response;
	}

	/**
	 * 分享文件详细信息
	 * GET /share/1
	 * @method GET_info
	 * @author NewFuture
	 * @todo 预览等，权限
	 */
	public function GET_infoAction($id = 0)
	{
		$uid = $this->auth();
		if ($share = ShareModel::find($id))
		{
			$share['user'] = $share['anonymous'] ? '不愿透露姓名的同学' : UserModel::where('id', $share['use_id'])->get('name');
			if ($uid != $share['use_id'])
			{
				unset($share['use_id']);
				unset($share['fil_id']);
			}
			$this->response(1, $share);
		}
		else
		{
			$this->response(0, '该分享不存在');
		}
	}

	/**
	 * 信息修改
	 * PUT /share/1
	 * @method PUT_info
	 * @author NewFuture
	 * @param name 文件名
	 */
	public function PUT_infoAction($id = 0)
	{
		$userid = $this->auth();
		/*检查输入*/
		if (Input::put('name', $name, 'title'))
		{
			$share['name'] = $name;
		}
		if (Input::put('anonymous', $anonymous))
		{
			$share['anonymous'] = boolval($anonymous);
		}
		if (Input::put('detail', $detail, 'text'))
		{
			$share['detail'] = $detail;
		}
		/*保存*/
		if (empty($share))
		{
			$this->response(0, '没有任何有效的修改内容');
		}
		elseif (ShareModel::where('id', $id)->where('use_id', $use_id)->update($share))
		{
			$this->response(1, $share);
		}
		else
		{
			$this->response(0, '保存失败');
		}
	}

	/**
	 * 删除
	 * DELETE /share/1
	 * @method DELETE_info
	 * @author NewFuture
	 */
	public function DELETE_infoAction($id = 0)
	{
		$userid = $this->auth();
		if ($share = ShareModel::where('use_id', $userid)->field('url')->find(intval($id)))
		{
			$this->response(0, '您无此分享文件');
		}
		elseif (!$url = ($share['url']))
		{
			$this->response(0, '此共享已经删除');
		}
		elseif ($share->set('url', '')->save())
		{
			File::del($url);
			$this->response(1, '删除成功');
		}
		else
		{
			$this->response(0, '删除失败');
		}
	}

	/**
	 * 删除
	 * POST /share/123/print
	 * @method 添加打印任务
	 * @author NewFuture
	 */
	public function POST_printAction($id = 0)
	{
		$userid             = $this->auth();
		$response['status'] = 0;
		if (!$share = ShareModel::where('status', '>', 0)->field('name,url')->find())
		{
			$response['info'] = '此分享已经删除！';
		}
		elseif (!Input::post('pid', $pid, 'int'))
		{
			$response['info'] = '请选择打印店！';
		}
		else
		{
			$task           = TaskModel::create('post');
			$task['use_id'] = $userid;
			$task['pid']    = $pid;
			$task['url']    = File::addTask($share['url']);
			$task['name']   = $share['name'];

			if (!$tid = TaskModel::insert($task))
			{
				$response['info'] = '任务添加失败';
			}
			else
			{
				$response['status'] = 1;
				$response['info']   = ['msg' => '任务添加成功', 'id' => $tid];
			}
		}
		$this->response = $response;
	}

	/**
	 * 搜索
	 * @method GET_searchAction
	 * @todo 标签
	 * @author NewFuture
	 */
	public function GET_searchAction()
	{
		Input::get('page', $page, 'int', 1);
		$Share = ShareModel::page($page)->field('id,name,time');
		if (Input::get('key', $key))
		{
			$key   = '%' . strtr($key, ' ', '%') . '%';
			$Share = where('name', 'LIKE', $key)->orWhere('detail', 'LIKE', $key);
		}

		if (Input::get('key', $tag, 'int'))
		{
			//标签筛选
		}
		$shares = $Share->select();
		$this->response(1, $shares);
	}

	// /**
	// * 赞
	// * POST /share/123/up
	// */
	// public function POST_upAction($id)
	// {
	// }
}
