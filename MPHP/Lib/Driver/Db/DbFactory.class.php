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
* 数据驱动工厂
*/
final class DbFactory
{
	public static $dbFactory = null;//静态工厂实例
	protected $driverList = array();//驱动组
	/**
	 * 获得工厂实例 单例模式
	 * @param  [type] $driver 连接驱动
	 * @param  [type] $table  表
	 * @param  [type] $full   全表
	 * @return [type]         [description]
	 */
	public static function factory($driver,$table,$full)
	{
		//只实例化一个对象
		if(is_null(self::$dbFactory)){
			self::$dbFactory = new DbFactory();
		}
		if(is_null($driver)){
			$driver = ucfirst(C('DB_DRIVER'));
		}
		//数据库驱动存在并且连接正常
		if(isset(self::$dbFactory->driverList[$table]) && self::$dbFactory->driverList[$table]->link){
			return self::$dbFactory->driverList[$table];
		}
		//获得驱动连接
		if(self::$dbFactory->getDriver($driver,$table,$full)){
			return self::$dbFactory->driverList[$table];
		}else{
			return false;
		}
	}
	/**
	 * 获得数据库驱动接口
	 * @param  [type] $driver [description]
	 * @param  [type] $table  [description]
	 * @param  [type] $full   [description]
	 * @return [type]         [description]
	 */
	private function getDriver($driver,$table,$full)
	{
		$class = "Db".$driver;//数据库驱动
		$this->driverList[$table] = new $calss();
		return $this->driverList[$table]->link($table,$full);
	}
	/**
	 * 释放驱动连接
	 * @return [type] [description]
	 */
	private function close()
	{
		foreach ($this->driverList as $db) {
			$db->close();
		}
	}
	/**
	 * [__destruct description]
	 */
	function __destruct()
	{
		$this->close();
	}
}