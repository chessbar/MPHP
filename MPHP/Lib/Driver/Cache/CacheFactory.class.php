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
*缓存驱动工厂
*/
final class CacheFatory
{
	public static $cacheFactory = null;//静态工程实例
	protected $cacheList = array();//驱动连接组
	public static  function factory($options)
	{
		$options = is_array($options) ? $options : array();
		//只实例化一个对象
		if(is_null(self::$cacheFactory)){
			self::$cacheFactory = new CacheFactory();
		}
		$driver = isset($options['driver']) ? $options['driver'] : C('CACHE_TYPE');
		//静态缓存实例
		$driverName = md5_d($options);
		//对象实例存在
		if(isset(self::$cacheFactory->cacheList[$driverName])){
			return self::$cacheFactory->cacheList[$driverName];
		}
		$class = 'Cache'.ucwords(strtolower($driver));//驱动缓存
		if(!class_exists($class)){
			$classFile = MPHP_DRIVER_PATH.'Cache/'.$class.'.class.php';
			//加载驱动类库文件
			if(!require_cache($classFile)){
				halt('缓存类型指定错误，不存在缓存驱动文件：'.$classFile);
			}
		}
		$cacheObj = new $class();
		self::$cacheFactory->cacheList[$driverName] = $cacheObj;
		return self::$cacheFactory->cacheList[$driverName];
	}
}