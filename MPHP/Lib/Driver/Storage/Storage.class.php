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
* 存储类工厂
*/
final class Storage
{
	//处理程序
	static public $handler = null;
	static public function init($driver='File')
	{
		if(is_null(self::$handler)){
			self::connect($driver);
		}else{
			return self::$handler;
		}

	}
	//驱动连接
	static public function connect($Dirver = '')
	{
		$Driver = empty($Dirver) ? C('STORAGE_DRIVER') : $Driver;
		$class = $Driver.'Storage';
		self::$handlers = new $calss();
	}
	//调用驱动方法
    public function __call($method, $args)
    {
        if (method_exists(self::$handler, $method)) {
            return call_user_func_array(array(self::$handler, $method), $args);
        }
    }
}