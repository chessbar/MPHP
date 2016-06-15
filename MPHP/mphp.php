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
*框架入口文件
*/
define('MPHP_VERSION','MPHP2.1');
defined('DEBUG') or define('DEBUG',FALSE);
defined('DEBUG_TOOL') or define('DEBUG_TOOL',DEBUG);
defined('APP_PATH') or define('APP_PATH','Application/');
defined('TEMP_PATH') or define('TEMP_PATH',APP_PATH.'Temp/');
defined('TEMP_FILE') or define('TEMP_FILE',TEMP_PATH.'~Boot.php');//编译文件
if(!DEBUG && is_file(TEMP_FILE)){
	require TEMP_FILE;
}else{
	//加载核心编译文件
	define('MPHP_PATH',str_replace('\\','/',dirname(__FILE__)).'/');
	require MPHP_PATH.'Lib/Core/Boot.class.php';
	Boot::run();
}

