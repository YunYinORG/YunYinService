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
	public function _initConfig()
	{
		$config = Yaf_Application::app()->getConfig()->toArray();
		Yaf_Registry::set('config', $config);
	}

	/**
	 * 关闭视图输出
	 * @method _initView
	 * @author NewFuture
	 */
	public function _initView(Yaf_Dispatcher $dispatcher)
	{
		$dispatcher->disableView();
	}
}