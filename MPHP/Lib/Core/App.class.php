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
*
*/
final class App{
	/**
	 * 运行应用
	 */
	static public function run()
	{
		//session处理
		session(C('SESSION_OPTIONS'));
		//开始执行钩子应用
		Hook::listen("APP_INIT");
		Hook::listen("APP_BEGIN");
		DEBUG && Debug::start('APP_BEGIN');
		self::start();
		DEBUG && Debug::show('APP_BEGIN','APP_END');
		//日志记录
		!DEBUG && C('LOG_RECORD') && Log::save();
		//应用钩子结束
		Hook::listen('APP_END');
	}
	/**
	 * 运行应用
	 * @return [type] [description]
	 */
	static private function start()
	{
		//控制器实例
		$controller = controller(CONTROLLER);
		if(!$controller)
		{
			if(!is_dir(MODULE_PATH)){
				_404('模块'.MODULE.'不存在');
			}
			$contrller = controller('Empty');
			if(!$controller)
			{
				_404('控制器'.CONTROLLER.'不存在');
			}
		}
		try{
			$action = new ReflectionMethod($controller,ACTION);
			if($action->isPublic()){
				$action->invoke($controller);
			}else{
				throw new ReflectionException;
			}
		}catch(ReflectionException $e){
			$action = new ReflectionMethod($contrller,'__call');
			$action->invokeArgs($controller,array(ACTION,''));
		}
	}
}