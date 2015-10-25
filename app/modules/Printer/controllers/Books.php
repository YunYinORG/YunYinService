<?php

class BooksController extends Rest
{

	/**
	 * 获取资源列表
	 * @method GET_indexAction
	 * @author NewFuture
	 */
	public function GET_indexAction()
	{
		$pid = $this->authPrinter();
		Input::get('page', $page, 'int', 1);
		$Book = BookModel::where('pri_id', '=', $pid)->page($page);
		if (Input::get('key', $key))
		{
			$key = '%' . strtr($key, ' ', '%') . '%';
			$Book->where('name', 'LIKE', $key)->orWhere('detail', 'LIKE', $key);
		}
		$books = $Book->select('id,name,price,image');
		$this->response(1, $books);
	}

	/**
	 * 添加资源
	 * @method POST_indexAction
	 * @author NewFuture
	 */
	public function POST_indexAction()
	{
		$pid = $this->authPrinter();
		if (Input::post('books', $books))
		{
			/*批量插入*/
			$books = explode("\n", $books);
			$books = array_map(array('\\Parse\\Filter', 'title'), $books);
			$books = array_filter($books);
			$data  = array();
			foreach ($books as $key => $name)
			{
				$data[] = [$pid, $name];
			}
			/*全部插入*/
			if (BookModel::insertAll(['pri_id', 'name'], $data))
			{
				$this->response(1, $books);
			}
			else
			{
				$this->response(0, '批量插入失败');
			}
		}
		elseif (Input::post('name', $name, 'title'))
		{
			/*单个插入*/
			$book['name']   = $name;
			$book['pri_id'] = $pid;
			if (Input::post('price', $price, 'float'))
			{
				$book['price'] = $price;
			}
			if (Input::post('detail', $detail, 'text'))
			{
				$book['detail'] = $detail;
			}

			if ($book['id'] = BookModel::insert($book))
			{
				$this->response(1, $book);
			}
			else
			{
				$this->response(0, '添加失败');
			}
		}
		else
		{
			$this->response(0, '数据无效');
		}
	}

	/**
	 * 获取详情
	 * GET /books/123
	 * @method GET_infoAction
	 * @param  integer        $id [资源id]
	 * @author NewFuture
	 */
	public function GET_infoAction($id = 0)
	{
		$pid = $this->authPrinter();
		if ($book = BookModel::where('pri_id', '=', $pid)->find($id))
		{
			$this->response(1, $book);
		}
		else
		{
			$this->response(0, '信息不存在或者无权访问');
		}
	}

	/**
	 * 删除
	 * DELETE /books/123
	 * @method GET_infoAction
	 * @param  integer        $id [资源id]
	 * @author NewFuture
	 */
	public function DELETE_infoAction($id = 0)
	{
		$pid = $this->authPrinter();
		if (BookModel::delete($id))
		{
			$this->response(1, '删除成功！');
		}
		else
		{
			$this->response(0, '信息不存在或者无权访问');
		}
	}

	/**
	 * 获取详情
	 * PUT /books/123
	 * @method GET_infoAction
	 * @param  integer        $id [资源id]
	 * @author NewFuture
	 */
	public function PUT_infoAction($id = 0)
	{
		$pid  = $this->authPrinter();
		$book = [];
		if (Input::put('name', $name, 'title'))
		{
			$book['name'] = $name;
		}

		if (Input::put('detail', $detail, 'text'))
		{
			$book['detail'] = $detail;
		}

		if (Input::put('price', $price, 'float'))
		{
			$book['price'] = $price;
		}

		if (empty($book))
		{
			$this->response(0, '无修改内容');
		}
		elseif (BookModel::where('id', $id)->where('pri_id', $pid)->update($book))
		{
			$this->response(1, $book);
		}
		else
		{
			$this->response(0, '修改失败');
		}
	}
}