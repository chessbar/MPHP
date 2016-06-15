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
* MPHP模板引擎
*/
final class ViewM
{
	/**
	 *  模板变量
	 * @var array
	 */
	public $var = array();
	/**
	 * 系统常量 __WEB__
	 * @var array
	 */
	public $const = array();
	/**
	 * 模板文件
	 * @var null
	 */
	public $tplFile = null;
	/**
	 * 编译文件
	 * @var null
	 */
	public $compileFile = null;
	public function display($tplFile=null,$cacheTime=-1,$cachePath=null,$contentType='text/html',$show = true)
	{
		//缓存文件名
		$cacheName = md5($_SERVER['REQUEST_URI']);
		//缓存时间
		$cacheTime = is_numeric($cacheTime) ? $cacheTime : intval(C('TPL_CACHE_TIME'));
		//缓存路径
		$cachePath = $cachePath ? $cachePath : APP_CACHE_PATH;
		//内容
		$content = null;
		if($cacheTime >= 0){
			$conent = S($cacheName,false,array('dir'=>$cachePath,'zip'=>false,'Driver'=>'file'));
		}
		//缓存失效
		if(!$content){
			//全局变量定义 使用模板 {$m.get.xxx}
			$this->vars['m']['get'] = &$_GET;
			$this->vars['m']['post'] = &$_POST;
			$this->vars['m']['request'] = &$_REQUEST;
			$this->vars['m']['cookie'] = &$_COOKIE;
			$this->vars['m']['session'] = &$_SESSION;
			$this->vars['m']['server'] = &$SERVER;
			$this->vars['m']['congfig'] =C();
			$this->vars['m']['language'] = L();
			$this->vars['m']['const'] = get_defined_constants();
			//获得模板文件
			$this->tplFile = $this->getTempalteFile($tplFile);
			if(!$this->tplFile) return;
			//编译文件
			$this->compileFile = APP_COMPILE_PATH.MODULE.'/'.CONTROLLER.'/'.ACTION.'_'.substr(md5($this->tplFile),0,8).'.php';
			// 记录模板编译文件
			if(DEBUG){
				Debug::$tpl[] = array(basename($this->tplFile),$this->compileFile);
			}
			//编译文件失效
			if($this->compileInvalid($tplFile)){
				$this->compile();
			}
			//加载全局变量
			if(!empty($this->vars)){
				extract($this->vars);
			}
			ob_start();
			include($this->compileFile);
			$content = ob_get_clean();
			//创建缓存
			if($cacheTime >=0){
				//写入缓存
				S($cacheName,$content,$cacheTime,array('dir'=>$cachePath,'zip'=>false,'Driver'=>'File'));
			}
		}
		if($show)
		{
			$charset = C('TPL_CHARSET') ? C('TPL_CHARSET') : 'UTF-8';
			if(!headers_sent()){
				header("Content-type:".$contentType.";charset=".$charset);
			}
			echo $content;
		}else{
			return $content;
		}
	}
	/**
	 * 获得视图内容
	 * @param  [type]  $tplFile     [description]
	 * @param  integer $cacheTime   [description]
	 * @param  [type]  $cachePath   [description]
	 * @param  string  $contentType [description]
	 * @return [type]               [description]
	 */
	public function fetch($tplFile=null,$cacheTime=-1,$cachePath=null,$contentType='text/html')
	{
		return $this->display($tplFile,$cacheTime,$cachePath,$contentType,false);
	}
	/**
	 * 缓存目录
	 * @param  [type]  $cachePath [description]
	 * @return boolean            [description]
	 */
	public function isCache($cachePath=null)
	{
		$cachePath = $cachePath ? $cachePath : APP_CACHE_PATH;
		$cacheName = md5($_SERVER['REQUEST_URI']);
		return S($cacheNae,false,null,array("dir" => $cachePath, "Driver" => "File")) ? true : false;
	}
	/**
	 * 向模板中传递变量
	 * @param  string|array $var   变量名
	 * @param  mixed $value 变量值
	 */
	public function assign($var,$value)
	{
		if(is_array($var)){
			foreach ($var as $k => $v) {
				if(is_string($k)){
					$this->vars[$k] = $v;
				}
			}
		}else{
			$this->vars[$var] = $value;
		}
	}
	/**
	 * 获得模板文件
	 * @param  [type] $file [description]
	 * @return [type]       [description]
	 */
	private function getTempalteFile($file)
	{
		if(is_null($file)){
			$file = CONTROLLER_VIEW_PATH.ACTION;
		}
		if(!is_file($file)){
			if(!strstr($file,'/')){
				//没有路径时使用控制器视图目录
				$file = CONTROLLER_VIEW_PATH.$file;
			}
		}
		//添加后缀
		if(!preg_match('/\.[a-z]$/i',$file)){
			$file .= C('TPL_FIX');
		}
		//模板文件检测
		if(is_file($file)){
			return $file;
		}else{
			DEBUG && halt("模板不存在$file");
			return false;
		}
	}
	/**
	 * 编译是否失效
	 * @return bool true失效
	 */
	private function compileInvalid()
	{
		$tplFile = $this->tplFile;
		$compileFile = $this->compileFile;
		return DEBUG || !file_exists($tplFile) || filemtime($tplFile) > filemtime($compileFile);
	}
	public function compile()
	{
		/**
		 * 编译模板是否失效
		 */
		if(!$this->compileInvalid()) return;
		$compileObj = new ViewCompile();
		$compileObj->run($this);
	}
}