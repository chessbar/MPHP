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
*钩子
*/
abstract class Hook
{
	//钩子
	static private $hook = array();
	/**
	 * 添加钩子时间
	 * @param [type] $hook   钩子事件
	 * @param [type] $action 钩子方法
	 */
	static public function add($hook,$action)
	{
		if(!isset(self::$hook[$hook])){
			self::$hook[$hook] = array();
		}
		if(is_array($action)){
			self::$hook[$hook] = array_merge(self::$hook[$hook],$action);
		}else{
			self::$hook[$hook][] = $action;
		}
	}
	/**
	 * 获得钩子信息
	 * @param  string $hook 钩子名称
	 * @return array
	 */
	static public function get($hook='')
	{
		if(empty($hook)){
			return self::$hook;
		}else{
			return self::$hook[$hook];
		}
	}
	/**
	 * 批量导入钩子
	 * @param  [type]  $data      钩子数据
	 * @param  boolean $recursive 是否递归合并
	 * @return [type]             [description]
	 */
	static public function import($data,$recursive = true)
	{
		if($recursive == false){
			self::$hook = array_merge(self::$hook,$data);
		}else{
			foreach ($data as $hook => $value) {
				if(!isset(self::$hook[$hook])) self::$hook[$hook];
				if(isset($value['_overflow'])){
					unset($value['_overflow']);
					self::$hook[$hook] = $value;
				}else{
					self::$hook[$hook] = array_merge(self::$hook[$hook],$value);
				}
			}
		}
	}
	/**
	 * 监听钩子
	 * @param  [type] $hook   钩子名
	 * @param  [type] &$param 参数
	 * @return [type]         [description]
	 */
	static public function listen($hook,&$param = null)
	{
		if(!isset(self::$hook[$hook])) return false;
		foreach (self::$hook[$hook] as $name) {
			if(false == self::exe($name,$hook,$param)) return;
		}
	}
	/**
	 * 执行钩子
	 * @param  [type] $name   钩子名
	 * @param  [type] $hook   钩子名称
	 * @param  [type] &$param 参数
	 * @return bool
	 */
	static public function exe($name,$hook,&$param=null)
	{
		if(substr($name,-4) == 'Hook'){//钩子
			$action = 'run';
		}else{//插件
			require_cache(APP_ADDON_PATH.$name.'/'.$name.'Addon.class.php');
			$name = $name.'Addon';
			if(!class_exists($name,false)) return false;
		}
		$obj = new $name;
		if(method_exists($obj,$action)) $obj->$action($param);
		return true;
	}
}