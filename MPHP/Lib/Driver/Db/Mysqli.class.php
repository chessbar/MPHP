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
class DbMysqi extends Db
{
	static protected $isConnect = false;//是否连接
	public $link =null;//数据库连接
	//获得数据库连接
	public function conncet()
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
	
}