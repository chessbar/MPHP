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
* mphp模板引擎标签解析类
*/
class ViewTag extends Tag
{
	/**
	 * block 块标签
	 * level 嵌套层次
	 * @var array
	 */
	public $Tag = array(
			'foreach' => array('block' => 1, 'level' => 5),
            'list'    => array('block' => 1, 'level' => 5),
            'if'      => array('block' => 1, 'level' => 5),
            'elseif'  => array('block' => 0, 'level' => 0),
            'else'    => array('block' => 0, 'level' => 0),
            'js'      => array('block' => 0, 'level' => 0),
            'css'     => array('block' => 0, 'level' => 0),
            'include' => array('block' => 0, 'level' => 0),
            'jsconst' => array('block' => 0, 'level' => 1),
            'empty'   => array('block' => 1, 'level' => 5),
            'noempty' => array('block' => 0, 'level' => 0),
		);
	
}