<?php 
	class PhpLog
	{
		const DEBUG = 1;// Most Verbose
		const INFO = 2;// ...
		const WARN = 3;// ...
		const ERROR = 4;// ...
		const FATAL = 5;// Least Verbose
		const OFF = 6;// Nothing at all.
		 
		const LOG_OPEN = 1;
		const OPEN_FAILED = 2;
		const LOG_CLOSED = 3;
		 
		/* Public members: Not so much of an example of encapsulation, but that's okay. */
		public $Log_Status = PhpLog::LOG_CLOSED;
		public $DateFormat= "Y-m-d G:i:s";
		public $MessageQueue;
		 
		private $filename;
		private $log_file;
		private $priority = PhpLog::INFO;
		 
		private $file_handle;
		
		/**
		 * AUTHOR:	gu_yongkang
		 * DATA:	20110322
		 * Enter description here ...
		 * @param $filepath
		 * 文件存储的路径
		 * @param $timezone
		 * 时间格式，此处设置为"PRC"（中国）
		 * @param $priority
		 * 设置运行级别
		 */
		 
		public function __construct( $filepath, $timezone, $priority )
		{
		}
		 
		public function __destruct()
		{
		}
		
		/**
	     *作用:创建目录
	     *输入:要创建的目录
	     *输出:true | false
	     */
		private  function _createDir($dir)
		{
		}
		
		/**
	     *作用:构建路径
	     *输入:文件的路径,要写入的文件名
	     *输出:构建好的路径字串
	     */
		private function createPath($dir, $filename)
		{
		}
		 
		public function LogInfo($line)
		{
		}
		 
		public function LogDebug($line)
		{
		}
		 
		public function LogWarn($line)
		{
		}
		 
		public function LogError($line)
		{
		}
		 
		public function LogFatal($line)
		{
		}

		/**
		 * Author ： gu_yongkang
		 * Enter description here ...
		 * @param unknown_type $line
		 * content 内容
		 * @param unknown_type $priority
		 * 打印级别
		 * @param unknown_type $sFile
		 * 调用打印日志的文件名
		 * @param unknown_type $iLine
		 * 打印文件的位置（行数）
		 */
		public function Log($line, $priority, $sFile, $iLine)
		{
		}
		 // 支持输入多个参数
		public function WriteFreeFormLine( $line )
		{
		}
		private function getRemoteIP()
		{
		}
		 
		private function getTimeLine( $level, $FilePath, $FileLine)
		{
		}
	}
?>
