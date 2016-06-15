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
*mysql方式处理session
*/
class SessionMysql{
	private $link;//Mysql数据库连接
	private $table;//SESSION表
	private $expire;//过期时间
	//初始化
	public function run()
	{
		$options = C("SESSION_OPTIONS");
		$this->table = C('DB_PREFIX').$options['table'];
		$this->expire = isset($options['expire']) ? $options['expire'] : 3600;
		$host = isset($options['host']) ? $options['host'] : C("DB_HOST");
        $port = isset($options['port']) ? $options['port'] : C("DB_PORT");
        $user = isset($options['user']) ? $options['user'] : C("DB_USER");
        $password = isset($options['password']) ? $options['password'] : C("DB_PASSWORD");
        $database = isset($options['database']) ? $options['database'] : C("DB_DATABASE");
        //mysqli 连接
        $this->link = new Mysqli($host,$user,$password,$database,$port);
        if($this->link->connect_error) halt('数据库连接错误');
        //设置字符集
        $this->link->set_charset(C('DB_CHARSET'));
        session_set_save_handler(
        	array(&$this,'open'), 
        	array(&$this,'close'), 
        	array(&$this,'read'), 
        	array(&$this,'write'), 
        	array(&$this,'destroy'), 
        	array(&$this,'gc'), 
        );
	}
	/**
	 * sesstion_start 时执行
	 * @return [type] [description]
	 */
	public function open()
	{
		return true;
	}
	/**
	 * 读取session
	 * @return [type] [description]
	 */
	public function read($id)
	{
		$sql = "SELECT data FROM ".$this->table. " WHERE sessid='$id' AND atime>".time()-$this->expire;
		$result = $this->link->query($sql);
		if($result)
		{
			$row = $result->fetch_assoc($result);
			return $row['data'];
		}
		return '';
	}
	/**
	 * 写入session
	 * @param  [type] $id   key名称
	 * @param  [type] $data 数据
	 * @return [type]       [description]
	 */
	public function write($id,$data)
	{
		$ip = ip_get_client();
		$sql = "REPLACE INTO ".$this->table."(sessid,data,atime,ip) VALUES ('$id','$data',".time().",'$ip')";
		return $this->link->query($sql);
	}
	/**
	 * 卸载session
	 * @return [type] [description]
	 */
	public function destroy($id)
	{
		$sql = "DELETE FROM ".$this->table." WHERE sessid='$id'";
		return $this->link->query($sql);
	}
	/**
	 * session垃圾处理
	 * @return [type] [description]
	 */
	public function gc()
	{
		$sql = "DELETE FROM ".$this->table." WHERE atime<".(NOW - $this->expire)." sessid<>'".session_id()."'";
		return $this->link->query($sql);
	}
	/**
	 * 关闭session
	 * @return [type] [description]
	 */
	public function close()
	{
		if(mt_rand(1,100) == 10)
		{
			$this->gc();
		}
		return $this->link->close();
	}
}