<?php
use \Service\Message;
use \Service\Smtp;

/**
 * 邮件发送类
 * TODO 模板渲染
 */
Class Mail
{
	protected $_config;
	private $_smtp;
	private static $_instance = null;

	/**
	 * 发送验证邮件
	 * @method verify
	 * @param  [type] $email [description]
	 * @param  [type] $name  [description]
	 * @param  [type] $link  [description]
	 * @return [type]        [description]
	 * @author NewFuture
	 */
	public static function verify($email, $name, $link)
	{
		$instance     = self::getInstance();
		$from         = $instance->_config['verify'];
		$to           = ['email' => $email, 'name' => $name ?: $email];
		$msg['title'] = '云印验证邮件';
		$msg['body']  = $link; //TODO 渲染模板

		return $instance->send($from, $to, $msg);
	}

	/**
	 * 发送通知邮件
	 * @method notify
	 * @param  [type] $email [description]
	 * @param  [type] $name  [description]
	 * @param  [type] $body  [description]
	 * @return [type]        [description]
	 * @author NewFuture
	 */
	public static function notify($email, $name, $body)
	{
		$instance = self::getInstance();

		$from = $instance->_config['notify'];
		$to           = ['email' => $email];
		$to['name']   = $name ?: $email;
		$msg['title'] = '云印通知邮件';
		$msg['body']  = $body; //TODO 渲染模板
		return $instance->send($from, $to, $msg);
	}

	/**
	 * 发送邮件
	 * @method send
	 * @param  [type] $from [description]
	 * @param  [type] $to   [description]
	 * @param  [type] $msg  [description]
	 * @return bool       [description]
	 * @author NewFuture
	 */
	public function send($from, $to, $msg)
	{
		$Message = new Message();
		$Message->setFrom($from['name'], $from['email'])
		        ->addTo($to['name'], $to['email'])
		        ->setSubject($msg['title'])
		        ->setBody($msg['body']);
		return $this->_smtp
		            ->setAuth($from['email'], $from['pwd'])
		            ->send($Message);
	}

	public static function getInstance()
	{
		return self::$_instance ?: (self::$_instance = new self());
	}

	private function __construct()
	{
		$this->_config = Config::getSecret('mail');
		$this->_smtp   = new Smtp();
		$server        = $this->_config['server'];
		$this->_smtp->setServer($server['smtp'], $server['port'], $server['secure']);
	}
}