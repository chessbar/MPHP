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
* 控制器基类
*/
abstract class Controller
{
	/**
	 * 模板视图对象
	 * @var null
	 */
	protected $view = null;
	/**
	 * 事件参数
	 * @var array
	 */
	protected $options = array();

	public function __construct()
	{
		Hook::listen('CONTROLLER_START',$this->options);
		$this->view = ViewFactory::factory();
		//
		if(method_exists($this,'__init'))
		{
			$this->__init();
		}
	}
	/**
	 * 执行不存在的函数时自动调用
	 * @param  [type] $action [description]
	 * @param  [type] $args   [description]
	 * @return [type]         [description]
	 */
	public function __call($action,$args)
	{
		if(strcasecmp($action,ACTION) ==0){
			if(method_exists($this,'__empty')){
				$this->__empty();
			}else{
				_404('控制器中方法不存在'.$action);
			}
		}
	}
	public function __set($name,$value)
	{
		$this->assign($name,$value);
	}
	/**
	 * [display description]
	 * @param  [type]  $tplFile     [description]
	 * @param  integer $cacheTime   [description]
	 * @param  [type]  $cachePath   [description]
	 * @param  string  $contentType [description]
	 * @param  boolean $show        [description]
	 * @return [type]               [description]
	 */
	protected function display($tplFile=null,$cacheTime = -1,$cachePath = null,$contentType = 'text/html',$show = true)
	{
		Hook::listen('VIEW_START');
		//执行视图对象同名diplay方法
		$status = $this->view->display($tplFile,$cacheTime,$cachePath,$contentType,$show);
		Hook::listen('VIEW_END');
		return $status;
	}
	/**
     * 获得视图显示内容 用于生成静态或生成缓存文件
     * @param string $tplFile 模板文件
     * @param null $cacheTime 缓存时间
     * @param string $cachePath 缓存目录
     * @param string $contentType 文件类型
     * @param string $charset 字符集
     * @param bool $show 是否显示
     * @return mixed
     */
    protected function fetch($tplFile = null, $cacheTime = null, $cachePath = null, $contentType = "text/html")
    {
        return $this->view->fetch($tplFile, $cacheTime, $cachePath, $contentType);
    }
    /**
     * 缓存是否过期
     * @param  [type]  $cachePath [description]
     * @return boolean            [description]
     */
    protected function isCache($cachePath)
    {
    	$args = func_get_args();
    	return call_user_func_array(array($this->view,'isCache'),$args);
    }	
    /**
     * 分配变量
     * @access protected
     * @param mixed $name 变量名
     * @param mixed $value 变量值
     * @return mixed
     */
    protected function assign($name, $value = null)
    {
        return $this->view->assign($name, $value);
    }
    /**
     * 错误提示
     * @param  string  $message [description]
     * @param  [type]  $url     [description]
     * @param  integer $time    [description]
     * @param  [type]  $tpl     [description]
     * @return [type]           [description]
     */
    protected function error($message='出错了',$url=NULL,$time=2,$tpl=null)
    {
    	if(IS_AJAX){
    		$this->ajax(array('status'=>0,'message'=>$message));
    	}else{
    		$url = $url ? "window.location.href='".U($url)."'" : "window.location.href='".__HISTORY__."'";
    		$tpl = $tpl ? $tpl : (strstr(C('TPL_ERROR'),'/')) ? C('TPL_ERROR') : MODULE_PUBLIC_PATH.C('TPL_ERROR');
    		$this->assign(array("message" => $message, 'url' => $url, 'time' => $time));
            $this->display($tpl);
    	}
    	exit;
    }
    /**
     * 成功提示
     * @param  string  $message [description]
     * @param  [type]  $url     [description]
     * @param  integer $time    [description]
     * @param  [type]  $tpl     [description]
     * @return [type]           [description]
     */
    protected function success($message='操作成功',$url=NULL,$time=2,$tpl=null)
    {
    	if(IS_AJAX){
    		$this->ajax(array('status'=>1,'message'=>$message));
    	}else{
    		$url = $url ? "window.location.href='".U($url)."'" : "window.location.href='".__HISTORY__."'";
    		$tpl = $tpl ? $tpl : (strstr(C('TPL_SUCCESS'),'/')) ? C('TPL_SUCCESS') : MODULE_PUBLIC_PATH.C('TPL_SUCCESS');
    		$this->assign(array("message" => $message, 'url' => $url, 'time' => $time));
            $this->display($tpl);
    	}
    	exit;
    }
    /**
     * 生成静态文件
     * @param  string $htmlFile 文件名
     * @param  string $htmlPath 目录
     * @param  string $template 模板
     */
    public function createHtml($htmlFile,$htmlPath,$template)
    {
    	$content = $this->fetch($template);
    	$file = $htmlPath.$htmlFile;
    	$Storage = Storage::init();
    	return $Storage->save($file,$content);
    }
    /**
     * ajax输出
     * @param  $data 数据
     * @param  string $type 类型
     */
    protected function ajax($data,$type='JSON')
    {
    	$type = strtoupper($type);
    	switch ($type) {
    		case 'HTML':
    		case 'TEXT':
    			$_data = $data;
    			break;
    		/*case "XML" :
                //XML处理
                $_data = Xml::create($data, "root", "UTF-8");
            */
    		default:
    			$_data = json_encode($_data);
    			break;
    	}
    	echo $_daat;
    	exit;
    }
    /**
     * 析构函数
     */
    public function __destruct()
    {
        Hook::listen('CONTROLLER_END', $this->options);
    }
}