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
		if ($books = BookModel::select())
		{
			$this->response(1, $books);
		}
		else
		{
			$this->response(0, '没有找到〒_〒');
		}
	}

	/**
	 * 获取书籍详情
	 * @method GET_infoAction
	 * @param  integer        $id [description]
	 * @author NewFuture
	 */
	public function GET_infoAction($id = 0)
	{
		$uid = $this->auth();
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
			$task           = ['use_id' => $uid, 'url' => 'book_' . $id];
			$task['pri_id'] = $book['pri_id'];
			$task['name']   = $book['name'];
			if ($tid = TaskModel::insert($task))
			{
				$response['id']   = $tid;
				$response['info'] = '保存成功';
			}
			else
			{
				$response['info'] = '保存出错';
			}
		}
		$this->response = $response;
	}
}