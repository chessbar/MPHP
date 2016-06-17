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
* 缓存处理类
* 缓存驱动类均继承此类
* 
*/
class Cache
{
	protected $isConnect = false;//连接状态
	protected $options = array();//参数
	static public function init($factory)
	{
		return CacheFactory::factory($factory);//获得缓存操作对象
	}
	/**
	 * 魔术方法获得缓存
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public function __get($name)
	{
		return $this->get($name);
	}
	/**
	 * 设置缓存
	 * @param [type] $name  [description]
	 * @param [type] $value [description]
	 */
	public function __set($name,$value)
	{
		return $this->set($name,$value);
	}
	/**
	 * 访问私有方法执行
	 * @param  [type] $method [description]
	 * @param  [type] $args   [description]
	 * @return [type]         [description]
	 */
	public function __call($method,$args)
	{
		if(method_exists($this,$method))
		{
			return call_user_func_array(array($this,$method),$args);
		}
		return false;
	}
	/**
	 * 删除缓存
	 * @param [type] $name 缓存key
	 */
	public function __unset($name)
	{
		return $this->del($name);
	}
	/**
	 * 获取或设置属性
	 * @param  [type] $name  缓存配置名称
	 * @param  [type] $value 设置缓存的值
	 * @return [type]        [description]
	 */
	public function options($name,$value = null)
	{
		if(!is_null($value)){
			$this->options[$name] = $value;
			return true;
		}else{
			if(isset($this->options[$name])){
				return $this->options[$name];
			}else{
				return null;
			}
		}
	}
	/**
	 * 缓存队列
	 * 缓存队列即设置可以缓存的最大数值，以先进先删除的原则处理消息队列
	 * @param  [type] $name key名称
	 * @return mixed
	 */
	protected function queue($name)
	{
		static $drivers = array('file'=>'F');
		$driver = isset($this->options['Driver']) ? $this->options['Driver'] : 'file';
		$_queue = $drivers[$driver][0]('mphp_queue');
		if(!$queue){
			$_queue = array();
		}
		$mphp_queue = array_unique($_queue);
		//超过队列最大值
		if(count($mphp_queue) > $this->options['length']){
			$gc = array_shift($mphp_queue);
			if($gc) $this->del($gc);
		}
		return $drivers[$driver][0]('mphp_queue',$mphp_queue);
	}
	/**
	 * @param  [type]  $type 记录类型 1写入 2 读取
	 * @param  integer $stat 状态 0失败 1成功
	 */
	protected function record($type,$stat = 1)
	{
		if(!DEBUG && !C('SHOW_CACHE')) return;
		if($type ==1){
			$stat ? Debug::$cache['write_s']++ : Debug::$cache['write_f']++;
		}else{
			$stat ? Debug::$cache['read_s']++ : Debug::$cache['read_f']++;
		}
	}
}