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
	const AUTH_FAIL        = -1;
	const AUTH_BAN         = -2;
	/**
	 * 初始化 REST 路由
	 * 修改操作 和 绑定参数
	 * @method init
	 * @author NewFuture
	 */
	protected function init()
	{
		Yaf_Dispatcher::getInstance()->disableView(); //立即输出响应，并关闭视图模板引擎
		/*请求来源，跨站响应*/
		$from=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:(isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:false);
		if ($from)
		{
			/*跨站请求*/
			$from = parse_url($from);
			if (isset($from['host']) && substr($from['host'], -11) == '.yunyin.org')
			{
				$cors = Config::get('cors');
				foreach ($cors as $key => $value)
				{
					header($key . ':' . $value);
				}
				/*允许来自cors跨站响应头*/
				header('Access-Control-Allow-Origin:' . $from['scheme'] . '://' . $from['host']);
			}
		}

		/*请求操作判断*/
		$request = $this->_request;
		$method  = $request->getMethod();
		if ($method == 'OPTIONS')
		{
			/*cors应答*/
			exit();
		}
		elseif ($method == 'PUT')
		{
			//put请求写入GOLBAL中和post get一样
			parse_str(file_get_contents('php://input'), $GLOBALS['_PUT']);
		}

		/*Action路由*/
		$action = $request->getActionName();
		if (is_numeric($action))
		{
			/*数字id映射带infoAction*/
			$request->setParam('id', intval($action));
			$path   = substr(strstr($_SERVER['PATH_INFO'], $action), strlen($action) + 1);
			$action = $path ? strstr($path . '/', '/', true) : 'info';
		}

		$rest_action = $method . '_' . $action; //对应REST_Action

		/*检查该action操作是否存在，存在则修改为REST接口*/
		if (method_exists($this, $rest_action . 'Action'))
		{
			/*存在对应的操作*/
			$request->setActionName($rest_action);
		}
		elseif (!method_exists($this, $action . 'Action'))
		{
			/*action和REST_action 都不存在*/
			$this->response = array(
				'error' => '未定义操作',
				'method' => $method,
				'action' => $action,
				'controller' => $request->getControllerName(),
				'module' => $request->getmoduleName(),
			);
			exit;
		}
	}

	/**
	 * 验证用户信息
	 * 验证用户是否登录或者是否为当前用户
	 * 如果验证失败，立即返回错误信息并终止执行
	 * @method auth
	 * @param  int $user_id [有则验证是否为当前用户，否则只验证是否登录]
	 * @return [type]           [description]
	 * @author NewFuture
	 */
	protected function auth($user_id = false)
	{
		if (!$uid = Auth::id())
		{
			/*验证是否有效*/
			$this->response(self::AUTH_FAIL, '用户信息验证失效，请重新登录！');
			exit();
		}
		elseif ($user_id !== false && $user_id != $uid)
		{
			/*资源所有权验证*/
			$this->response(self::AUTH_BAN, '账号验证失败无权访问！');
			exit();
		}
		else
		{
			return $uid;
		}
	}

	/**
	 * 验证打印店登录信息
	 * 如果验证失败，立即返回错误信息并终止执行
	 * @method auth
	 * @param  int $pid [有则验证是否为当前用户，否则只验证是否登录]
	 * @return [type]           [description]
	 * @author NewFuture
	 */
	protected function authPrinter($pid = false)
	{
		if (!$id = Auth::priId())
		{
			/*验证是否有效*/
			$this->response(self::AUTH_FAIL, '登陆信息验证失效，请重新登录！');
			exit();
		}
		elseif ($pid && $pid != $id)
		{
			/*资源所有权验证*/
			$this->response(self::AUTH_BAN, '账号验证失败或者无权访问！');
			exit();
		}
		else
		{
			return $id;
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
			header('Content-type: application/json;charset=utf-8');
			echo (json_encode($this->response, JSON_UNESCAPED_UNICODE)); //unicode不转码
		}
	}
}