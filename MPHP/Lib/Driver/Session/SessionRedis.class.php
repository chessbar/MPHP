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
*基于Redis的session处理引擎
*/
class SessionRedis
{
	private $redis;//连接对象
	private $card='m';
	public function run()
	{
		$options = C('SESSION_OPTIONS');
		$this->redis = new Redis();
		$this->redis->connect($options['host'],$options['port'],2.5);
		if(!empty($options['password'])){
			$this->redis->auth($options['password']);
		}
		$this->redis->select((int) $options['db']);
		session_set_save_handler(
            array(&$this, "open"),
            array(&$this, "close"),
            array(&$this, "read"),
            array(&$this, "write"),
            array(&$this, "destroy"),
            array(&$this, "gc")
        );
	}
	function open() {
        return true;
    }
    /**
     * 获取缓存数据
     * @return [type] [description]
     */
    function read($sid)
    {
    	$data = $this->redis->get($sid);
    	if($data)
    	{
    		$values = explode('|#|',$data);
    		return $values[0] === $this->card ? $values[1] : '';
    	}
    	return $data;
    }
    function write($id,$data)
    {
    	return $this->redis->set($id,$this->card.'|#|'.$data);
    }
    function destory($sid)
    {
    	return $this->redis->delete($id);
    }
    /**
     * 垃圾回收
     * @return boolean
     */
    function gc() {
        return true;
    }
}