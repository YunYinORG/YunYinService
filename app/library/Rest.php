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
		$action      = $this->_request->getActionName();
		$method      = strtoupper($this->_request->getMethod());
		$rest_action = $method . '_' . $action; //REST对应的action如PUT_info
		/*检查该action操作是否存在，存在则修改为REST接口*/
		if (ctype_digit($action))
		{
			/*数字之间映射GET /user/123 => user/GET_info参数id=123;*/
			$this->_request->setActionName($method . '_info'); //数字映射
			$this->_request->setParam('id', $action);          //绑定参数
		}
		elseif (method_exists($this, $rest_action . 'Action'))
		{
			$this->_request->setActionName($rest_action);
		}
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