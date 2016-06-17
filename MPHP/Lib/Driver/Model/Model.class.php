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
    	/**
         * 执行子类构造函数__init
         */
        if (method_exists($this, "__init")) {
            $this->__init($param);
        }
    }
    /**
     * 魔术方法  设置模型属性如表名字段名
     *
     * @param string $var   属性名
     * @param mixed  $value 值
     *
     * @return object
     */
    public function __set($var, $value)
    {
        /**
         * 设置$data属性值用于插入修改等操作
         */
        $this->data[$var] = $value;
    }

    /**
     * 魔术方法
     *
     * @param $name 变量
     *
     * @return mixed
     */
    public function __get($name)
    {
        /**
         * 返回$this->data属性
         * $this->data属性指添加与编辑的数据
         */
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
    }
    public function __call($method,$args)
    {
        //执行表名函数
        if(isset($this->alias[$method])){
            return call_user_func_array(array($this,$this->alias[$method]),$args);
        }elseif(method_exists($this,$method)){
            $return = call_user_func_array(array($this,$method),$args);
            return $return ===null ? $this : $return;
        }
    }
    //重置模型
    protected function __reset()
    {
         $this->data = array();
         $this->trigger = true;
    }
    /**
     * 获得添加 更新的数据
     * @param  array  $data [description]
     * @return array|null
     */
    public function data($data = array())
    {
        $this->data = empty($data) ? $_POST : $data;
        //系统开启转义时 去除转义操作
        foreach ($this->data as $key => $value) {
           if(MAGIC_QUOTES_GPC && is_string($value)){
            $this->data[$key] = stripslashes($val);
           }
        }
        return $this;
    }
    /**
     * 执行自动映射 自动验证 自动完成
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function create($data = array())
    {
        //初始化数据
        $this->data($data);
        /**
         * 批量执行方法
         */
        $action = array('validate','auto','map');
        foreach ($action as $v) {
           if(!$this->$v()){
                return;
           }
        }
        return true;
    }
    /**
     * 字段映射
     * 将添加或更新的数据键名改为字段名
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function map($data)
    {
        //初始化数据
        $this->data($data);
        if(!empty($this->map)){
            foreach ($this->map as $k => $v) {
               //处理POST 
               if(isset($this->data[$k])){
                    $this->data[$v] = $this->data[$k];
                    unset($this->data[$k]);
               }
            }
        }
        return true;
    }
    /**
     * 获得当前操作方法
     * 判断数据中是否有主键 存在主键为更新 否则为插入 
     * 1为插入 2为更新
     * @return [type] [description]
     */
    private function getCurrentMethod()
    {
        return isset($this->data[$this->db->pri]) ? 2 : 1;
    }
    /**
     * 自动验证
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function validate($data = array())
    {
        //验证规则为空时 不验证
        if(empty($this->validate)){
            return true;
        }
        //操作数据
        $this->data($data);
        $data = &$this->data;
        //当前方法
        $motion = $this->getCurrentMethod();
        foreach ($this->validate as $v) {
            //表名
            $name = $v[0];
            /**
             * 验证条件
             * 1 有表单时
             * 2 必须验证
             * 3 不为空时
             */
            $condition = isset($v[3]) ? $v[3] : 2;
            /**
             * 验证时机
             * 1插入时
             * 2更新时
             * 3插入与更新
             */
            $action = isset($v[4]) ? $v[4] : 3;
            //验证时间判断
            if(! in_array($action,array($motion,3))){
                continue;
            }
            $msg = $v[2];
            switch ($condition) {
                case 1:
                    //有表单时验证 不存在时则不验证
                    if(!isset($data[$name])){
                        continue 2;
                    }
                    break;
                case 2:
                    //必须验证
                    if(!isset($data[$name])){
                        $this->error = $msg;
                        return false;
                    }
                    break;
                case 3:
                    //不为空时验证 
                    if(empty($data[$name])){
                        continue 2;
                    }
                    break;
            }
            if($pos = strpos($v[1],':')){
                $func = substr($v[1],0,$pos);
                $args = substr($v[1],$pos+1);
            }else{
                $func = $pos;
                $args = '';
            }
            //执行模型方法
            if(method_exists($this,$func)){
                $res = call_user_func_array(array($this,$func),array($name,$data[$name],$msg,$args));
                if(!$res !== true){
                    $this->error = $res;
                    return false;
                }
            }elseif(function_exists($func)){
                //函数验证
                $res = $func($name,$data[$name],$msg,$args);
                if(!$res !== true){
                    $this->error = $res;
                    return false;
                }
            }else{
                /**
                 * validate验证类处理
                 */
                $validate = new Validate();
                $func = '_'.$func;
                if(method_exists($validate,$func)){
                    $res = call_user_func_array(array($this,$func),array($name,$data[$name],$msg,$args));
                    //验证失败
                    if(!$res !== true){
                        $this->error = $res;
                        return false;
                    }
                }
            }
        }
        return true;
    }
    /**
     * 自动验证
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function auto($data)
    {
        //获得数据
        $this->data($data);
        $data = &$this->data;
        /**
         * 处理时机
         * 1 插入
         * 2 更新
         */
        $motion = $this->getCurrentMethod();
        foreach ($this->auro as $v) {
            //表名
            $name = $v[0];
            //方法名
            $func = $v[1];
            /**
             * 执行方法
             *  function 方法
             *  method 模型方法
             *  string 字符串
             */
            $handle = isset($v[2]) ? $v[2] : 'string';
            /**
             * 处理条件
             * 1有表单时
             * 2必须处理
             * 3值不为空时
             * 4值为空时
             */
            $condition = isset($v[3]) ? $v[3] : 1;
            /**
             * 处理时机
             * 1插入时
             * 2更新时
             * 3插入与更新时
             */
            $action = isset($v[4]) ? $v[4] : 3;
            //验证处理时机
            if(!in_array($action,array($motion,3))){
                continue;
            }
            switch ($condition) {
                case 1:
                    //不存在字段时
                    if(!isset($data[$name])){
                        continue 2;
                    }
                    break;
                case 2:
                    //必须验证
                    if ( ! isset($data[$name])) {
                        $data[$name] = '';
                    }
                    break;
                case 3:
                   //值不为空时
                    if (empty($data[$name])) {
                        continue 2;
                    }
                    break;
                case 4:
                    //值为空时
                    if (empty($data[$name])) {
                        $data[$name] = '';
                    } else {
                        continue 2;
                    }
                    break;
            }
            $data[$name] = isset($data[$name]) ? $data[$name] : '';
            switch ($handle) {
                case "function":
                    //函数
                    if(function_exists($func)){
                        $data[$name] = $func($data[$name]);
                    }
                    break;
                case "method":
                    //模型方法
                    if(method_exists($this,$method)){
                        $data[$name] = $func($data[$name]);
                    }
                    break;
                case "string":
                    $data[$name] = $func;
                    break;
            }
        }
        return true;
    }
    //深圳触发器状态
    public function trigger($status)
    {
        $this->trigger = $status;

        return $this;
    }
    /**
     * 删除数据
     * @param  string $where [description]
     * @return [type]        [description]
     */
    public function delete($where = '')
    {
        $this->trigger && method_exists($this,'__before_delete') && $this->__before_delete();
        $return = $this->db->delete($where);
        $this->trigger && method_exists($this,'__after_delete') && $this->__after_delete($return);
        $this->__reset();
        return $return;
    }
    /**
     * 查找满足条件的一条记录
     * @param  string $where [description]
     * @return [type]        [description]
     */
    public function find($where='')
    {
        $result = $this->select($where);
        return is_array($result) ? current($result) : $result;
    }
    /**
     * 查询结果
     *
     * @param string $where 条件
     *
     * @return mixed
     */
    public function select($where = '')
    {
        $this->trigger && method_exists($this, '__before_select')
        && $this->__before_select();
        $return = $this->db->select($where);
        $this->trigger && method_exists($this, '__after_select')
        && $this->__after_select($return);
        /**
         * 重置模型
         */
        $this->__reset();

        return $return;
    }
    /**
     * 更新数据
     *
     * @param array $data 更新的数据
     *
     * @return mixed
     */
    public function update($data = array())
    {
        $this->data($data);
        $this->trigger && method_exists($this, '__before_update')
        && $this->__before_update($this->data);
        $return = $this->db->update($this->data);
        $this->trigger && method_exists($this, '__after_update')
        && $this->__after_update($return);
        /**
         * 重置模型
         */
        $this->__reset();

        return $return;
    }
    /**
     * 插入数据
     *
     * @param array $data 新数据
     *
     * @return mixed
     */
    public function insert($data = array())
    {
        $this->data($data);
        $this->trigger && method_exists($this, '__before_insert')
        && $this->__before_insert($this->data);
        $return = $this->db->insert($this->data);
        $this->trigger && method_exists($this, '__after_insert')
        && $this->__after_insert($return);
        /**
         * 重置模型
         */
        $this->__reset();

        return $return;
    }
    /**
     * replace方式插入数据
     * 更新数据中存在主键或唯一索引数据为更新操作否则为添加操作
     *
     * @param array $data
     *
     * @return mixed
     */
    public function replace($data = array())
    {
        $this->data($data);
        $this->trigger && method_exists($this, '__before_insert')
        && $this->__before_insert($this->data);
        $return = $this->db->replace($this->data);
        $this->trigger && method_exists($this, '__after_insert')
        && $this->__after_insert($return);
        /**
         * 重置模型
         */
        $this->__reset();

        return $return;
    }
}