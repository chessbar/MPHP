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
* mysqli数据库驱动
*/
class DbMysqli extends Db
{
	static protected $isConnect = false;//是否连接
	public $link =null;//数据库连接
	//获得数据库连接
	public function connect()
	{
		if(!self::$isConnect){
			$link = new Mysqli(C('DB_HOST'),C('DB_USER'),C('DB_PASSWORD'),C('DB_DATABASE'),C('DB_PORT'));
			if(mysqli_connect_errno()){
				$this->error(mysqli_connect_error());
				return false;
			}
			self::$isConnect = $link;
			self::setCharset();
		}
		$this->link = self::$isConnect;
		return true;
	}
	/**
	 * 设置字符集
	 */
	private function setCharset()
	{
		self::$isConnect->set_charset(C('DB_CHARSET'));
	}
	//获得最后插入的ID
	public function getInsertId()
	{
		return $this->link->insert_id;
	}
	//获得受影响行数
	public function getAffectedRows()
	{
		return $this->link->affected_rows;
	}
	//遍历结果集
	protected function fetch()
	{
		$res = $this->lastQuery->fetch_assoc();
		if(!$res){
			$this->resultFree();
		}
		return $res;
	}
	//数据安全处理
	public function escapeString($str)
	{
		if($this->link)
		{
			return $this->link->real_escape_string($str);
		}else{
			return addslashes($str);
		}
	}
	//执行没有结果集的操作
	public function exe($sql)
	{
		//查询参数初始化
		$this->optInit();
		//记录sql语句
		$this->recordSql($sql);
		$this->lastQuery = $this->link->query($sql);
		if($this->lastQuery){
			$insert_id = $this->link->insert_id;
			return $insert_id ? $insert_id : true;
		}else{
			$this->error($this->link->error."\t".$sql);
			return false;
		}
	}
	//执行有结果集操作
	public function query($sql)
	{
		//查询缓存 缓存没有设置时使用系统配置的缓存时间
		$cacheTime = is_null($this->opt['cacheTime']) ? C('CACHE_SELECT_TIME') : $this->opt['cacheTime'];
		//查询参数初始化
		$this->optInit();
		$cacheName = md5($sql.MODULE.CONTROLLER.ACTION);
		//读取缓存
		if($cacheTime>-1)
		{
			$result = S($cacheName,FALSE,NULL,array('Driver'=>'file','dir'=>APP_CACHE_PATH,'zip'=>false));
			if($result) return $result;
		}
		if(!$this->exe($sql)) return false;
		$list = array();
		while(($res = $this->fetch()) !=false){
			$list[] = $res;
		}
		//记录缓存
		if($list && $cacheTime >=0 && count($list) <= C('CACHE_SELECT_LENGTH')){
			S($cacheName,$list,$cacheTime,array('Driver'=>'file','dir'=>APP_CACHE_PATH,'zip'=>false));
		}
		return empty($list) ? array() : $list;
	}
	//释放结果集
	protected function resultFree()
	{
		if(isset($this->lastQuery)){
			$this->lastQuery->close();
		}
	}
	public function getVersion()
	{
		return $this->link->server_info;
	}
	//自动提交模式
	public function beginTrans()
	{
		$this->link->autocommit(0);
	}
	//提供一个事务
	public function commit()
	{
		$this->link->commit();
		$this->link->autocommit(1);
	}
	//回滚事务
	public function rollback()
	{
		$this->link->rollback();
		$this->link->autocommit(1);
	}
	//释放资源
	public function close()
	{
		if(self::$isConnect){
			$this->link->close();
			self::$isConnect = false;
			$this->link = null;
		}
	}
	public function __destruct()
	{
		$this->close();
	}
}