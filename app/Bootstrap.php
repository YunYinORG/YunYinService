<?php
/**
 * 启动加载
 */
class Bootstrap extends Yaf_Bootstrap_Abstract
{

	/**
	 * 开启调试输出
	 * @method _initRoute
	 * @author NewFuture
	 */
	public function _initRoute(Yaf_Dispatcher $dispatcher)
	{
		$dispatcher->getRouter()->addConfig(Config::get('routes'));
	}
}