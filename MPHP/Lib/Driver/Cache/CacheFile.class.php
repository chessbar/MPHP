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
*文件缓存类
*
*/
class CacheFile extends Cache
{
	/**
	 * 构造函数
	 * @param array $options [description]
	 */
	public function __construct($options = array())
	{
		$this->options['dir'] = isset($options['dir']) ? rtrim($options['dir'],'/') : rtrim(APP_CACHE_PATH,'/');//缓存目录
		$this->option['expire'] = isset($options['expire']) ? intval($options['expire']) : C('CACHE_TIME');//缓存时间
		$this->options['prefix'] = isset($options['prefix']) ? $options['prefix'] : '';
		$this->options['length'] = isset($options['length']) ? $options['length'] : '';//队列长度
		$this->options['zip'] = isset($options['zip']) ? $options['zip'] : false;//压缩数据
		$this->options['save'] = isset($options['save']) ? $options['save'] : true;//记录缓存命中率
		$this->isConnect = is_dir($this->options['dir']) && is_writeable($this->options['dir']);
		if(!$this->isConnect){
			$this->createDir();
		}
	}
	/**
	 * 创建目录
	 * @return [type] [description]
	 */
	private function createDir()
	{
		$this->isConnect = dir_create($this->options['dir']);
	}
	/**
	 * 获得缓存文件
	 * @param  [type] $name 缓存时间KEY
	 * @return string
	 */
	public function getCacheFile($name)
	{
		return $this->options['dir'].'/'.$this->options['prefix'].$name.".php";
	}
	/**
	 * 设置缓存
	 * @param string $name   缓存KEY
	 * @param void $data   缓存数据
	 * @param void $expire 缓存时间
	 * @return bool
	 */
	public function set($name,$data,$expire = null)
	{
		$cacheFile = $this->getCacheFile($name);
		//删除缓存数据 $this->set($name,null);
		if(is_null($data)){
			if(is_file($cacheFile)){
				return unlink($cacheFile);
			}else{
				return true;
			}
		}
		//缓存时间
		$expire = sprintf("%010d",!is_null($expire) ? (int)$expire : $this->options['expire']);
		//缓存时间小于0 为不缓存
		if($expire < 0) return false;
		//缓存目录失效
		if(!$this->isConnect){
			$this->createDir();
		}
		$data = serialize($data);
		//压缩数据
		if($this->options['zip'] && function_exists('gzcompress')){
			$data = gzcompress($data,6);
		}
		$data = "<?php\n//".$expire.$data."\n?>";
		$stat = file_put_contents($cacheFile,$data);
		if($stat){
			if($this->options['length'] >0){
				//队列处理
				$this->queue($name);
			}
			$this->record(1,1);
			return true;
		}else{
			$this->record(1,0);
			return false;
		}
	}
	/**
	 * 获取缓存
	 * @param  string $name 缓存KEY
	 * @return bool|mixed|null
	 */
	public function get($name)
	{
		$cacheFile = $this->getCacheFile($name);
		//缓存文件不存在
		if(!is_file($cacheFile)){
			$this->record(2,0);
			return null;
		}
		$content = file_get_contents($cacheFile);
		if(!$content){
			$this->record(2,0);
			return null;
		}
		$expire = intval(substr($content,8,10));
		//文件修改时间
		$filemtime = filemtime($cacheFile);
		//缓存失效处理
		if($expire>0 && $filemtime+$expire < time()){
			unlink($cacheFile);
			$this->record(2,0);
			return false;
		}
		$data = substr($content,18,-3);
		if($this->options['zip'] && function_exists('gzuncompress')){
			$data = gzuncompress($data);
		}
		$this->record(2,1);
		return unserialize($data);
	}
	/**
	 * 删除缓存
	 * @param  string $name 缓存key
	 * @return bool
	 */
	public function del($name)
	{
		$cacheFile = $this->getCacheFile($name);
		return is_file($cacheFile) && unlink($cacheFile);
	}
	/**
	 * 删除所有缓存
	 * @param  int $time 缓存时间
	 * @return bool
	 */
	public function delAll($time = null)
	{
		foreach (glob($this->options['dir'].'/*.*') as $file) {
			if(is_file($file)){
				if($time){
					(filemtime($file) + $time <time()) && unlink($file);
				}else{
					unlink($file);
				}
			}
		}
		return true;
	}
}