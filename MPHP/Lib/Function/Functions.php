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
*核心函数库
*/
function p($var)
{
	if(is_bool($var) || is_null($var)){
		var_dump($var);
	}else{
		echo "<pre style='padding:5px;border:1px solid #d90; background:#dedede;border-radius:5px;'>";
		print_r($var);
		echo '</pre>';
	}
}
/**
 * 加载配置项
 * @param [type] $name  配置名称
 * @param [type] $value 配置值
 */
function C($name = null,$value =null)
{
	static $config = array();
	//C()返回配置项
	if(is_null($name)){
		return $config;
	}elseif(is_string($name)){
		$name = strtoupper($name);
		$data = array_change_key_case($config,CASE_UPPER);
		if(!strstr($name,'.')){//一维数组 
			//返回配置项 
			if(is_null($value)){
				return isset($data[$name]) ? $data[$name] : null;
			}else{//
				return $config[$name] = isset($data[$name]) && is_array($data[$name]) && is_array($value) ? array_merge($config[$name],$value) : $value;
			}
		}else{//二维数组
			$name = array_change_key_case(explode('.',$name));
			if(is_null($value)){
				return isset($data[$name[0]][$name[1]]) ? $data[$name[0]][$name[1]] : null;
			}else{
				return $config[$name[0]][$name[1]] = $value;
			}
		}

	}elseif(is_array($name)){
		return $config = array_merge($config,array_change_key_case($name,CASE_UPPER));
	}
}
/**
 * 加载语言配置
 * @param [type] $name  [description]
 * @param [type] $value [description]
 */
function L($name =null,$value=null)
{
	static $language = array();
	if(is_null($name)){//L();
		return $language;
	}elseif(is_string($name)){
		$name = strtoupper($name);
		if(!strstr($name,'.')){//
			 if(is_null($value)){//返回值
			 	return isset($language[$name]) ? $language[$name] : null;
			 }else{
			 	return $language[$name] = isset($language[$name]) && is_array($language[$name]) && is_array($value) ? array_merge($language[$name],$value) : $value;
			 }
		}else{//二维数组
			$name= array_change_key_case(explode('.',$name));
			if(is_null($value)){
				return isset($language[$name[0]][$name[1]]) ? $language[$name[0]][$name[1]] : null;
			}else{
				return $language[$name[1]][$name[1]] = $value;
			}
		}
	}elseif(is_array($name)){
		return $language = array_merge($language,array_change_key_case($name,CASE_UPPER));
	}
}
/**
 * 导入应用别名
 * @return [type] [description]
 */
function alias_import($name = null,$path=null)
{
	static $_alias = array();
	if(is_null($name)){
		return $_alias;
	}elseif(is_array($name)){
		//批量导入别名定义
		return $_alias = array_merge($_alias,array_change_key_case($name));
	}elseif(!is_null($path)){
		//定义一条别名规则
		return $_alias[$name] = $path;
	}elseif(isset($_alias[strtolower($name)])){
		//加载别名定义文件
		return require_cache($_alias[$name]);
	}
	return false;
}
/**
 * 加载文件并缓存
 * @param  [type] $path 文件路径
 * @return [type]       [description]
 */
function require_cache($path = null)
{
	//静态文件缓存
	$files = array();
	if(is_null($path)){//返回加载过的文件列表
		return $files;
	}
	//已经加载过
	if(isset($files[$path])){
		return true;
	}
	//区分大小写的文件判断
	if(!file_exists_case($path)){
		return false;
	}
	//加载文件并记录缓存
	require($path);
	$files[$path] = true;
	return true;
}
/**
 * 区分大小写判断文件
 * @param  [type] $file [description]
 * @return [type]       [description]
 */
function file_exists_case($file)
{
	if(is_file($file)){
		//Windows环境下判断大小写
		if(C('CHECK_FILE_CASE')){
			if(basename(realpath($file)) != basename($file)){
				return false;
			}
		}
		return true;
	}
	return false;
}
/**
 * 递归并创建文件
 * @param  string  $dirName 目录
 * @param  integer $auth    权限
 * @return bool
 */
