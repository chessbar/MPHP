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
* Redis缓存类
*/
class CacheRedis extends Cache
{
	/**
	 * 缓存对象
	 * @var array
	 */
	protected $redisObj = array();
	public function __construct($options = array())
	{
		//检测Redis扩展
        if (!extension_loaded('Redis')) {
            throw_exception("Redis扩展加载失败");
        }
        $this->options['expire'] = isset($options['expire']) ? intval($options['expire']) : intval(C("CACHE_TIME")); //缓存时间
        $this->options['prefix'] = isset($options['prefix']) ? $options['prefix'] : ''; //缓存前缀
        $this->options['length'] = isset($options['length']) ? $options['length'] : 0; //队列长度
        $this->options['zip'] = isset($options['zip']) ? $options['zip'] : TRUE; //队列长度
        $this->options['server'] = isset($options['server']) ? $options['server'] : C("CACHE_REDIS");
        $this->options['save'] = isset($options['save']) ? $options['save'] : true; 
        if(!$this->isConnect){
        	$this->connectRedis();
        	$this->isConnect = true;
        }
	}
	/**
	 * 连接Redis
	 * @return [type] [description]
	 */
	private function connnectRedis()
	{
		$host = $this->options['server'];
		$hostArr = is_array(current($host)) ? $host : array($host);
		foreach ($hostArr as $h) {
			$_host = isset($h['host']) ? $h['host'] : "127.0.0.1"; //主机
            $_port = isset($h['port']) ? $h['port'] : 6379; //端口
            $_pconnect = isset($h['pconnect']) ? $h['pconnect'] : 1; //持久连接
            $_password = isset($h['password']) ? $h['password'] : null; //密码
            $_timeout = isset($h['timeout']) ? $h['timeout'] : 1; //连接超时
            $_db = isset($h['Db']) ? $h['Db'] : 0; //数据库
            try{
            	$this->redisObj[$_host] = new Redis();
            	$linkFunc = $_pconnect ? 'pconnect' : 'connect';//是否持久连接
            	$this->redisObj[$_host]->$linkFunc($_host,$port,$timeout);
            	if($_password){
            		if(!$this->redisObj[$_host]->auth($_password)){
            			throw new Exception();
            		}
            	}
            }catch(Exception $e)
            {
            	if(DEBUG){
            		throw_exception('Redis{$_host} connect fails');
            	}
            	Log::write($->getMessage());
            }
		}
	}
	/**
	 * 设置缓存
	 * @param [type] $name   [description]
	 * @param [type] $value  [description]
	 * @param [type] $expire [description]
	 */
	public function set($name,$value,$expire=null)
	{
		$name = $this->options['prefix'] . $name;
		if(is_null($value)){
			return $this->del($name);
		}
		foreach ($this->redisOnj as $obj) {
			//设置缓存
			$data = $obj->set($name,$value);
			//设置过期时间
			$expire = is_null($expire) ? $this->options['expire'] : (int)$expire;
			if($expire) $obj->expire($name,$value);
		}
		//缓存计数
		if($this->option['save'] === true){
			if($data === false){
				N('m_cache_set_misses',1);
			}else{
				N('m_cache_set_hits',1);
			}
		}
		return $data;
	}
	/**
	 * 获取缓存数据
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public function get($name)
	{
		$name = $this->options['prefix'] . $name;
		$objs = $this->redisObj;
		$count = count($objs);
		for ($i=0; $i < $count; $i++) { 
			$_index = array_rand($objs);
			$data = $onj[$_index]->get($name);
			if($data !== false){
				break;
			}
			unset($objs[$_index]);
		}
		return $data !== false ? $data : null;
	}
	public function del($name)
	{
		$name = $this->options['prefix'] . $name;
		foreach ($this->redisObj as $obj) {
			$obj->del($name);
		}
		return true;
	}
	public function delAll()
	{
		return $this->redisObj->flushall();
	}
}