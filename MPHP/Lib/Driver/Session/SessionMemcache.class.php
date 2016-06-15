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
*基于MEMCACHE的session处理引擎
*/
class SessionMemcache{
	/**
	 * memcache连接对象
	 * @var [type]
	 */
	private $memcache;
	public function run()
	{
		$options = C('SESSION_OPTIONS');
		$this->memcache = new Memcache();
		$this->memcache->connect($options['host'],$options['port'],2.5);
		session_set_save_handler(
            array(&$this, "open"),
            array(&$this, "close"),
            array(&$this, "read"),
            array(&$this, "write"),
            array(&$this, "destroy"),
            array(&$this, "gc")
        );
	}
	public function open()
	{
		return true;
	}
	public function read($id)
	{
		return $this->memcache->get($id);
	}
	public function write($id,$data)
	{
		return $this->memcache->set($id,$data);
	}
	public function destroy($id)
	{
		return $this->memecache->delete($id);
	}
	public function gc()
	{
		return true;
	}
	public function close()
	{
		return true;
	}
}