function dir_create($dirName,$auth=0755)
{
	$dirPath = rtrim(str_replace('\\','/',$dirName),'/');
	if(is_dir($dirPath)) return true;
	$dirs = explode('/',$dirPath);
	$dir = '';
	foreach ($dirs as $v) {
		$dir .= $v . '/';
		is_dir($dir) or mkdir($dir,$auth,true);
	}
	return is_dir($dirPath);
}
/**
 * 错误终端
 * @param  [type] $error [description]
 * @return [type]        [description]
 */
function halt($error)
{
	$e = array();
	if(DEBUG){
		if(!is_array($error)){
			$trace = debug_backtrace();
			$e['message'] = $error;
			$e['file'] = $trace[0]['file'];
			$e['line'] = $trace[0]['line'];
			$e['class'] = isset($trace[0]['class']) ? $trace[0]['class'] : "";
			$e['function'] = isset($trace[0]['function']) ? $trace[0]['function'] : "";
			ob_start();
			debug_print_backtrace();
			$e['trace'] = htmlspecialchars(ob_get_clean());
		}else{
			$e = $error;
		}
	}else{
		//显示错误URL 
		if($_url = C('ERROR_URL')){
			go($_url);
		}else{
			$e['message'] = C('ERROR_MESSAGE');
		}
	}
	//显示DEBUG模板 开始DEBUG 显示trace
	require MPHP_PATH.'Lib/Tpl/halt.html';
	exit;
}
/**
 * 跳转
 * @param  string  $url  跳转地址
 * @param  integer $time 跳转时间
 * @param  string  $msg  提示信息
 * @return [type]        [description]
 */
function go($url,$time=0,$msg='')
{
	$url = Route::getUrl($url);
	if(!heade_sent()){
		$time == 0  ? header("Location:".$url) : header("refresh:{$time};url={$url}");
	}else{
		echo "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
		if($time) exit($msg);
	}
}
/**
 *  判断是否为ajax提交
 * @return boolean [description]
 */
function is_ajax()
{
	if(isset($_SERVER['HTTP_X_REQUEST_WITH']) && strtolower($_SERVER['HTTP_X_REQUEST_WITH']) == 'xmlhttprequest') return true;
	return false;
}
/**
 * 导入文件组
 * @return [type] [description]
 */
function require_array($fileArr)
{
	foreach ($fileArr as $file) {
		if(is_file($file) && require_cache($file)) return true;
	}
	return false;
}
/**
 * trace记录
 * @param  string  $value  [description]
 * @param  string  $level  [description]
 * @param  boolean $record [description]
 * @return [type]          [description]
 */
function trace($value='[MPHP]',$level = 'DEBUG',$record=false)
{
	static $_trace = array();
	if($value === '[MPHP]') return $_trace;
	$info = ':'.print_r($value,true);
	//调试模式时处理ERROR类型
	if(DEBUG && 'ERROR' == $level){
		throw_exception($info);
	}
	if(!isset($_trace[$level])){
		$_trace[$level] = array();
	}
	$_trace[$level][] = $info;
	if(IS_AJAX || $record){
		Log::write($info,$level,$record);
	}
}
/**
 * 抛出异常
 * @param  [type]  $msg  错误信息
 * @param  string  $type 异常类
 * @param  integer $code 编码
 * @return [type]        [description]
 */
function throw_exception($msg,$type = 'MException',$code = 0)
{
	if(class_exists($type,false)){
		throw new $type($msg,$type,$code);
	}else{
		halt($msg);
	}
}
function FriendlyErrorType($type)
{
    switch ($type) {
        case E_ERROR: // 1 //
            return 'E_ERROR';
        case E_WARNING: // 2 //
            return 'E_WARNING';
        case E_PARSE: // 4 //
            return 'E_PARSE';
        case E_NOTICE: // 8 //
            return 'E_NOTICE';
        case E_CORE_ERROR: // 16 //
            return 'E_CORE_ERROR';
        case E_CORE_WARNING: // 32 //
            return 'E_CORE_WARNING';
        case E_CORE_ERROR: // 64 //
            return 'E_COMPILE_ERROR';
        case E_CORE_WARNING: // 128 //
            return 'E_COMPILE_WARNING';
        case E_USER_ERROR: // 256 //
            return 'E_USER_ERROR';
        case E_USER_WARNING: // 512 //
            return 'E_USER_WARNING';
        case E_USER_NOTICE: // 1024 //
            return 'E_USER_NOTICE';
        case E_STRICT: // 2048 //
            return 'E_STRICT';
        case E_RECOVERABLE_ERROR: // 4096 //
            return 'E_RECOVERABLE_ERROR';
        case E_DEPRECATED: // 8192 //
            return 'E_DEPRECATED';
        case E_USER_DEPRECATED: // 16384 //
            return 'E_USER_DEPRECATED';
    }
    return $type;
}
/**
 * session处理
 * @param  string $name  数组为初始session
 * @param  string $value 值
 * @return mixed
 */
