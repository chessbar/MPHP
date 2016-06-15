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
* 标签基类
*/
abstract class Tag
{
	// 标签左符号
	private $left;
	//标签右符号
	private $right;
	//比较运算符
	private $condition = array(
			'neq' => '<>',
            'eq'  => '==',
            'gt'  => '>',
            'egt' => '>=',
            'lt'  => '<',
            'elt' => '<='
		);
	public function __construct()
	{
		$this->left = C('TPL_TAG_LEFT');
		$this->right = C('TPL_TAG_RIGHT');
		if(method_exists($this,'__init')){
			$this->__init();
		}
	}
	/**
	 * 解析标签
	 * @param  [type] $tag          标签
	 * @param  [type] &$ViewContent 解析内容
	 * @param  [type] &$MView       [description]
	 * @return [type]               [description]
	 */
	public function parseTag($tag,&$ViewContent,&$MView)
	{
		if($this->Tag[$tag]['block']){
			//块标签
			 $preg = '#' . $this->left . '(?:' . $tag . '|' . $tag . '\s+(.*))'
                . $this->right . '(.*)' .
                $this->left[0] . '/' . substr($this->left, 1) . $tag
                . $this->right . '#isU';
		}else{
			/**
             * 行标签处理
             */
            $preg = '#' . $this->left . '(?:' . $tag . '|' . $tag . '\s+(.*))/'
                . $this->right . "#isU"; //独立正则
		}
		$status = preg_match_all($preg, $ViewContent, $info,PREG_SET_ORDER);
		if($status){
			foreach ($info as $v) {
				//$v[0] 全部内容 $v[1] 属性部分 $v[2]内容部分
				//属性解析
				if(empty($v[1])){
					$attr = array();
				}else{
					$attr = $this->parseTagAttr($v[1]);
				}
				//内容解析
				$v[2] = isset($v[2]) ? $v[2] : '';
				$content = call_user_func_array(array($this,'_'.$tag),array($attr,$v[2],&$MView));
				$ViewContent = str_replace($v[0],$content,$ViewContent);
			}
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 解析标签属性
	 * @param  [type] $attrStr [description]
	 * @return [type]          [description]
	 */
	private function parseTagAttr($attrStr)
	{
		$pregAttr = '#([a-z]+)=(["\'])(.*)\2#iU';
		$status = preg_match_all($pregAttr,$attrStr,$info,PREG_SET_ORDER);
		if($status){
			$attr = array();
			foreach ($info as $v) {
				/**
				 * 0 全部内容
				 * 1 属性名
				 * 2 引号
				 * 3 属性值
				 */
				foreach ($info as $v) {
					$attr[$v[1]] = $this->parseAttrValue($v[3]);
				}
			}
			return $attr;
		}else{
			return array();
		}
	}
	/**
	 * 解析属性值
	 * @param  [type] $attrValue [description]
	 * @return [type]            [description]
	 */
	private function parseAttrValue($attrValue)
	{
		//替换GT LT 等
		foreach ($$this->condition as $k => $v) {
			$attrValue = preg_replace('/\s+'.$k.'\s+/i',$v,$attrValue);
		}
		//替换常量
		$const = get_defined_constants(true);
		foreach ($const['user'] as $name => $value) {
			//替换已__开始的常量
			if(substr($name,0,2) == '__'){
				$attrValue = str_ireplace($name, $value, $attrValue);
			}
		}
		//解析变量为php可识别状态
		$preg = '@\$([\w\.]+)@i';
		$status = preg_match_all($preg,$attrValue,$info,PREG_SET_ORDER);
		if($status){
			foreach ($info as $k => $v) {
				$var = '';
				$data = explode('.',$d[1]);
				foreach ($data as $n => $m) {
					if($n===0){
						$var .= $m;
					}else{
						$var .= '[\''.$m.'\']';
					}
				}
				$attrValue = str_replace($d[1],$var,$attrValue);
			}
		}
		return $attrValue;
	}
}