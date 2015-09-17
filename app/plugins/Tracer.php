<?php
/**
 * 时间跟踪统计
 * 和文件加载统计
 */
class TracerPlugin extends Yaf_Plugin_Abstract
{
	private $time = [];

	public function __construct()
	{
		$start = $_SERVER['REQUEST_TIME_FLOAT'] * 1000;
		Log::write($start . ' 请求:' . getenv('REQUEST_URI'), 'TRACER');
		$this->time['request'] = $start;
	}

	//在路由之前触发，这个是7个事件中, 最早的一个. 但是一些全局自定的工作, 还是应该放在Bootstrap中去完成
	public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
	{
		$this->time['routerstartup'] = self::mtime();
	}

//路由结束之后触发，此时路由一定正确完成, 否则这个事件不会触发
	public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
	{
		$this->time['routershutdown'] = self::mtime();
	}

//分发循环开始之前被触发
	public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
	{
		$this->time['dispatchloopstartup'] = self::mtime();
	}

// //分发之前触发	如果在一个请求处理过程中, 发生了forward, 则这个事件会被触发多次
	// 	public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
	// 	{
	// 		$this->time['predispatch'] = self::mtime();
	// 	}

//分发结束之后触发，此时动作已经执行结束, 视图也已经渲染完成. 和preDispatch类似, 此事件也可能触发多次
	public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
	{
		$this->time['postdispatch'] = self::mtime();
	}

	//分发循环结束之后触发，此时表示所有的业务逻辑都已经运行完成, 但是响应还没有发送
	public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
	{
		$this->time['dispatchloopshutdown'] = self::mtime();
	}

	// public function preResponse(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
	// {
	// 	echo 1;
	// 	$this->time['preresponse'] = self::mtime();
	// }

	/**
	 * 获取当前毫秒数
	 * @method mtime
	 * @return [type] [description]
	 * @author NewFuture
	 */
	public static function mtime()
	{
		list($usec, $sec) = explode(' ', microtime());
		return ((float) $usec + (float) $sec) * 1000;
	}

	public function __destruct()
	{
		$included_files = get_included_files();
		$file_msg       = '文件加载[' . count($included_files) . ']' . print_r($included_files, true);
		$time           = $this->time;
		$end            = self::mtime();
		$time_msg       = '启动时间:' . $time['request'];
		$time_msg .= PHP_EOL . '结束时间:' . $end;
		$time_msg .= PHP_EOL . '框架启动耗时：' . ($time['routerstartup'] - $time['request']) . 'ms';
		$time_msg .= PHP_EOL . '路由处理耗时：' . ($time['routershutdown'] - $time['routerstartup']) . 'ms';
		$time_msg .= PHP_EOL . '分发准备耗时：' . ($time['dispatchloopstartup'] - $time['routershutdown']) . 'ms';

		if (isset($time['dispatchloopshutdown']))
		{
			$time_msg .= PHP_EOL . '处理过程耗时：' . ($time['dispatchloopshutdown'] - $time['dispatchloopstartup']) . 'ms';
			$time_msg .= PHP_EOL . '输出关闭耗时:' . ($end - $time['dispatchloopshutdown']) . 'ms';
		}
		else
		{
			$time_msg .= PHP_EOL . '处理过程耗时:' . ($end - $time['dispatchloopstartup']) . 'ms';
		}

		$time_msg .= PHP_EOL . '总耗时：' . ($end - $time['request']) . 'ms';
		Log::write($file_msg . $time_msg . PHP_EOL, 'TRACER');
	}
}