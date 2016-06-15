<?php
// .-----------------------------------------------------------------------------------
// |  Software: [MPHP framework]
// |   Version: 2016.01
// |-----------------------------------------------------------------------------------
// |    Author: M <1006760526@qq.com>
// |-----------------------------------------------------------------------------------
// |   License: http://www.apache.org/licenses/LICENSE-2.0
// '-----------------------------------------------------------------------------------
/**
*@version
*日志出处理类
*/
class Log{
	const FATAL = 'FATAL';//严重错误，导致系统崩溃无法使用
	const ERROR = 'ERROR';//一般错误
	const WARNING = 'WARNING';//警告性错误
	const NOTICE = 'NOTICE';//通知：程序可以运行但是不完美
	const DEBUG = 'DEBUG';//调试信息
	const SQL = 'SQL';//SQL语句
	//日志信息
	static $log = array();
	/**
	 * 记录日志内容
	 * @param  [type]  $message 错误信息
	 * @param  [type]  $level   级别
	 * @param  boolean $record  是否记录
	 * @return [type]           [description]
	 */
	static public function record($message,$level=self::ERROR,$record = false)
	{
		if($record || in_array($level,C('LOG_LEVEL'))){
			self::$log[] = date('[ c ]') . "{$level}:{$message}\r\n";
		}
	}
	/**
	 * 储存日志信息
	 * @param  integer $type         处理方式
	 * @param  [type]  $destination  日志文件
	 * @param  [type]  $extraHeaders 额外信息（发送邮件）
	 * @return [type]                [description]
	 */
	static public function save($type = 3 ,$destination = null,$extraHeaders =null)
	{
		if(empty(self::$log)) return;
		if(is_null($destination)){
			$destination = APP_LOG_PATH.date('Y_m_d').'.log';
		}
		if(is_dir(APP_LOG_PATH)) error_log(implode("",self::$log)."\r\n",$type,$destination,$extraHeaders);
		self::$log = array();
	}
	/**
	 * 写入日志内容
	 * @param  [type]  $message      内容
	 * @param  integer $type         级别
	 * @param  [type]  $destination  日志文件
	 * @param  [type]  $extraHeaders 额外信息
	 * @return [type]                [description]
	 */
	static public function write($message,$level = self::ERROR,$type=3,$destination=null,$extraHeaders=null)
	{
		if(is_null($destination)){
			$destination = APP_LOG_PATH.date('Y_m_d').'.log';
		}
		if(is_dir(APP_LOG_PATH)) error_log(date("[ c ]")."{$level}:{$message}\r\n",$type,$destination,$extraHeaders);
	}
}