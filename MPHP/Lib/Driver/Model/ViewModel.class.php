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
*  视图模型处理类
*/
class ViewModel extends Model
{
	public $view = array();
	/**
	 * 这些方法需要改变驱动Db相应opt['table']与opt['field']等属性值
	 * @var array
	 */
	private $queryMethod = array('select','find','count','max','min','avg','sum');
	/**
	 * 魔术方法用于动态执行Db类中的方法
	 * @param  [type] $method [description]
	 * @param  [type] $param  [description]
	 * @return [type]         [description]
	 */
	public function __call($method,$param)
	{
		if(in_array($method,$this->queryMethod)){
			$this->setDriverOption();
		}
		//调用父类方法完成操作
		return parent::__call($method,$param);
	}
	/**
	 * 设置查询表名与字段 就是设置驱动的DB的opt属性
	 */
	private function setDriverOption()
	{
		if(empty($this->view)){
			//没有定义view数组时不设置
			return;
		}else{
			//获得本次查询的table 与field
			$this->setTable();
			$this->setField();
		}
	}
	public function find($where='')
	{
		$result = $this->select($where);
		return is_array($result) ? current($result) : $result;
	}
	public function select($where='')
	{
		//设置查询表与字段
		$this->setDriverOption();
		$this->trigger && method_exists($this, '__before_select') && $this->__before_select();
        $return = $this->db->select($where);
        $this->trigger && method_exists($this, '__after_select') && $this->__after_select($return);
        /**
         * 重置模型
         */
        $this->__reset();
        return $return;
	}
	/**
	 * 获得驱动表
	 */
	private function setTable()
	{
		$form = '';
		foreach ($this->view as $table => $set) {
			//表别名设置
			$as = isset($set['_as']) ? $set['_as'] : $table;
			$form .= C('DB_PREFIX').$table.' '.$as;
			//关联条件
			if(isset($set['_on'])){	
				$form .= " ON ".$set['_on'];
			}
			//_type 关联方式
			if(isset($set['_type'])){
				$form .= ' '.strtoupper($set['_type']) .' JOIN ';
			}
		}
		//除去表连接 后面的 LEFT JOIN 并更改表驱动
		$this->db->opt['table'] = preg_replace('@(INNER|LEFT|RIGHT)\s*JOIN\s*$@','',$from);
	}
	/**
	 * 设置表字段
	 */
	private function setField()
	{
		//如果链式操作中调用了field 方法 则不进行以下操作
		if($this->db->opt['field'] != "*"){
			return $this->db->opt['field'];
		}else{
			$field ='';
			foreach ($this->view as $table => $set) {
				if(!isset($set['_field'])){
					continue;
				}else{
					$field .= $set['field'] . ',';
				}
			}
			if(empty($field)){
				$field = rtrim($field,',');
				$this->db->opt['field'] = $field;
			}
		}
	}
}