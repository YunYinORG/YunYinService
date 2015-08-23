<?php
/**
 * 文件管理
 */
class FileController extends Rest
{
	/**
	 * 文件列表
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
	 * @method POST_index
	 */
	public function POST_indexAction()
	{
		//TO DO
		//上传
	}

	/**
	 * 获取上传token
	 * @method POST_token
	 */
	public function POST_tokenAction()
	{
		//TO DO
		//上传
	}

	/**
	 * 文件列表
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
	 * @method PUT_info
	 * @author NewFuture
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
}