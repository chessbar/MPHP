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
* 视图处理抽象工厂
*/
final class ViewFactory
{
	/**
	 * 静态工厂实例
	 * @var null
	 */
	public static $viewfactory = null;
	/**
	 * 驱动连接组
	 * @var array
	 */
	protected $driverlist = array();
	/**
	 * 返回工厂实例 单例模式
	 * @param  [type] $driver [description]
	 * @return [type]         [description]
	 */
	public static function factory($driver =null)
	{
		if(is_null(self::$viewfactory)){
			self::$viewfactory = new viewFactory();
		}
		if(is_null($driver)){
			$driver = ucfirst(strtolower(C('TPL_ENGINE')));
		}
		if(isset(self::$viewfactory->driverlist[$driver])){
			return self::$viewfactory->driverlist[$driver];
		}
		self::$viewfactory->getDriver($driver);
		return self::$viewfactory->driverlist[$driver];
	}
	/**
	 * 获得驱动接口
	 * @param  [type] $driver [description]
	 * @return [type]         [description]
	 */
	public function getDriver($driver)
	{	
		$class = 'View'.ucfirst($driver);
		//加载类文件
		if(!class_exists($class,false))
		{
			$classFile = MPHP_DRIVER_PATH.'View/'.$class.'.class.php';
			if(!require_cache($classFile)){
				DEBUG && halt($classFile.'不存在');
			}
		}
		$this->driverlist[$driver] = new $class();
		return true;
	}
}