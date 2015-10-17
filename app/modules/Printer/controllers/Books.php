<?php

class BooksController extends PrinterRest
{

	/**
	 * 获取资源列表
	 * @method GET_indexAction
	 * @author NewFuture
	 */
	public function GET_indexAction()
	{
		$pid = $this->auth();
		Input::get('page', $page, 'int', 1);
		$Book = BookModel::where('pri_id', '=', $pid)->page($page);
		if (Input::get('key', $key))
		{
			$key = '%' . strtr($key, ' ', '%') . '%';
			$Book->where('name', 'LIKE', $key)->orWhere('detail', 'LIKE', $key);
		}

		if ($books = $Book->select('id,name,price,image'))
		{
			$this->response(1, $books);
		}
		else
		{
			$this->response(0, '还有没有添加资源');
		}
	}

	/**
	 * 添加资源
	 * @method POST_indexAction
	 * @author NewFuture
	 */
	public function POST_indexAction()
	{
		$pid = $this->auth();

	}

	/**
	 * 获取书籍详情
	 * GET /books/123
	 * @method GET_infoAction
	 * @param  integer        $id [资源id]
	 * @author NewFuture
	 */
	public function GET_infoAction($id = 0)
	{
		$pid = $this->auth();
		if ($book = BookModel::where('pri_id', '=', $pid)->find($id))
		{
			$this->response(1, $book);
		}
		else
		{
			$this->response(0, '信息不存在或者无权访问');
		}
	}
}