function session($name = '',$value = '')
{
	if(is_array($name)){
		ini_set('session.suto_start',0);
		if(isset($name['name'])) session_name($name['name']);
		if(isset($_REQUEST[session_name()])) session_id($_REQUEST[session_name()]);
		if(isset($name['path'])) session_save_path($name['path']);
		if(isset($name['domain'])) ini_set('session.cookie_domain',$name['domain']);
		if(isset($name['expire'])) {
			ini_set('session.gc_maxlifetime', $name['expire']);
			session_set_cookie_params($name['expire']);
		}
		if (isset($name['use_trans_sid']))
            ini_set('session.use_trans_sid', $name['use_trans_sid'] ? 1 : 0);
        if (isset($name['use_cookies']))
            ini_set('session.use_cookies', $name['use_cookies'] ? 1 : 0);
        if (isset($name['cache_limiter']))
            session_cache_limiter($name['cache_limiter']);
        if (isset($name['cache_expire']))
            session_cache_expire($name['cache_expire']);
        if(isset($name['type'])){
        	$class = 'Session' . ucfirst($name['type']);
        	require_cache(MPHP_DRIVER_PATH.'/Session/'.$class.'.class.php');
        	$hander = new $class();
        	$hander->run();
        }
        //自动开启session
        if(C('SESSION_AUTO_START')) session_start();
	}else if($name ===''){//session();
		return $_SESSION;
	}else if(is_null($name)){//session(null);
		$_SESSION = array();
		session_unset();
		session_destroy();
	}else if($value ===''){//value为空
		if('[pause]' == $name){//session('[pause]') 停止session
			session_write_close();
		}elseif('[start]' == $name){//开启session
			session_start();
		}elseif('[destroy]' == $name){//注销session
			$_SESSION = array();
			session_unset();
			session_destroy();
		}elseif('[regenerate]' == $name){//生成id
			session_regenerate_id();
		}elseif(0 === strpos($name,'?')){//session('?username') 检测session
			$name = substr($name,1);
			return isset($_SESSION[$name]);
		}else{
			return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
		}
	}elseif(is_null($value)){
		if(isset($_SESSION[$name])) unset($_SESSION[$name]);
	}else{
		$_SESSION[$name] = $value;
	}	
}
/**
 * 获取客户端ip
 * @return [type] [description]
 */
function ip_get_client($type=0)
{
	if($_SERVER){
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	}else{
		if(getenv('HTTP_X_FORWARDED_FOR')){
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}elseif(getenv('HTTP_CLIENT_IP')){
			$ip = getenv('HTTP_CLIENT_IP');
		}else{
			$ip = getenv('REMOTE_ADDR');
		}
	}
	$long = ip2long($ip);
	$clientIp = $long ? array($ip,$long) : array('0.0.0.0',0);
	return $clientIp[$type];
}
/**
 * 实例化控制器并执行方法
 * @param  [type] $class  [description]
 * @param  [type] $method [description]
 * @param  array  $args   [description]
 * @return [type]         [description]
 */
function controller($class,$method = null,$args = array())
{
	$class = $class . C('CONTROLLER_FIX');
	$classFile = $class.".class.php";
	if(require_array(
			array(MPHP_CORE_PATH.$classFile,
			MODULE_CONTROLLER_PATH.$classFile,
			APP_CONTROLLER_PATH.$classFile
		)))
	{
		if(class_exists($class)){
			$obj = new $class();
			if($method && method_exists($obj,$method)){
				return call_user_func_array(array($class,$method),$args);
			}
			return $obj;
		}
	}else{
		return false;
	}
}
/**
 * 404错误
 * @param  string $msg 提示信息
 * @param  string $url 跳转地址
 * @return [type]      [description]
 */
