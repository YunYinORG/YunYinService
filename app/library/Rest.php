<?php
/**
 * Created by PhpStorm.
 * User: goaway
 * Date: 15-6-29
 * Time: 16:19
 */
abstract class Rest extends Yaf_Controller_Abstract
{
	/*允许的请求*/
	// protected $request = array('GET', 'POST', 'PUT', 'DELETE');

	protected $response_type = 'json'; //返回数据格式
	protected $response      = null;   //返回数据

	/**
	 *初始化
	 * @method init
	 * @author NewFuture
	 */
	protected function init()
	{
		$action      = $this->_request->getActionName();
		$method      = strtoupper($this->_request->getMethod());
		$rest_action = $method . '_' . $action; //REST对应的action如PUT_info

		/*检查该action操作是否存在，存在则修改为REST接口*/
		if (method_exists($this, $rest_action . 'Action'))
		{
			$this->_request->setActionName($rest_action);
		}
	}

	/**
	 * 返回信息
	 * @method response
	 * @param  [array]   $data [返回数据]
	 * @param  string   $type [description]
	 * @author NewFuture
	 */
	// protected function response($data)
	// {
	// 	header('Content-type: application/json');
	// 	echo json_encode($data);
	// }

	public function __destruct()
	{
		header('Content-type: application/json');
		echo json_encode($this->response);
	}
}