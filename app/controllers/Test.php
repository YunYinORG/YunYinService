<?php
/**
 *接口测试
 */
class TestController extends Rest
{
	/**
	 * @method index
	 * @param  string $value        [description]
	 * @return [type] [description]
	 * @author NewFuture
	 */
	public function indexAction()
	{
		$method  = $_SERVER['REQUEST_METHOD'];
		$reponse = [
			'method' => $method,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'token'=> $_SERVER['HTTP_TOKEN'],
			'data' => $_REQUEST,
			'param' => $GLOBALS['_' . $method],
		];
		$this->response = $reponse;
	}

	public function GET_requestAction()
	{
		$reponse = [
			'method' => $_SERVER['REQUEST_METHOD'],
			'uri' => $_SERVER['REQUEST_URI'],
			'param' => $_GET,
		];
		$this->response = $reponse;
	}

	public function POST_requestAction()
	{
		$reponse = [
			'method' => $_SERVER['REQUEST_METHOD'],
			'uri' => $_SERVER['REQUEST_URI'],
			'param' => $_POST,
		];
		$this->response = $reponse;
	}

	public function PUT_requestAction()
	{
		$reponse = [
			'method' => $_SERVER['REQUEST_METHOD'],
			'uri' => $_SERVER['REQUEST_URI'],
			'param' => $GLOBALS['_PUT'],
		];
		$this->response = $reponse;
	}

	public function DELETE_requestAction()
	{
		$reponse = [
			'method' => $_SERVER['REQUEST_METHOD'],
			'uri' => $_SERVER['REQUEST_URI'],
		];
		$this->response = $reponse;
	}
}
?>