function _404($msg='',$url='')
{
	DEBUG && halt($msg);
	//写入日志
	Log::write($msg);
	$url = empty($url) ? C("404_URL") : $url;
	if($url) go($url);
	else set_http_state(404);
	exit;
}
/**
 * HTTP状态嘛设置
 * @param [type] $code [description]
 */
function set_http_state($code)
{
	$state = array(
			200=>'OK',//Success 2xx
			// Redirection 3xx 重定向
			301=>'Moved Permanently',//永久移除
			302=>'Moved Temporarily',//临时移动
			//Client Error 4xx
			400=>'Bad Request',
			403=>'Forbidden',
			404=>'Not Found',
			//Server Error 5xx
			500=>'Internal Server Error',
			503=>'Service Unavailable'
		);
	if(isset($state[$code])){
		header('HTTP/1.1 '.$code.' '.$state[$code]);
		header('Status:'.$code.' '.$status[$code]);
	}
}
/**
 * 缓存处理
 * @param [type]  $name    缓存名称
 * @param boolean $value   缓存内容
 * @param [type]  $expire  缓存时间
 * @param array   $options 选项
 */
function S($name,$value = false,$expire =null,$options = array())
{
	/**
	 * 缓存数据
	 * @var array
	 */
	static $_data = array();
	$cacheObj = Cache::init($options);
	//删除缓存
	if(is_null($name)){
		return $cacheObj->del($name);
	}
	$driver = isset($options['driver']) ? $options['driver'] : '';
	$key = $name.$driver;
	if($value === false){
		if(isset($_data[$key])){
			Debug::$cache['read_s']++;
			return $_data[$key];
		}else{
			return $cacheObj->get($name,$expire);
		}
	}
	$cacheObj->set($name,$value,$expire);
	$_data[$key] = $value;
	return true;
}
/**
 * 生成序列字符串
 * @param  [type] $var [description]
 * @return [type]      [description]
 */
function md5_d($var)
{
	return md5(serialize($var));
}
/**
 * 快速缓存 以文件形式缓存
 * @param [type]  $name  缓存key
 * @param boolean $value 删除缓存
 * @param [type]  $path  缓存目录
 */
function F($name,$value = false,$path=APP_CACHE_PATH)
{
	static $_cache = array();
	$cacheFile = rtrim($path,'/').'/'.$name.'.php';
	//删除缓存F('user',NULL)
	if(is_null($name)){
		if(is_file($cacheFile)){
			unlink($cacheFile);
			unset($_cache[$name]);
		}
		return true;
	}
	//F('user');读取缓存
	if($value === false)
	{
		if(isset($_cache[$name])) return $_cache[$name];
		return is_file($cacheFile) ? include $cacheFile : null;
	}
	$data = "<?php if(!defined('MPHP_PATH'))exit;\nreturn".compress(var_export($value,true)).";\n?>";
	is_dir($path) || dir_create($path);
	if(!file_put_contents($cacheFile,$data)){
		return false;
	}
	$_cache[$name] = $value;
	return true;
}
/**
 * 去空格，去除注释包括单行及多行注释
 * @param string $content 数据
 * @return string
 */
