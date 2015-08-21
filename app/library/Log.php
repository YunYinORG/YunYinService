<?php
/**
 * 日志记录
 */
class Log
{
	private static $_dir    = null;
	private static $_tags   = null; //运行写入的日志级别
	private static $_stream = null; //
	/**
	 * 写入日志
	 * @method write
	 * @param  [type]  $msg   [消息]
	 * @param  integer $tag [日志级别]
	 * @return [bool]         [description]
	 * @author NewFuture
	 */
	public static function write($msg, $tag = 'INFO')
	{
		if ($stream = self::getStream($tag))
		{
			$msg = '[' . date('Y-m-j H:i:s') . '] ' . $msg . PHP_EOL;

			return fwrite($stream, $msg);
		}
	}

	/**
	 * 获取写入流
	 * @method getStream
	 * @param  [type]    $tag [description]
	 * @return [type]           [description]
	 * @author NewFuture
	 */
	private static function getStream($tag)
	{
		if (null === self::$_dir)
		{
			//日志目录
			$logdir = Config::get('log.dir');
			if (!Storage\File::mkdir($logdir))
			{
				throw new Exception('目录文件无法创建' . $logdir, 1);
			}
			self::$_dir = $logdir;
			//日志级别
			self::$_tags = explode(',', Config::get('log.allow'));
			date_default_timezone_set('PRC');
		}

		/*级别过滤*/
		if (!in_array($tag, self::$_tags))
		{
			return false;
		}
		/*打开文件*/
		if (!isset(self::$_stream[$tag]))
		{
			//打开日志文件
			$file = self::$_dir . DIRECTORY_SEPARATOR . $tag . '.log';
			if (!self::$_stream[$tag] = fopen($file, 'a'))
			{
				throw new Exception('Cannot log to file: ' . $file);
			}
		}
		return self::$_stream[$tag];
	}

	public function __destruct()
	{
		foreach (self::$_stream as $stream)
		{
			fclose(self::$stream);
		}
	}
}
?>