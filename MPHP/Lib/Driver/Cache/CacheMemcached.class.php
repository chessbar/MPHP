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
*Memcache 缓存类
*/
class CacheMmemcache extends Cache
{
	/**
	 * memcache缓存处理对象
	 * @var object
	 */
	protected $memcacheObj;
	public function __construct($options= array())
	{
		if(!extension_loaded('memcache')){
			throw_exception('Memcache扩展加载失败');
		}
		$this->options['expire'] = isset($options['expire']) ? intval($options['expire']) : intval(C("CACHE_TIME")); //缓存时间
        $this->options['prefix'] = isset($options['prefix']) ? $options['prefix'] : ''; //缓存前缀
        $this->options['length'] = isset($options['length']) ? $options['length'] : 0; //队列长度
        $this->options['zip'] = isset($options['zip']) ? $options['zip'] : TRUE; //队列长度
        $this->options['server'] = isset($options['server']) ? $options['server'] : C('CACHE_MEMCACHE');
        $this->options['save'] = isset($options['save']) ? $options['save'] : true;
        if(!$this->isConnect){
        	$this->connectMemcache();
        	$this->isConnect = true;
        }
	}
	private function connectMemcache()
	{
		$host = $this->options['server'];
		$hostArr = is_array(current($host)) ? $host : array($host);
		$this->memcacheObj = new Memcache();
		foreach ($hostArr as $h) {
			$_host = isset($h['server']) ? $h['server'] : "127.0.0.1";
			$_port = isset($h['port']) ? $h['port'] : 11211;
			$_pconnect = isset($h['pconnect']) ? $h['pconnect'] : 1;
			$_weight = isset($h['weight']) ? $h['weight'] : 1;
			$_timeout = isset($h['timeout']) ? $h['timeout'] : 1;
			$this->memcacheObj->addServer($_host,$_port,$_pconnect,$_weight,$_timeout);
		}
	}
	/**
	 * 设置缓存
	 * @param void $name   缓存名称
	 * @param void] $value  缓存数据
	 * @param null $expire 缓存时间
	 */
	public function set($name,$value,$expire=null)
	{
		//缓存KEY
		$name = $this->options['prefix'].$name;
		//删除缓存
		if(is_null($name)){
			return $this->memcacheObj->delete($name);
		}
		//是否压缩数据
		$zip = $this->options['zip'] ? MEMCACHE_COMPRESSED : 0;
		//过期时间
		$expire = is_null($expire) ? $this->options['expire'] : $expire;
		//设置缓存
		$stat = $this->memcacheObj->set($name,$value,$zip,$expire);
		return $stat;
	}
	/**
	 * 获得缓存数据
	 * @param  string $name 缓存key
	 * @return bool
	 */
	public function get($name)
	{
		$name = $this->options['prefix'].$name;
		$data = $this->memcacheObj->get($name);
		return $data !== false ? $data : NULL;
	}
	/**
	 * 删除缓存
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public function del($name)
	{
		$name = $this->options['prefix'].$name;
		return $this->memcacheObj->delete($name);
	}
	/**
	 * 删除所有缓存数据
	 * @return [type] [description]
	 */
	public function dellAll()
	{
		return $this->memcacheObj->flush();
	}
}