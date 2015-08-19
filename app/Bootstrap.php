<?php
/**
 * 启动加载
 */
class Bootstrap extends Yaf_Bootstrap_Abstract
{

	/**
	 * 初始配置
	 * @method _initConfig
	 * @author NewFuture
	 */
	// public function _initConfig()
	// {
	// 	$config = Yaf_Application::app()->getConfig()->toArray();
	// }

	/**
	 * 关闭视图输出
	 * @method _initView
	 * @author NewFuture
	 */
	public function _initView(Yaf_Dispatcher $dispatcher)
	{
		//关闭视图
		$dispatcher->disableView();
		//路由
		$dispatcher->getRouter()->addConfig(Config::get('routes'));
	}

	/**
	 * 开启调试输出
	 * @method _initDebug
	 * @author NewFuture
	 */
	public function _initDebug()
	{
		if (Config::get('isdebug'))
		{
			/*加载 PHP Console Debug模块*/
			Yaf_Loader::import('PhpConsole/__autoload.php');
			$handler = PhpConsole\Handler::getInstance();
			$handler->start();
			PhpConsole\Helper::register();

			$connector  = PhpConsole\Connector::getInstance();
			$dispatcher = $connector->getDebugDispatcher();
			$connector->setSourcesBasePath(APP_PATH);
			$connector->setServerEncoding('utf8');
			$dispatcher->detectTraceAndSource = true;

			if ($pwd = Config::get('debug.auth'))
			{
				$connector->setPassword($pwd);
				$connector->startEvalRequestsListener();
			}
		}
	}
}