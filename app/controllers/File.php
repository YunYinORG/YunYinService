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
		$files = FileModel::where('use_id', '=', $userid)->page($page)->select('id,name,time');
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

		if (!Input::post('key', $key, 'char_num'))
		{
			$this->response(0, '未收到数据');
			return;
		}
		$userid = substr(strrchr($key, '_'), 1);
		$userid = $this->auth($userid);

		$response['status'] = 0;

		if (!$name = Cache::get($key))
		{
			$response['info'] = '文件不存在';
		}
		else
		{

			Cache::del($key);
			/*文件名由 temp_xxxx,重命名为 file_xxxx*/
			$newfile        = strtr($key, 'temp', 'file') . '_' . $_SERVER['REQUEST_TIME'];
			$file['name']   = $name;
			$file['url']    = $newfile;
			$file['use_id'] = $userid;

			if (!File::set($key, $newfile)) //修改文件名
			{
				$response['info'] = '文件校验失败';
			}
			elseif (!$fid = FileModel::add($file));
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
			$key   = uniqid('temp_' . $userid . '_');
			$token = File::token($key);
			if ($token)
			{
				header('Access-Control-Allow-Origin:http://upload.qiniu.com');
				Cache::set($key, $name, 1200);
				$response['token'] = $token;
				$response['key']   = $key;
				$response['name']  = $name;
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
	 * DELETE /file/token/temp_ae1233
	 * @method POST_token
	 * @param name 文件名
	 */
	public function DELETE_tokenAction($key = null)
	{
		if ($key && $name = Cache::get($key))
		{
			$userid = substr(strrchr($key, '_'), 1);
			$userid = $this->auth($userid);
			Cache::del($key);
			File::del($key);
			$this->response(1, '已经成功取消' . $name);
		}
		else
		{
			$this->response(0, '此任务不存在');
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
		$File               = FileModel::where('id', '=', $id)->where('userid', '=', $userid);
		$response['status'] = 0;
		if (!$path = $File->get('url'))
		{
			$response['info'] = '没有找这个文件';
		}
		elseif (!File::del($path))
		{
			$response['info'] = '删除出错';
		}
		elseif ($File->set('url', '')->set('status', 0)->save())
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
}