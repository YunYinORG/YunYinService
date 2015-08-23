<?php
/**
 * REST 控制器
 */
abstract class Rest extends Yaf_Controller_Abstract
{
	/*允许的请求*/
	// protected $request = array('GET', 'POST', 'PUT', 'DELETE');

	private $response_type = 'json'; //返回数据格式
	protected $response    = false;  //返回数据

	/**
	 * 初始化 REST 路由
	 * 修改操作 和 绑定参数
	 * @method init
	 * @author NewFuture
	 */
	protected function init()
	{
		Yaf_Dispatcher::getInstance()->disableView(); //关闭视图模板引擎
		$action      = $this->_request->getActionName();
		$method      = strtoupper($this->_request->getMethod());
		$rest_action = $method . '_' . $action; //REST对应的action如PUT_info

		if ($method == 'PUT')
		{
			//put请求写入GOLBAL中和post get一样
			parse_str(file_get_contents('php://input'), $GLOBALS['_PUT']);
		}
		/*检查该action操作是否存在，存在则修改为REST接口*/
		if (ctype_digit($action))
		{
			/*数字之间映射GET /user/123 => user/GET_info参数id=123;*/
			$this->_request->setActionName($method . '_info'); //数字映射
			$this->_request->setParam('id', $action);          //绑定参数
		}
		elseif (method_exists($this, $rest_action . 'Action'))
		{
			/*存在对应的操作*/
			$this->_request->setActionName($rest_action);
		}
		elseif (!method_exists($this, $action . 'Action'))
		{
			/*action和REST_actiodn 都不存在*/
			$this->response = array(
				'error' => '未定义操作',
				'method' => $method,
				'action' => $action,
				'controller' => $this->_request->getControllerName(),
			);
			exit;
		}
	}

	/**
	 * 设置返回信息
	 * @method response
	 * @param  [type]   $status [请求结果]
	 * @param  string   $info   [请求信息]
	 * @return [type]           [description]
	 * @author NewFuture
	 */
	protected function response($status, $info = '')
	{
		$this->response = ['status' => $status, 'info' => $info];
	}

	/**
	 * 结束时自动输出信息
	 * @method __destruct
	 * @access private
	 * @author NewFuture
	 */
	public function __destruct()
	{
		if ($this->response !== false)
		{
			switch ($this->response_type)
			{
				case 'xml':
					header('Content-type: application/xml');
					echo Parse\Xml::encode($this->response);
					break;

				case 'json':
				default:
					header('Content-type: application/json');
					echo json_encode($this->response);
					break;
			}
		}
	}

}