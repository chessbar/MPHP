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
*生成编译文件
*/
final class Boot{
	/**
	 * 允许框架
	 * @return [type] [description]
	 */
	static public function run()
	{
		self::setConst();//定义常量
		self::loadCoreFiles();//加载核心文件
		self::loadConfig();//加载基本配置
		self::compile();//编译核心文件
		MPHP::init();//初始化应用
		self::mkDirs();//创建应用目录
		App::run();
	}
	/**
	 * 定义常量
	 */
	static private function setConst()
	{
		if(version_compare(PHP_VERSION, '5.4.0','<')){
			ini_set('magic_quotes_runtime',0);
			define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc() ? true : false);
		}else{
			define("MAGIC_QUOTES_GPC",FALSE);
		}
		$root = str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']);
		define('ROOT_PATH',str_replace('\\','/',dirname($root)).'/');//根目录
		define('DS',DIRECTORY_SEPARATOR);//目录分隔符
		define('IS_CGI',substr(PHP_SAPI,0,3) =='cgi' ? true : false);
		define('IS_WEIN',strstr(PHP_OS,'WIN') ? true : false);
		define('IS_CLI',PHP_SAPI == 'cli' ? true : false);
		define("MPHP_DATA_PATH",       MPHP_PATH . 'Data/'); //数据目录
        define("MPHP_LIB_PATH",        MPHP_PATH . 'Lib/'); //lib目录
        define("MPHP_CONFIG_PATH",     MPHP_PATH . 'Config/'); //配置目录
        define("MPHP_CORE_PATH",       MPHP_LIB_PATH . 'Core/'); //核心目录
        define("MPHP_EXTEND_PATH",     MPHP_PATH . 'Extend/'); //扩展目录
        define("MPHP_ORG_PATH",        MPHP_EXTEND_PATH . 'Org/'); //org目录
        define("MPHP_TPL_PATH",        MPHP_PATH . 'Lib/Tpl/'); //框架Tpl目录
        define("MPHP_DRIVER_PATH",     MPHP_LIB_PATH . 'Driver/'); //驱动目录
        define("MPHP_FUNCTION_PATH",   MPHP_LIB_PATH . 'Function/'); //函数目录
        define("MPHP_LANGUAGE_PATH",   MPHP_LIB_PATH . 'Language/'); //语言目录
        defined("STATIC_PATH")          or define("STATIC_PATH",'Static/'); //网站静态文件目录
        defined("APP_COMMON_PATH")      or define("APP_COMMON_PATH", APP_PATH. 'Common/'); //应用公共目录
        defined("APP_CONFIG_PATH")      or define("APP_CONFIG_PATH", APP_COMMON_PATH . 'Config/' ); //应用公共目录
        defined("APP_MODEL_PATH")       or define("APP_MODEL_PATH",  APP_COMMON_PATH . 'Model/' ); //应用公共目录
        defined("APP_CONTROLLER_PATH")  or define("APP_CONTROLLER_PATH",  APP_COMMON_PATH . 'Controller/'); //应用公共目录
        defined("APP_LANGUAGE_PATH")    or define("APP_LANGUAGE_PATH", APP_COMMON_PATH . 'Language/'); //应用语言包目录
        defined("APP_ADDON_PATH")       or define("APP_ADDON_PATH", APP_PATH . 'Addons/' ); //插件目录
        defined("APP_HOOK_PATH")        or define("APP_HOOK_PATH", APP_COMMON_PATH . 'Hook/' ); //应用钓子目录
        defined("APP_TAG_PATH")         or define("APP_TAG_PATH",  APP_COMMON_PATH . 'Tag/'); //应用标签目录
        defined("APP_LIB_PATH")         or define("APP_LIB_PATH", APP_COMMON_PATH . 'Lib/' ); //应用扩展包目录
        defined("APP_COMPILE_PATH")     or define("APP_COMPILE_PATH", TEMP_PATH . 'Compile/' ); //应用编译包目录
        defined("APP_CACHE_PATH")       or define("APP_CACHE_PATH", TEMP_PATH . 'Cache/' ); //应用缓存目录
        defined("APP_TABLE_PATH")       or define("APP_TABLE_PATH", TEMP_PATH . 'Table/' ); //表字段缓存
        defined("APP_LOG_PATH")         or define("APP_LOG_PATH", TEMP_PATH . 'Log/' ); //应用日志目录
	}
	/**
	 * 加载核心文件
	 * @return [type] [description]
	 */
	static private function loadCoreFiles()
	{
		$files = array(
				MPHP_CORE_PATH . 'MPHP.class.php', //MPHP顶级类
	            MPHP_CORE_PATH . 'Controller.class.php', //MPHP顶级类
	            MPHP_CORE_PATH . 'MException.class.php', //异常处理类
	            MPHP_CORE_PATH . 'App.class.php', //MPHP顶级类
	            MPHP_CORE_PATH . 'Route.class.php', //URL处理类
	            MPHP_CORE_PATH . 'Hook.class.php', //钓子处理类
	            MPHP_CORE_PATH . 'Log.class.php', //公共函数
	            MPHP_FUNCTION_PATH . 'Functions.php', //应用函数
	            MPHP_CORE_PATH . 'Debug.class.php', //Debug处理类
			);
		foreach ($files as $file) {
			require $file;
		}
	}
	/**
	 * 加载基本配置
	 * @return [type] [description]
	 */
	static private function loadConfig()
	{	
		//系统配置
		C(require(MPHP_CONFIG_PATH.'Config.php'));
		//系统语言
		L(require(MPHP_LANGUAGE_PATH.'zh.php'));
		//应用别名导入
		alias_import(C('ALIAS'));
	}
	/**
	 * 创建应用目录
	 * @return [type] [description]
	 */
	static private function mkDirs()
	{
		//if(is_dir(APP_COMMON_PATH)) return;
		//目录
		$dirs = array(
				APP_PATH,
	            //临时目录
	            TEMP_PATH,
	            //应用组目录
	            APP_COMMON_PATH,
	            APP_CONFIG_PATH,
	            APP_ADDON_PATH,
	            APP_MODEL_PATH,
	            APP_CONTROLLER_PATH,
	            APP_LANGUAGE_PATH,
	            APP_HOOK_PATH,
	            APP_TAG_PATH,
	            APP_LIB_PATH,
	            APP_COMPILE_PATH,
	            APP_CACHE_PATH,
	            APP_TABLE_PATH,
	            APP_LOG_PATH,
	            //模块目录
	            MODULE_CONTROLLER_PATH,
	            MODULE_CONFIG_PATH,
	            MODULE_LANGUAGE_PATH,
	            MODULE_MODEL_PATH,
	            MODULE_HOOK_PATH,
	            MODULE_TAG_PATH,
	            MODULE_LIB_PATH,
	            MODULE_VIEW_PATH,
	            //控制器目录
	            CONTROLLER_VIEW_PATH,
	            MODULE_PUBLIC_PATH,
	            //公共目录
	            STATIC_PATH
			);
		foreach ($dirs as $d) {
			if(!dir_create($d,0755)){
				header('Content-type:text/html;charset=utf-8');
				exit("目录{$d}创建失败，请检测权限");
			}
		}
		//复制文件
		is_file(CONTROLLER_VIEW_PATH.'index.html') or copy(MPHP_PATH.'Lib/Tpl/view.html',CONTROLLER_VIEW_PATH.'index.html');
		//复制模板文件
		is_file(MODULE_PUBLIC_PATH . "success.html")    or copy(MPHP_PATH . 'Lib/Tpl/success.html', MODULE_PUBLIC_PATH . "success.html");
        is_file(MODULE_PUBLIC_PATH . "error.html")      or copy(MPHP_PATH . 'Lib/Tpl/error.html', MODULE_PUBLIC_PATH . "error.html");
        //复制配置文件
        is_file(APP_CONFIG_PATH.'config.php') or copy(MPHP_PATH.'Lib/Data/configApp.php',APP_CONFIG_PATH.'config.php');
        is_file(MODULE_CONFIG_PATH.'config.php') or copy(MPHP_PATH.'Lib/Data/configModule.php',MODULE_CONFIG_PATH.'config.php');
        //复制标签库
        is_file(APP_TAG_PATH.'CommonTag.class.php') or copy(MPHP_PATH.'Lib/Data/CommonTag.class.php',APP_TAG_PATH.'CommonTag.class.php');
        //创建测试控制器
        is_file(MODULE_CONTROLLER_PATH.'IndexController.class.php') or copy(MPHP_PATH.'Lib/Data/IndexController.class.php',MODULE_CONTROLLER_PATH.'IndexController.class.php');
		//创建安全文件
		self::safeFile();
		//批量创建模块
		if(defined('MODULE_LIST')){
			$module = explode(',',MODULE_LIST);
			if(!empty($module)){
				foreach ($module as $m) {
					Dir::create(APP_PATH.$m);
					Dir::copy(MODULE_PATH,APP_PATH.$m);
				}
			}
		}
	}
	/**
	 * 创建安全文件
	 * @return [type] [description]
	 */
	static private function safeFile()
	{
		if(defined('DIR_SAFE') &&  DIR_SAFE === FALSE) return;
		$dirs = array(
				APP_PATH,
	            //临时目录
	            TEMP_PATH,
	            //应用组目录
	            APP_COMMON_PATH,
	            APP_CONFIG_PATH,
	            APP_ADDON_PATH,
	            APP_MODEL_PATH,
	            APP_CONTROLLER_PATH,
	            APP_LANGUAGE_PATH,
	            APP_HOOK_PATH,
	            APP_TAG_PATH,
	            APP_LIB_PATH,
	            APP_COMPILE_PATH,
	            APP_CACHE_PATH,
	            APP_TABLE_PATH,
	            APP_LOG_PATH,
	           //模块目录
	            MODULE_CONTROLLER_PATH,
	            MODULE_CONFIG_PATH,
	            MODULE_LANGUAGE_PATH,
	            MODULE_MODEL_PATH,
	            MODULE_HOOK_PATH,
	            MODULE_TAG_PATH,
	            MODULE_LIB_PATH,
	            MODULE_VIEW_PATH,
	            //控制器目录
	            CONTROLLER_VIEW_PATH,
	            MODULE_PUBLIC_PATH,
	            //公共目录
	            STATIC_PATH
			);
		$file = MPHP_PATH.'Lib/Tpl/index.html';
		foreach ($dirs as $d) {
			is_file($d.'index.html') || copy($file,$d.'index.html');
		}
	}
	/**
	 * 编译核心文件
	 * @return [type] [description]
	 */
	static private function compile()
	{
		
	}
}