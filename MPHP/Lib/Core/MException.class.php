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
*异常处理类
*/
final class MException extends Exception{
	//异常类型
	private $type;
	//是否存在多余调试信息
	private $extra;
	public function __construct($message,$code = 0,$extra = false)
	{
		parent::__construct($message,$code);
		$this->type = get_class($this);
		$this->extra = $extra;
	}
	/**
	 * 异常输出 所有异常处理类均通过__toSting方法输出错误
	 * 每次异常都会写入日志
	 * 该方法可以被子类重载
	 * @return string [description]
	 */
	public function __toString()
	{
		$trace = $this->getTrace();
		$this->class = isset($trace[0]['class']) ? $trace[0]['class'] : '';
		$this->function = isset($trace[0]['function']) ? $trace[0]['function'] : '';
		$this->file = isset($trace[0]['file']) ? $trace[0]['file'] : '';
		$this->line = isset($trace[0]['line']) ? $trace[0]['line'] : '';
		$traceInfo = '';
		$time = date('y-m-d H:i:s');
		foreach ($trace as $t) {
			if(isset($t['file'])){
				$traceInfo .= '['.$time.']'.$t['file'].'('.$t['line'].')';
				if(isset($t['class'])){
					$traceInfo .= $t['class'].$t['type'].$t['function'];
				}
				$traceInfo .= "\n";
			}
		}
		$error['message'] = $this->message;
		$error['type'] = $this->type;
		$error['class'] = $this->class;
		$error['function'] = $this->function;
		$error['file'] = $this->file;
		$error['line'] = $this->line;
		$error['trace'] = $traceInfo;
		//记录
		if(C('LOG_EXCEPTION_RECORD')){
			Log::write('('.$this->type.')'.$this->message);
		}
		return $error;
	}
}