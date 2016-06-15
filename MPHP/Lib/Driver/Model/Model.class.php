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
* 基本模型处理类
*/
class Model
{
	//数据表
	public $table = null;
	//数据库连接驱动对象
	public $db = null;
	//触发器状态
	public $trigger = true;
	//模型错误信息
	public $error = true;
	//模型操作数据
	public $data = array();
	//验证规则
	public $validate = array();
	//自动完成规则
	public $auto = array();
	//字段映射规则
	public $map = array();
	//别名方法
	public $alias
        = array('add' => 'insert', 'save' => 'update', 'all' => 'select',
                'del' => 'delete');
    /**
     * 构造函数
     * @param [type]  $table  表名
     * @param boolean $full   是否为全表名
     * @param array   $param  参数
     * @param [type]  $driver 驱动
     */
    public function __construct($table=null,$full=false,$param = array(),$driver=null)
    {
    	//初始化表名
    	if(!$this->table)
    	{
    		$this->table = $table;
    	}
    	//获得数据引擎
    	$this->db = DbFactory::factory($driver,$this->table,$full);
    	
    }
}