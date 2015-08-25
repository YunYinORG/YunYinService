<?php
/**
 * 文件管理
 */
use Service\File;

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
		$files  = FileModel::where('use_id', '=', $userid)->select('id,name,time');
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
		$userid             = $this->auth();
		$response['status'] = 0;
		if (!Input::post('key', $key, 'trim'))
		{
			$response['info'] = '未收到数据';
		}
		elseif (!$name = Cookie::get($key))
		{
			$response['info'] = '文件不存在';
		}
		else
		{
			Cookie::del($key);
			//文件名由 temp_xxxx,重命名为 file_xxxx,
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
				$response['info']   = '保存成功';
				$response['id']     = $fid;
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
		if (Input::post('name', $name, 'trim'))
		{
			$key   = uniqid('temp_' . $userid . '.');
			$token = File::getToken('temp_' . $key);
			if ($token)
			{
				Cookie::set($key, $name);
				$response['token'] = $files;
				$response['key']   = $key;
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
	 * 文件列表
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
		if (Input::put('name', $name, 'tirm'))
		{
			if ($file = FileModel::where('id', $id)->where('use_id', $userid)->update(['name' => $name]))
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
		elseif ($File->set('url', '')->save())
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