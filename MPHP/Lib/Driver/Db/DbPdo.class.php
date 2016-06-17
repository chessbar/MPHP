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
* PDO数据库操作类
*/
class DbPdo extends Db
{
	static protected $isConnect = null;//是否连接
	public $link = null;//数据库连接
	private $PDOStatement = null;//预准备
	public $affectedRows;//受影响条数
	public function connect()
	{
		if(is_null(self::$isConnect)){
			$dsn = "mysql:host=".C('DB_HOST').";dbname=".C('DB_DATABASE');
			try{
				self::$isConnect = new Pdo($dsn,C('DB_USER'),C('DB_PASSWORD'));
				self::setCharts();
			}catch(PDOException $e){
				return false;
			}
		}
		$this->link = self::$isConnect;
		return true;
	}
	//设置字符集
	private static function setCharts()
	{
		$character = C('DB_CHARSET');
		$sql = "SET character_set_connection=$character,character_set_results=$character,character_set_client=binary";
		self::$isConnect->query($sql);
	}
	//获得最后插入的ID
	public function getInsertId()
	{
		return $this->link = lastInserId();
	}
	public function getAffectedRows()
	{
		return $this->link->affectedRows;
	}
	//数据安全处理
    public function escapeString($str)
    {
        return addslashes($str);
    }
    //执行没有结果集的操作
    public function exe($sql)
    {
    	//查询参数初始化
    	$this->optInit();
    	//记录SQL语句
    	$this->recordSql($sql);
    	//释放结果
    	if(!$this->PDOStatement) $this->resultFree();
    	$this->PDOStatement = $this->link->prepare($sql);
    	//预准备失败
    	if($this->PDOStatement === false)
    	{
    		$this->error($this->link->errorCode()."\t".$this->lastSql);
    		return false;
    	}
    	$result = $this->PDOStatement->execute();
    	if($result === false){
    		$this->error($this->link->errorCode()."\t".$this->lastSql);
    		return false;
    	}else{
    		$insert_id = $this->link->lastInsertId();
    		return $insert_id ? $insert_id : TRUE;
    	}
    }
    //
    public function query($sql)
    {
    	/**
         * 缓存时间没有设置时使用配置项缓存时间
         */
        $cacheTime = is_null($this->opt['cacheTime']) ? C("CACHE_SELECT_TIME") : $this->opt['cacheTime'];
        /**
         * 查询参数初始化
         */
        $this->optInit();
        $cacheName =md5($sql . MODULE . CONTROLLER . ACTION);
        if ($cacheTime > -1) {
            $result = S($cacheName, FALSE, null, array("Driver" => "file", "dir" => APP_CACHE_PATH, "zip" => false));
            if ($result) {
                //查询参数初始化
                $this->optInit();
                return $result;
            }
        }
        //发送sql失败
        if(!$this->exe($sql)) return false;
        $list = $this->PDOStatement->fetchAll(PDO::FETCH_ASSOC);
        //受影响条数
        $this->affectedRows = count($list);
        if ($list && $cacheTime >= 0 && count($list) <= C("CACHE_SELECT_LENGTH")) {
            S($cacheName, $list, $cacheTime, array("Driver" => "file", "dir" => APP_CACHE_PATH, "zip" => false));
        }
        return empty($list) ? array() : $list;
    }
    //遍历结果集
    protected function fetch()
    {
    	$res = $this->lastQuery->fetch(PDO::FETCH_ASSOC);
    	if(!$res){
    		$this->resultFree();
    	}
    	return $res;
    }
    //释放结果集
    protected function resultFree()
    {
    	$this->PDOStatement = NULL;
    }
    //获得mysql版本
    public function getVersion()
    {
    	return $this->link->getAttribute(PDO::ATTR_SERVER_VESRION);
    }
    //开始事务处理
    public function beginTrans()
    {
    	$this->link->beginTransaction();
    }
    //提供一个事务
    public function commit()
    {
    	$this->link->commit();
    }
    //事务回滚
    public function rollback()
    {
    	$this->link->rollback();
    }
    // 释放连接资源
    public function close()
    {
        if (is_object($this->link)) {
            $this->link = NULL;
            self::$isConnect = NULL;
        }
    }

    //析构函数  释放连接资源
    public function __destruct()
    {
        $this->close();
    }
}