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
*目录处理类
*/
final class Dir{
	/**
	 * 批量创建目录
	 * @param  [type]  $dirName 目录
	 * @param  integer $auth    权限
	 * @return [type]           [description]
	 */
	static public function create($dirName,$auth=0755)
	{
		$dirPath  = self::dirPath($dirName);
		if(is_dir($dirPath)) return true;
		$dirs = explode('/', $dirPath);
		$dir = '';
		foreach ($dirs as $v) {
			$dir .= $v.'/';
			if(is_dir($dir)) continue;
			mkdir($dir,$auth);
		}
		return is_dir($dirPath);
	}
	/**
	 * 复制目录
	 * @param  string  $olddir      原目录
	 * @param  string  $newdir      目标目录
	 * @param  boolean $strip_space 去掉空白注释
	 * @return bool
	 */
	static public function copy($olddir,$newdir,$strip_space = false)
	{
		$olddir = self::dirPath($olddir);
		$newdir = self::dirPath($newdir);
		if(!is_dir($olddir)) halt('复制失败:'.$olddir."目录不存在");
		if(!is_dir($newdir)) self::create($newdir);
		foreach (glob($olddir.'*') as $v) {
			$to = $newdir.basename($v);
			if(is_file($to)) continue;
			if(is_dir($v)){
				self::copy($v,$to,$strip_space);
			}else{
				if($strip_space){
					$data = file_get_contents($v);
					file_put_contents($to,strip_whitespace($data));
				}else{
					copy($v,$to);
				}
				chmod($to,0777);
			}
		}
		return true;
	}
	static private function dirPath($dirName)
	{
		$dirName = str_ireplace('\\','/',$dirName);
		return substr($dirName,-1) == '/' ? $dirName : $dirName.'/';
	}
	/**
	 * 目录下创建安全文件
	 * @param  [type]  $dirName   创造目录
	 * @param  boolean $recursive 是否递归创建
	 * @return [type]             [description]
	 */
	static public function safeFile($dirName,$recursive = false)
	{
		if(!is_dir($dirName)) return false;
		$file = MPHP_TPL_PATH.'index.html';
		$dirPath = self::dirPath($dirName);
		//创建安全文件
		if(!is_file($dirPath.'index.html')){
			copy($file,$dirPath.'index.html');
		}
		//操作子目录
		if($recursive){
			foreach (glob($driPath.'*') as $d) {
				is_dir($d) && self::safeFile($d,$recursive);
			}
		}
	}
}