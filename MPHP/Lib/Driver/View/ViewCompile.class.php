<?php
// .-----------------------------------------------------------------------------------
// |  Software: [MPHP framework]
// |   Version: 2016.01
// |-----------------------------------------------------------------------------------
// |    Author: M <1006760526@qq.com>
// |-----------------------------------------------------------------------------------
// |   License: http://www.apache.org/licenses/LICENSE-2.0
// '-----------------------------------------------------------------------------------
import('MPHP.Lib.Driver.View.Tag');
/**
*@version
*M模板引擎编译处理类
*/
class ViewCompile
{
	//视图对象
	private $view;
	//模板编译内容
	private $content;
	//别名函数
	private $aliasFunction = array(
			'default'=>'_default',
		);
	//不解析内容
	private $literal = array();
	//运行编译
	public function run(&$view=null)
	{
		//MView对象
		$this->view = $view;
		//模板内容
		$this->content = file_get_contents($this->view->tplFile);
		//获得不解析内容
		$this->getNoParseContent();
		//加载标签类 标签类由系统标签与用户扩展标签构成
		$this->parseTag();
		//解析变量
		$this->parseVar();
		//替换所有常量
		$this->parseUrlConst();
		//将不解析的内容还原
		$this->replaceNoParseContent();
		//编译内容
		$this->content = "<?php if(!defined('MPHP_PATH'))exit;C('SHOW_NOTICE',FALSE);?>\n".$this->content;
		//创建编译目录 与安全文件
		Dir::create(dirname($this->view->compileFile));
		Dir::safeFile(dirname($this->view->compileFile));
		//存储编译文件
		file_put_contents($this->view->compileFile,$this->content);
	}
	//获得不解析内容
	private function getNoParseContent()
	{
		$status = preg_match_all('@<literal>(.*?)<\/literal>@isU',$this->content,$ifno,PREG_SET_ORDER);
		if($status){
			foreach ($info as $n => $content) {
				$this->literal[$n] = $content[1];
				$this->content = str_replace($content[0],"###".$n."###",$this->content);

			}
		}
	}
	private function parseTag()
	{
		//所有标签类库
		$tagClass = array();
		//框架标签库
		if(import('MPHP.Lib.Driver.ViewViewTag')){
			$tagClass[] = 'ViewTag';
		}
		//用户自定义标签
		$tags = C('TPL_TAGS');
		//导入用户自定义标签库
		if(!empty($tags) && is_array($tags)){
			//压入标签类
			foreach ($tags as $file) {
				if(import($file) || import($file,MODULE_TAG_PATH) || import($file,APP_TAG_PATH)){
					//类名
					$file = str_replace('.','/',$file);
					$class = basename($file);
					//合法标签库必须有Tag 属性 且继承 Tag类
					if(class_exists($calss,false) && property_exists($calss, 'Tag') && get_parent_class($class) == 'Tag'){
						$tagClass[]=$class;
					}
				}
			}
		}
		//解析标签类
		foreach ($tagClass as $class) {
			//标签类对象
			$obj = new $class();
			//标签类中的标签方法
			foreach ($obj->Tag as $tag => $option) {
				//合法标签必须满足 定义了 block 与level值
				if(!isset($option['block']) || !isset($option['level']))
				{
					continue;
				}
			}
			//解析标签
			for ($i=0; $i < $option['level']; $i++) { 
				if(!$obj->parseTag($tag,$this->content,$this->view))
				{
					break;
				}
			}
		}
	}
	/**
	 * 解析变量
	 * @return [type] [description]
	 */
	private function parseVar()
	{
		$preg = '#\{(\$[\w\.\[\]\'"]+)?(?:\|(.*))?\}#isU';
		$status = preg_match_all($preg,$this->content,$info,PREG_SET_ORDER);
		if($status){
			foreach ($info as $d) {
				//变量
				$var = '';
				if(!empty($d[1])){
					$data = explode('.',$d[1]);
					foreach ($data as $n => $m) {
						if($n===0){
							$var .= $m;
						}else{
							$var .= '[\''.$m.'\']';
						}
					}
				}
				//函数
				if(!empty($d[2])){
					$functions = explode('|',$d[2]);
					foreach ($functions as $func) {
						//函数解析 如 substr 0,2
						$tmp = explode(':',$func,2);
						//存在别名函数时使用别名函数
						if(isset($this->aliasFunction[$tmp[0]])){
							$name = $this->aliasFunction[$tmp[0]];
						}else{
							$name = $tmp[0];
						}
						//参数
						$arg = empty($tmp[1]) ? '' : $tmp[1]; 
						//变量加入到参数中
						//参数中用@@时 将变量替换@@
						if(strstr($arg,'@@')){
							$var = str_replace('@@',$var,$arg);
						}else{
							$var = $var.','.$arg;
						}
						$var = rtrim($var,',');
						$var = $name.'('.$var.')';
					}
				}
				if(!empty($var)){
					$replace = '<?php echo '.$var.';?>';
					$this->content = str_replace($d[0],$replace,$this->content);
				}
			}
		}
	}
	/**
	 * 替换URL地址常量
	 * @return [type] [description]
	 */
	private function parseUrlConst()
	{
		$const = get_defined_constants(true);
		foreach ($const['user'] as $name => $value) {
			if(strstr($name,'__')){
				$this->content = str_replace($name,$value,$this->content);
			}
		}
	}
	/**
	 * 将不解析的内容替换回来
	 * @return [type] [description]
	 */
	private function replaceNoParseContent()
	{
		foreach ($this->literal as $n => $content) {
			$this->content = str_replace('###'.$n.'###',$content,$this->content);
		}
	}
}