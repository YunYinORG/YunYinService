<?php
/**
 * 短信发送
 */
class Sms
{
	private static $_handler = null;
	private static $_template;

	/**
	 * 绑定手机
	 * @param $code验证码
	 */
	public function bind($Phone, $code)
	{
		$msg = $code . ',5';
		return self::send($phone, $msg, 'bind');
	}

	/**
	 * 找回密码
	 * @param $code 验证码
	 */
	public function findPwd($phone, $code)
	{
		$msg = $code . ',5';
		return self::send($phone, $msg, 'pwd');
	}

	/**
	 * [card description]
	 * @method card
	 * @param  string $phone        [发送到手机]
	 * @param  string $receiver     [接收者姓名]
	 * @param  string $finder       [拾到卡者姓名]
	 * @param  string $connectPhone [联系方式]
	 */
	public function card($phone, $receiver, $finder, $connectPhone)
	{
		$msg = $receiver . ',' . $finder . ',' . $connectPhone;
		return self::send($phone, $msg, 'card');
	}

	/**
	 * 已打印通知
	 * @method printed
	 * @param  string  $phone    [发送到手机]
	 * @param  string  $printer  [打印店]
	 * @param  string  $taskid   [订单号]
	 * @param  string  $filename [文件名]
	 */
	public function printed($phone, $printer, $taskid, $filename)
	{
		$msg = $printer . ',' . $taskid . ',' . $filename;
		return self::send($phone, $msg, 'printed');
	}

	/**
	 * 发送短信
	 * @method send
	 * @param  [type] $phone   [手机]
	 * @param  [type] $msg     [内容参数]
	 * @param  [type] $tplName [模板名]
	 * @author NewFuture
	 */
	private static function send($phone, $msg, $tplName)
	{
		if (null == self::$_handler)
		{
			$config          = Config::getSecret('sms');
			$_handler        = new Service\Ucpass($config['account'], $config['appid'], $config['token']);
			self::$_template = $config['template'];
		}
		return self::$_handler->send($phone, $msg, self::$_template[$tplName]);
	}
}