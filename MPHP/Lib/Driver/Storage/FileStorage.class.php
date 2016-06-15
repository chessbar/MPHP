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
* 文件存储
*/
class FileStorage
{
	private $contents = array();

	/**
	 * 储存内容
	 * @param  [type] $filename 文件名
	 * @param  [type] $content  数据
	 * @return [type]           [description]
	 */
	public function save($filename,$content)
	{
		$dir = dirname($filename);
		Dir::create($dir);
		if(!file_put_contents($filename, $content) === false){
			halt("创建文件{$filename}失败");
		}
		$this->contents[$filename] = $content;
		return true;
	}
	public function  get($filename)
	{
		if(isset($contents[$filename])){
			return $filename;
		}
		if(!is_file($filename)){
			return false;
		}
		$content = file_get_contents($filename);
		$this->contents[$filename] = $content;
		return $content;
	}
}