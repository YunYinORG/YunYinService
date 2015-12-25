<?php
/**
 * 标签
 */

class TagsController extends Rest
{
	/**
	 * 获取相关标签
	 * @method GET_indexAction
	 * @author NewFuture
	 */
	public function GET_indexAction()
	{
		Input::get('page', $page, 'int', 1);
		if (Input::get('key', $key, 'tag')) //关键字
		{
			$key  = '%' . strtr($key, ' ', '%') . '%';
			$tags = TagModel::where('name', 'LIKE', $key)->order('count', 'DESC')->page($page)->select('id,name');
		}
		else
		{
			$tags = TagModel::order('count', 'DESC')->page($page)->select('id,name');
		}
		$this->response(1, $tags);
	}

	/**
	 * 添加标签
	 * @method POST_indexAction
	 * @param  integer          $id [description]
	 * @author NewFuture
	 */
	public function POST_indexAction()
	{
		$uid = $this->auth();
		if (Input::post('name', $name, 'tag'))
		{
			$tag = ['name' => $name, 'use_id' => $uid];
			if ($tid = TagModel::insert($tag))
			{
				$result = ['msg' => '添加成功', 'id' => $tid];
				$this->response(1, $result);
			}
			else
			{
				$this->response(0, '添加失败');
			}
		}
		else
		{
			$this->response(0, '标签名不合法');
		}
	}

	/**
	 * 获取标签详情
	 * @method GET_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 * @todo 标签引用的书籍
	 */
	public function GET_infoAction($id = 0)
	{
		$uid = $this->auth();
		if ($tag = TagModel::belongs('user')->find($id))
		{
			$this->response(1, $tag);
		}
		else
		{
			$this->response(0, '信息已经删除');
		}
	}

	/**
	 * 添加标签
	 * 开放权限
	 * @method POST_infoAction
	 * @param  integer         $id [description]
	 * @author NewFuture
	 */
	public function POST_infoAction($id = 0)
	{
		$uid = $this->auth();
		if (Input::post('sid', $sid, 'int') && TagModel::where('id', $id)->inc('count'))
		{
			$Hastag = new Model('hastag');
			$hastag = ['tag_id' => $id, 'sha_id' => $sid];
			if ($Hastag->update($hastag))
			{
				$this->response(1, '添加成功');
			}
			else
			{
				TagModel::where('id', $id)->inc('count', '-1');
				$this->response(0, '添加出错');
			}
		}
		else
		{
			$this->response(0, '分享或者标签有误');
		}
	}
}