function compress($content)
{
    $str = ""; //合并后的字符串
    $data = token_get_all($content);
    $end = false; //没结束如$v = "hdphp"中的等号;
    for ($i = 0, $count = count($data); $i < $count; $i++) {
        if (is_string($data[$i])) {
            $end = false;
            $str .= $data[$i];
        } else {
            switch ($data[$i][0]) { //检测类型
                //忽略单行多行注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //去除格
                case T_WHITESPACE:
                    if (!$end) {
                        $end = true;
                        $str .= " ";
                    }
                    break;
                //定界符开始
                case T_START_HEREDOC:
                    $str .= "<<<HDPHP\n";
                    break;
                //定界符结束
                case T_END_HEREDOC:
                    $str .= "HDPHP;\n";
                    //类似str;分号前换行情况
                    for ($m = $i + 1; $m < $count; $m++) {
                        if (is_string($data[$m]) && $data[$m] == ';') {
                            $i = $m;
                            break;
                        }
                        if ($data[$m] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;

                default:
                    $end = false;
                    $str .= $data[$i][1];
            }
        }
    }
    return $str;
}
/**
 * 删除空格注销
 * @return [type] [description]
 */
function compress1($content)
{		
	$str = '';//合并后的字符串
	$data = token_get_all($content);
	$end = false;
	$count = count($data);
	for ($i=0; $i < count($data); $i++) { 
		if(is_string($data[$i])){
			$end = false;
			$str .= $data[$i];
		}else{
			//检测类型
			switch ($data[$i][0]) {
				//忽略单行多行注释
				case T_COMMENT:
				case T_DOC_COMMENT:
					break;
				//去空格
				case T_WHITESPACE:
					if(!$end){
						$end = true;
						$str .= '';
					}
					break;
				//定界符开始
				case T_START_HEREDOC:
					$str .= "<<<MPHP\n";
					break;
				//定界符结束
				case T_END_HEREDOC:
					$str .= "MPHP;\n";
					for ($m=$i+1; $m < $count; $m++) { 
						if(is_string($data[$m]) && $data[$m] == ';'){
							$i = $m;
							break;
						}
						if($data[$m] == T_CLOSE_TAG){
							break;
						}
					}
					break;
				default:
					$end = false;
					$str .= $data[$i][1];
					break;
			}
		}
	}
	return $str;
}
/**
 * 记录缓存读写与数据操作次数
 * @param [type] $name [description]
 * @param [type] $num  [description]
 */
function N($name,$num=null)
{
	//计数静态变量
	static $data = array();
	if(!isset($data[$name])){
		$data[$name] = 0;
	}
	if(is_null($num)){//获得计数
		return $data[$name];
	}else{//更改记录数
		$data[$name] += (int)$num;
	}
}
/**
 * [import description]
 * @param  null $calss 类名称
 * @param  null $base  目录
 * @param  string $ext   扩展名
 * @return bool
 */
function import($class=null,$base =null,$ext='.class.php')
{
	$class=str_replace('.','/',$class);
	if(is_null($base)){
		$info= explode('/',$class);
		if($info[0] == '@'){
			//应用下类文件
			$base = APP_PATH;
			$class = substr_replace($class,'',0,strlen($info[0])+1);
		}elseif(strtoupper($info[0]) == 'MPHP'){
			//框架中类文件
			$base = dirname(substr_replace($class,MPHP_PATH,0,5)).'/';
			$class = basename($class);
		}elseif(in_array($info[0],array('Lib,Tag'))){
			//模块 Lib 或Tag 下的类文件
			$base = MODELE_PATH;
		}else{
			$base = dirname($class) . '/';
			$class = basename($class);
		}
	}else{
		$base = rtrim(str_replace('.','/',$base),'/').'/';
	}
	//类文件
	$file = $base.$class.$ext;
	if(!class_exists(basename($class),false)){
		return require_cache($file);
	}
	return true;
}
/**
 * 根据配置文件的URL参数重新生成URL地址
 * @param String $path 访问url
 * @param array $args GET参数
 *                     <code>
 *                     $args = "nid=2&cid=1"
 *                     $args=array("nid"=>2,"cid"=>1)
 *                     </code>
 * @return string
 */
function U($path, $args = array())
{
    return Route::getUrl($path, $args);
}
/**
 * 加载核心模型
 * @param [type] $table  表名
 * @param [type] $full   是否为全表名
 * @param array  $param  参数
 * @param [type] $driver 驱动
 */
function M($table=null,$full=null,$param=array(),$driver=null)
{
	return new Model($table,$full,$param,$driver);
}
/**
 * 获得扩展模型
 * @param [type] $name   模型名称不加Model后缀
 * @param [type] $full   是否为表全名
 * @param array  $param  参数
 * @param [type] $driver 驱动
 */
function K($name,$full=null,$param=array(),$driver=null)
{
	$class = ucfirst($name) . "Model";
	return new $class(strtolower($name),$full,$param);
}
/**
 * 获得关联模型
 * @param [type] $tableName [description]
 * @param [type] $full      [description]
 */
function R($tableName=null,$full=null)
{
	return new RelationModel($tableName,$full);
}
/**
 * 获得视图模型
 * @param [type] $tableName [description]
 * @param [type] $full      [description]
 */
function V($tableName=null,$full=null)
{
	return new ViewModel($tableName,$full);
}











