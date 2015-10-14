<?php
/**
 * 文件管理
 */

class FileController extends Rest
{
	/**
	 * 文件列表
	 * GET /file/
	 * @method GET_index
	 * @author NewFuture
	 */
	public function GET_indexAction()
	{
		$userid = $this->auth();
		Input::get('page', $page, 'int', 1);
		$files = FileModel::where('use_id', '=', $userid)
			->where('status', '>', 0)
			->page($page)
			->select('id,name,time');
		$this->response(1, $files);
	}

	/**
	 * 上传文件
	 * POST /file/
	 * @method POST_index
	 * @param key 获取token时返回的key
	 */
	public function POST_indexAction()
	{

		if (!Input::post('key', $key, 'filename'))
		{
			$this->response(0, '未收到数据');
			return;
		}
		list(, $userid)     = explode('_', $key, 3);
		$userid             = $this->auth($userid);
		$response['status'] = 0;

		if (!$name = Cache::get($key))
		{
			$response['info'] = '文件不存在';
		}
		else
		{

			Cache::del($key);
			/*文件名由 t_xxxx,重命名为 f_xxxx*/
			$bucket         = Config::getSecret('qiniu', 'file');
			$uri            = $bucket . ':f_' . substr($key, 2);
			$file['name']   = $name;
			$file['url']    = $uri;
			$file['use_id'] = $userid;

			if (!File::set($bucket . ':' . $key, $uri)) //修改文件名
			{
				$response['info'] = '文件校验失败';
			}
			elseif (!$fid = FileModel::add($file))
			{
				$response['info'] = '文件保存失败';
			}
			else
			{
				$response['status'] = 1;
				$response['info']   = ['msg' => '保存成功', 'id' => $fid];
			}
		}
		$this->response = $response;
	}

	/**
	 * 获取上传token
	 * POST /file/token
	 * @method POST_token
	 * @param name 文件名
	 */
	public function POST_tokenAction()
	{
		$userid = $this->auth();
		if (Input::post('name', $name, 'title') && $name = File::filterName($name))
		{
			$key    = uniqid('t_' . $userid . '_') . strrchr($name, '.');
			$bucket = Config::getSecret('qiniu', 'file');
			$token  = File::token($bucket, $key);
			if ($token)
			{
				// header('Access-Control-Allow-Origin:http://upload.qiniu.com');
				Cache::set($key, $name, 1200);
				$response['token'] = $token;
				// $response['key']   = $key;
				$response['name'] = $name;
				$this->response(1, $response);
			}
			else
			{
				$this->response(0, 'token获取失败');
			}
		}
		else
		{
			$this->response(0, '文件名无效');
		}
	}

	/**
	 * 删除上传token，放弃上传
	 * DELETE /file/token/tmp_ae1233
	 * @method POST_token
	 * @param name 文件名
	 */
	public function DELETE_tokenAction($key = null)
	{
		if ($key && $name = Cache::get($key))
		{
			list(, $userid) = explode('_', $key, 3);

			$userid = $this->auth($userid);
			Cache::del($key);
			$bucket = Config::getSecret('qiniu', 'file');
			File::del($bucket . ':' . $key);
			$this->response(1, '已经成功删除' . $name);
		}
		else
		{
			$this->response(0, '此上传信息不存在');
		}
	}

	/**
	 * 文件详细信息
	 * GET /file/1
	 * @method GET_info
	 * @author NewFuture
	 */
	public function GET_infoAction($id)
	{
		$userid = $this->auth();
		if ($file = FileModel::where('use_id', '=', $userid)->find($id))
		{
			$file->url = File::get($file->url, $file->name);
			$this->response(1, $file);
		}
		else
		{
			$this->response(0, '你没有该文件');
		}
	}

	/**
	 * 文件信息修改
	 * PUT /file/1
	 * @method PUT_info
	 * @author NewFuture
	 * @param name 文件名
	 */
	public function PUT_infoAction($id = 0)
	{
		$userid = $this->auth();
		if (Input::put('name', $name, 'title'))
		{
			if (FileModel::saveName($id, $name))
			{
				$this->response(1, '成功修改为：' . $name);
			}
			else
			{
				$this->response(0, '修改失败');
			}
		}
		else
		{
			$this->response(0, '文件名无效');
		}
	}

	/**
	 * 文件删除
	 * DELETE /file/1
	 * @method DELETE_info
	 * @author NewFuture
	 */
	public function DELETE_infoAction($id = 0)
	{
		$userid             = $this->auth();
		$File               = FileModel::where('use_id', '=', $userid)->field('url')->find($id);
		$response['status'] = 0;
		if (!$uri = $File['url'])
		{
			$response['info'] = '没有找这个文件';
		}
		elseif (!File::del($uri))
		{
			$response['info'] = '删除出错';
		}
		if ($File->update(['url' => '', 'status' => 0]))
		{
			$response['status'] = 1;
			$response['info']   = '已经删除';
		}
		else
		{
			$response['info'] = '文件状态更新失败';
		}
		$this->response = $response;
	}

	/**
	 * 打印文件
	 * POST /file/print
	 * @method
	 */
	public function POST_printAction()
	{
		$userid             = $this->auth();
		$response['status'] = 0;

		if (!Input::post('id', $id, 'int'))
		{
			$response['info'] = '未选择文件';
		}
		elseif (!Input::post('pid', $pid, 'int'))
		{
			$response['info'] = '未选择打印店';
		}
		elseif (!$file = FileModel::where('use_id', $userid)->where('status', '>', 0)->field('url,name,status')->find($id))
		{
			$response['info'] = '没有该文件或者此文件已经删除';
		}
		else
		{
			$task           = TaskModel::create('post');
			$task['name']   = $file['name'];
			$task['use_id'] = $userid;
			$task['pri_id'] = $pid;

			if(!$task['url']    = File::addTask($file['url']))
			{
				$response['info'] = '文件转换出错';
			}elseif (!$tid = TaskModel::insert($task))
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
}