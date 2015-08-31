<?php
/**
 * 文件共享
 *
 */
// use Service\File;

class ShareController extends Rest
{
	/**
	 * 文件列表
	 * GET /share/
	 * @method GET_index
	 * @author NewFuture
	 */
	public function GET_indexAction()
	{
		$userid = $this->auth();
		$shares = ShareModel::where('use_id', '=', $userid)->select('id,name,time');
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
		elseif (!$File = FileModel::filed('name,url')
				->where('use_id', '=', $userid)
				->where('status', '>', 0)
				->find($id))
		{
			/*数据库中查询的文件*/
			$this->response['info'] = '文件无效';
		}
		else
		{
			/*验证完成，开始插入*/
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
				//TODO
				//分享文件预处理
				$response['status'] = 1;
				$response['info']   =
				$response['id']     = $id;
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
		if ($share = ShareModel::find($id))
		{
			$this->response(1, $share);
		}
		else
		{
			$this->response(0, '不存在');
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
			$this->response(0, '没有提交任何内容');
		}
		elseif (ShareModel::where('id', $id)->where('use_id', $use_id)->update($share))
		{
			$this->response(1, '保存成功');
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
		if (ShareModel::where('id', $id)->where('use_id', $userid)->set('status', 0)->save)
		{
			$this->response(1, '删除成功');
		}
		else
		{
			$this->response(0, '删除失败');
		}
	}
}