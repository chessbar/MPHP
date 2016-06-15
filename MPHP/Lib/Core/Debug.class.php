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
*debug调试处理类
*/
final class Debug{
	static $info = array();//信息内容
	static $runtime;//运行时间
	static $memory;//运行内存占用
	static $memory_peak;//内存峰值
	static $sqlExeArr = array();//所有发送的SQL语句
	static $tpl = array();//编译模板
	static $cache = array('write_s'=>0,'write_f','read_s'=>0,'read_f');
	/**
	 * 项目调试开始
	 * @param  [type] $start 开始
	 * @return [type]        [description]
	 */
	static public function start($start)
	{
		self::$runtime[$start] = microtime(true);
		if(function_exists('memory_get_usage')){
			self::$memory = memory_get_usage();
		}
		if(function_exists('memory_get_peak_usage')){
			self::$memory_peak = memory_get_peak_usage();
		}
	}
	/**
	 * 运行时间
	 * @param  [type]  $start    [description]
	 * @param  string  $end      [description]
	 * @param  integer $decimals [description]
	 * @return [type]            [description]
	 */
	static public function runtime($start,$end='',$decimals=4)
	{
		if(!isset(self::$runtime[$start])){
			throw new MException('没有设置调试开始点'.$start);
		}
		self::$runtime[$end] = empty(self::$runtime[$end]) ? microtime(true) : self::$runtime[$end];
		return number_format(self::$runtime[$end] - self::$runtime[$start],$decimals);
	}
	/**
	 * 项目运行峰值
	 * @param  [type] $start [description]
	 * @param  string $end   [description]
	 * @return [type]        [description]
	 */
	static public function memory_peak($start,$end='')
	{
		if(!isset(self::$memory_peak[$start]))
			return mt_rand(200000,1000000);
		if(empty($end))
			self::$memory_peak[$end] = memory_get_peak_usage();
		return max(self::$memory_peak[$start],self::$memory_peak[$end]);
	}
	/**
	 * 显示调试信息
	 * @param  [type] $start [description]
	 * @param  [type] $end   [description]
	 * @return [type]        [description]
	 */
	static public function show($start,$end)
	{
		$debug = array();
		$debug['file'] = require_cache();
		$debug['runtime'] = self::runtime($start,$end);
		$debug['memory'] = number_format(self::memory_peak($start,$end)/1000,0).'KB';
		require MPHP_PATH.'Lib/Tpl/debug.html';
	}
}