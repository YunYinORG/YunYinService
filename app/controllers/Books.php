<?php
/**
 * 书籍查看
 */

class BooksController extends Rest
{
	/**
	 * 获取书籍列表
	 * @method GET_indexAction
	 * @author NewFuture
	 */
	public function GET_indexAction()
	{
		Input::get('page', $page, 'int', 1);
		$Book = BookModel::order('count', 'DESC')->page($page);
		if (Input::get('key', $key, 'tag')) //关键字
		{
			$key = '%' . strtr($key, ' ', '%') . '%';
			$Book->where('name', 'LIKE', $key)->orWhere('detail', 'LIKE', $key);
		}

		$books = $Book->select('id,name,detail,pri_id');
		$this->response(1, $books);
	}

	/**
	 * 获取书籍详情
	 * @method GET_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function GET_infoAction($id = 0)
	{
		// $uid = $this->auth();
		if ($book = BookModel::belongs('printer')->find($id))
		{
			$this->response(1, $book);
		}
		else
		{
			$this->response(0, '信息已经删除');
		}
	}

	/**
	 * 打印书籍
	 * @method POST_printAction
	 * @todo 计价
	 * @param  integer          $id [description]
	 * @author NewFuture
	 */
	public function POST_printAction($id = 0)
	{
		$uid                = $this->auth();
		$response['status'] = 0;
		if (!$book = BookModel::find($id))
		{
			$response['info'] = '无效书籍';
		}
		else
		{
			$task           = ['use_id' => $uid, 'url' => 'book/' . $id];
			$task['pri_id'] = $book['pri_id'];
			$task['name']   = $book['name'];
			if ($tid = TaskModel::insert($task))
			{
				$response['info'] = ['id' => $tid, 'msg' => '保存成功'];
			}
			else
			{
				$response['info'] = '保存出错';
			}
		}
		$this->response = $response;
	}
}