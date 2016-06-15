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
abstract class Db implements DbInterface
{
	public $table = NULL;//表
	public $pri = null;//默认表主键
	public $fieldData;//字段数组
	public $opt = array(
			'table'=>null,
			'pri'=>null,
			'field'=>'*',
			'fieldData'=> array(),
			'where'=>'',
			'like'=>'',
			'group' => '',
	        'having' => '',
	        'order' => '',
	        'limit' => '',
	        'cacheTime'=>null//查询缓存事件
		);
	public $lastSql;//最后发送的sql
	public $error = null;//错误信息
	/**
	 * 将eq转换标准的sql语法
	 */
	public $condition = array(
			"eq" => " = ", "neq" => " <> ",
	        "gt" => " > ", "egt" => " >= ",
	        "lt" => " < ", "elt" => " <= ",
		);
	/**
	 * 数据库连接
	 * @param  [type] $table 
	 * @param  [type] $full  [description]
	 * @return [type]        [description]
	 */
	public function link($table,$full)
	{
		if($this->connect()){
			if($table){
				//初始化表
				$this->table($table,$full);
			}
		}
	}
	/**
	 * 初始化表字段与主键
	 * @param  [type]  $table [description]
	 * @param  boolean $full  [description]
	 * @return [type]         [description]
	 */
	public function table($table,$full=false)
	{
		//初始化opt参数
		$this->optInit();
		//字段集
		$fieldData = $this->getAllField($table,$full);
		//表主键
		$pri =$this->getPrimaryKey($table,$full);
		//设置选项
		$this->opt['table'] = $full ? $table : C('DB_PREFIX') . $table;
		$this->opt['fieldData'] = $fieldData;
		$this->opt['pri'] = $pri;
	}
	/**
	 * 查找满足条件的所有记录
	 * @param  [type]  $field     [description]
	 * @param  boolean $returnAll [description]
	 * @return [type]             [description]
	 */
	public function getField($field,$returnAll = false)
	{
		//设置查询字段
		$this->field($field);
		$result = $this->select();
		if($result){
			//字段数组
			$field = explode(',',str_replace(' ','',$field));
			//如果有多个字段时 返回多维数组并且第一字段值做为KEY
			switch (count($field)) {
				case 1:
					if($resultAll){
						$data = array();
						foreach ($result as $v) {
							$data[]= current($v);
						}
						return $data;
					}else{
						return current($result[0]);
					}
				case 2:
					$data = array();
					foreach ($result as $v) {
						$data[$v[$field[0]]] = $v[$field[1]];
					}
					return $data;
				default:
					$data = array();
					foreach ($result as $v) {
						$data[$v[$field[0]]] = $v;
					}
					return $data;
			}

		}else{
			return array();
		}	
	}
	/**
	 *  查询字段处理
	 * @param  [type]  $data    [description]
	 * @param  boolean $exclude 是否为排除的字段
	 * @return [type]           [description]
	 */
	public function field($data,$exclude=false)
	{
		if(empty($data)) return;
		//$data是不是数组时
		if(!is_array($data)) $data = explode(',',$data);
		//为排除字段时
		if($exclude)
		{
			$_data = $data;
			$fieldData = $this->opt['fieldData'];
			foreach ($_data as $name => $field) {
				if(isset($fieldData[$field])){
					unset($fieldData[$field]);
				}
			}
			$data = array_keys($fieldData);
		}
		$field = '';
		foreach ($data as $name => $d) {
			if(is_string($name)){
				$field .= $name . ' AS '.$d .',';
			}else{
				$field .= $d.',';
			}
		}
		$this->opt['field'] = substr($field,0,-1);
	}
	/**
	 * 查找记录
	 * @param  string $where 条件
	 * @return array
	 */
	public function select($where = '')
	{
		$this->where($where);
		//组合sql语句
		$sql = 'SELECT '.$this->opt['field'].' FROM '.$this->opt['table'].$this->opt['where'] . $this->opt['group'] . $this->opt['having'] . $this->opt['order'] . $this->opt['limit'];
		return $this->query($sql);
	}
	/**
	 * SQL查询条件
	 * @param  mixed $opt 链式操作中的where参数
	 * @return string
	 */
	public function where($opt)
	{
		$where = '';
		if(empty($opt)) return;
		if(is_numeric($opt)){
			$where .= ' '.$this->opt['pri']."=$opt";
		}elseif(is_string($opt)){
			$where .= " $opt ";
		}elseif(is_array($opt)){
			foreach ($opt as $key => $set) {
				if($key[0] == '_'){
					switch (strtolower($key)) {
						case '_query':
							parse_str($set,$q);
							$this->where($q);
							break;
						case '_string':
							$set = preg_match('@(AND|OR|XOR)\s*@i', $set) ? $set : $set." AND ";
							$where .= $set;
							break;
					}
				}elseif(is_numeric($key)){
					if(!preg_match('@(AND|OR|XOR)\s*$@i',$set)){
						$set .= isset($opt['_logic']) ? " {$opt['_logic']} " : " AND ";
					}
					$where .= $set;
				}elseif(is_string($key)){
					if(!is_array($set)){
						$logic = isset($opt['_logic']) ? $opt['_logic'] : ' AND ';
						$where .= " $key " . "='$set' ".$logic;
					}else{
						$logic = isset($opt['_logic']) ? $opt['_logic'] : ' AND ';
						$logic = isset($set['_logic']) ? $set['_logic'] : $logic;
						//连接方式
						if(is_string(end($set)) && in_array(strtoupper(end($set)),array('AND','OR','XOR'))){
							$logic = ' '.current($set).' ';
						}
						reset($set);//指针回位
						//如 $map['username']=array(array('gt',3),array('lt',5),'AND');
						if(is_array(current($set))){
							foreach ($set as $exp) {
								if(is_array($exp)){
									$t[$key] = $exp;
									$this->where($t);
									$this->opt['where'] .= strtoupper($logic);
								}
							}
						}else{
							$option = !is_array($set[1]) ? explode(',',$set) : $set[1];
							switch (strtoupper($set[0])) {
								case 'IN':
									$value = '';
									foreach ($$option as $v) {
										$value .=is_numeric($v) ? $v.',' : "'$v',";
									}
									$value = trim($value,',');
									$where .= " $key "." IN ($value) $logic";
									break;
								case 'NOTIN':
									$value = '';
									foreach ($option as $v) {
										$value .=is_numeric($v) ? $v.',' : "'$v',";
									}
									$value = trim($value,',');
									$where .= " $key "." NOT IN ($value) $logic";
									break;
								case 'BETWEEN':
									$where .= " $key "." BETWEEN ".$option[0]." AND ".$option[1].$logic;
									break;
								case 'NOTBETWEEN':
									$where .= " $key "." NOT BETWEEN ".$option[0]." AND ".$option[1].$logic;
									break;	
								case 'LIKE':
									foreach ($option as $v) {
										$where .= " $key "." LIKE '$v' ".$logic;
									}
									break;
								case 'NOTLIKE':
									foreach ($option as $v) {
										$where .= " $key "." NOT LIKE '$v' ".$logic;
									}
									break;
								case 'EQ':
									$where .= " $key =".(is_numeric($set[1]) ? $set[1] : "'{$set[1]}'").$logic;
									break;		
								case 'NEQ':
                                    $where .= " $key " . '<>' . (is_numeric($set[1]) ? $set[1] : "'{$set[1]}'") . $logic;
                                    break;
                                case 'GT':
                                    $where .= " $key " . '>' . (is_numeric($set[1]) ? $set[1] : "'{$set[1]}'") . $logic;
                                    break;
                                case 'EGT':
                                    $where .= " $key " . '>=' . (is_numeric($set[1]) ? $set[1] : "'{$set[1]}'") . $logic;
                                    break;
                                case 'LT':
                                    $where .= " $key " . '<' . (is_numeric($set[1]) ? $set[1] : "'{$set[1]}'") . $logic;
                                    break;
                                case 'ELT':
                                    $where .= " $key " . '<=' . (is_numeric($set[1]) ? $set[1] : "'{$set[1]}'") . $logic;
                                    break;
                                case 'EXP':
                                    $where .= " $key " . $set[1] . $logic;
                                    break;
							}
						}
					}
				}
			}
		}
		if(!empty($where)){
			//删除末尾连接符号
			$where = preg_replace('@(OR|AND|XOR})\s$@i','',$where);
			if(empty($this->opt['where'])){
				//第一次设置where
				$this->opt['where'] = " WHERE ".$where;
			}elseif(preg_match('@(OR|AND|XOR})\s$@i',$this->opt['where'])){
				$this->opt['where'] .= $where;
			}else{
				$this->opt['where'] .= ' AND '.$where;
			}
		}else{
			$this->opt['where'] = preg_replace('@(OR|AND|XOR)\s*$@i', '', $this->opt['where']);
		}
	}
	/**
	 * 插入数据
	 * REPLACE方法如果存在插入记录相同的主键护或unique字段进行更新操作
	 * @param  array  $data 数据
	 * @param  string $type 类型 INSERT REPLACE
	 * @return array|bool
	 */
	public function insert($data=array(),$type='INSERT')
	{
		$value = $this->formatField($data);
		//数据不能为空
		if(!$value){
			$this->optInit();
			return false;
		}
		$sql = $type ." INTO ".$this->opt['table']."(".implode(',',$value['fields']).")"."VALUES(".implode(',',$value['values']).")";
		return $this->exe($sql);
	}
	/**
	 * REPLACE更新表
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function replace($data = array())
	{
		return $this->insert($data,'REPLACE');
	}
	/**
	 * 更新数据
	 * @param  array  $data [description]
	 * @return [type]      [description]
	 */
	public function update($data=array())
	{
		//更新必须有条件 如果数据中有主键则以主键做为条件
		if(empty($this->opt[$where])){
			if(isset($data[$this->opt['pri']])){
				$this->opt['where'] = " WHERE ".$this->opt['pri']." = ".intval($data[$this->opt['pri']]);
			}else{
				return false;
			}
		}
		//数据处理
		$data = $this->formatField($data);
		//没有更新数据
		if(empty($data)){
			$this->optInit();
			return false;
		}
		$sql = "UPDATE ".$this->opt['table']." SET ";
		foreach ($data['fields'] as $n => $field) {
			$sql .= $field . "=" . $data['values'][$n] . ',';
		}
		$sql = trim($sql,',') . $this->opt['where'] . $this->opt['limit'];
		return $this->exe($sql);
	}
	/**
	 * 删除数据
	 * @param  string $where 删除条件
	 * @return [type]        [description]
	 */
	public function delete($where='')
	{
		 $this->where($where);
		 if(empty($this->opt['where'])){
		 	$this->optInit();
		 	return false;
		 }
		 $sql = "DELETE FROM ".$this->opt['table'].$this->opt['where'].$this->opt['limit'];
		 return $this->exe($sql);
	}
	public function limit($data)
	{
		$this->opt['limit'] = " LIMIT $data ";
	}
	public function order($data)
	{
		$this->opt['order'] = " ORDER BY $data ";
	}
	/**
     * 分组操作
     * @param type $opt
     */
    public function group($opt)
    {
        $this->opt['group'] = " GROUP BY $opt";
    }
    /**
     * 分组条件having
     * @param type $opt
     */
    public function having($opt)
    {
        $this->opt['having'] = " HAVING $opt";
    }
    //设置查询缓存时间
    public function cache($time = -1)
    {
    	$this->opt['cacheTime'] = $time;
    }
    /**
     * 判断表中字段是否存在
     * @param  [type] $fielname 字段名
     * @param  [type] $table    表名
     * @return [type]           [description]
     */
    public function fieldExists($fielname,$table)
    {
    	$field = $this->query("DESC ".C("DB_PREFIX").$table);
    	foreach ($field as $f) {
    		if(strtolower($f['Field']) == strtolower($fielname)){
    			return true;
    		}
    	}
    	return false;
    }
    /**
     * 判断表是否存在
     * @param  [type] $tableName [description]
     * @return [type]            [description]
     */
    public function tableExists($tableName)
    {
    	$tableArr = $this->query("SHOW TABLES");
    	foreach ($tableArr as $k => $table) {
    		$tableTrue = $table['Table_in'.C('DB_DATABASE')];
    		if(strtolower($tableTrue) == strtolower($tableName)){
    			return true;
    		}
    	}
    	return false;
    }
    /**
     * 统计
     */
    public function count($field ='*')
    {
    	$sql =" SELECT count($field) As c FROM ".$this->opt['table'].$this->opt['where'] . $this->opt['group'] . $this->opt['having'] .$this->opt['order'] . $this->opt['limit'];
    	$data = $this->query($sql);
    	return $data ? $data[0]['c'] : $data;
    }
    /**
     * 求最大值
     * @param  [type] $field 字段
     * @return [type]        [description]
     */
    public function max($field)
    {
    	$sql ="SELECT max($field) as c FROM ".$this->opt['table'].$this->opt['where'] . $this->opt['group'] . $this->opt['having'] .$this->opt['order'] . $this->opt['limit'];
    	$data = $this->query($sql);
    	return $data ? $data[0]['c'] : $data;
    }
    public function min($field)
    {
    	$sql ="SELECT min($field) as c FROM ".$this->opt['table'].$this->opt['where'] . $this->opt['group'] . $this->opt['having'] .$this->opt['order'] . $this->opt['limit'];
    	$data = $this->query($sql);
    	return $data ? $data[0]['c'] : $data;
    }
    public function avg($field)
    {
    	$sql ="SELECT avg($field) as c FROM ".$this->opt['table'].$this->opt['where'] . $this->opt['group'] . $this->opt['having'] .$this->opt['order'] . $this->opt['limit'];
    	$data = $this->query($sql);
    	return $data ? $data[0]['c'] : $data;
    }
    public function sum($field)
    {
    	$sql ="SELECT sum($field) as c FROM ".$this->opt['table'].$this->opt['where'] . $this->opt['group'] . $this->opt['having'] .$this->opt['order'] . $this->opt['limit'];
    	$data = $this->query($sql);
    	return $data ? $data[0]['c'] : $data;
    }
    /**
     * 字段值增加
     * @param  [type]  $field 字段名
     * @param  [type]  $where 条件
     * @param  integer $step  增加数
     * @return [type]         [description]
     */
    public function inc($field,$where,$step=1)
    {
    	$sql = "UPDATE ".$this->opt['table']." SET ".$field."=".$field.'+'.$step+" WHERE " . $where;
    	return $this->exe($sql);
    }
    /**
     * 字段值减少
     * @param  [type]  $field 字段名
     * @param  [type]  $where 条件
     * @param  integer $step  减少数
     * @return [type]         [description]
     */
    public function dec($field,$where,$step=1)
    {
    	$sql = "UPDATE ".$this->opt['table']." SET ".$field."=".$field.'-'.$step+" WHERE " . $where;
    	return $this->exe($sql);
    }
    /**
     * 创建数据库
     * @param  [type] $data    [description]
     * @param  string $charset [description]
     * @return [type]          [description]
     */
    public function createDatabase($database,$charset='utf-8')
    {
    	return $this->exe("CREATE DATABASE IF NOT EXISTS `$database` CHARSET ".$charset);
    }
    /**
     * 删除表
     * @param  [type] $table [description]
     * @return [type]        [description]
     */
    public function dropTable($table)
    {
    	return $this->exe("DROP TABLE IF EXISTS `".C('DB_PREFIX').$table."`");
    }
    /**
     * 修复数据表
     * @param  [type] $table [description]
     * @return [type]        [description]
     */
    public function repair($table)
    {
    	return $this->exe("REPAIR TABLE `".C('DB_PREFIX').$table."`");
    }
    /**
     * 修改表名
     * @param  [type] $old [description]
     * @param  [type] $new [description]
     * @return [type]      [description]
     */
    public function rname($old,$new)
    {
    	return $this->exe('ALTER TABLE `'.C('DB_PREFIX').$old.'` RENAME '.C('DB_PREFIX').$new);
    }
    /**
     * 优化表 解决表碎片化问题
     * @param  [type] $table 表
     * @return [type]        [description]
     */
    public function optimize($table)
    {
    	$this->exe("OPTIMIZE TABLE `".C('DB_PREFIX').$table."`");
    }
    /**
     * 清空数据表
     * @param  [type] $table [description]
     * @return [type]        [description]
     */
    public function truncate($table)
    {
		$this->exe("TRUNCATE TABLE `".C('DB_PREFIX').$table."`");
    }
    /**
     * 判断表名是否存在
     * @param  [type]  $table [description]
     * @return boolean        [description]
     */
    public function isTable($table)
    {
    	$table = C('DB_PREFIX').$table;
    	$info = $this->query("SHOW TABLES");
    	 foreach ($info as $v) {
    	 	if($table == current($v)) return true;
    	 }
    	 return false;
    }
    /**
     * 获得最后一天sql语句
     * @return [type] [description]
     */
    public function getLastSql()
    {
    	return array_pop(Debug::$sqlExeArr)l
    }
    /**
     * 获得所有sql语句
     * @return [type] [description]
     */
    public function getAllSql()
    {
    	return Debug::$sqlExeArr;
    }
    /**
     * 将sql语句压入到调试数组
     * @param  [type] $sql [description]
     * @return [type]      [description]
     */
    protected function recordSql($sql)
    {
    	if(!preg_match('/\s*show/'),$sql)
    	{
    		Debug::$sqlExeArr[] = $sql;
    	}
    }
    //错误处理信息
    protected function error($error)
    {
    	$this->error = $error;
    	if(DEBUG){
    		halt($this->error);
    	}else{
    		log_write($this->error);
    	}
    }
    /**
     * 获得所有表信息
     * @return [type] [description]
     */
    public function getAllTableInfo()
    {
    	$info = $this->query("SHOW TABLE STATUS FROM".C('DB_DATABASE'));
    	$arr = array();
    	$arr['total_size'] = 0;//总大小
    	$arr['total_row'] = 0;//总条数
    	foreach ($info as $k => $t) {
    		$arr['table'][$t['Name']]['tablename'] = $t['Name'];
    		$arr['table'][$t['Name']]['engine'] = $t['Engine'];
    		$arr['table'][$t['Name']]['rows'] = $t['Rows'];
            $arr['table'][$t['Name']]['collation'] = $t['Collation'];
            $charset = $t['Collation'];
            $charset = explode('_',$charset);
            $arr['table'][$t['Name']]['charset']=$charset[0];
            $arr['table'][$t['Name']]['dataFree'] = $t['Data_free'];//碎片大小
            $arr['table'][$t['Name']]['indexSize'] = $t['Index_length'];//索引大小
            $arr['table'][$t['Name']]['dataSize'] = $t['Data_length'];//数据大小
            $arr['table'][$t['Name']]['totalSize'] = $t['Data_free'] + $t['Data_length'] + $t['Index_length'];
            $fieldData = $this->getAllField($t['Name'],true);
            $arr['table'][$t['Name']]['field'] = $fieldData;
            $arr['table'][$t['Name']]['primaryKey'] = $this->getPrimaryKey($t['Name'],true);
            $arr['table'][$t['Name']]['autoincrement'] = $t['Auto_increment'] ? $t['Auto_increment'] : '';
            $arr['total_size'] += $arr['table'][$t['Name']]['dataSize'];
            $arr['total_row'] += $t['Rows'];
    	}
    	return $arr;
    }
    /**
     * 获得数据库的大小
     * @return [type] [description]
     */
    public function getDataBaseSize()
    {
    	$sql = "show table status from".C('DB_DATABASE');
    	$data = $this->query($sql);
        $size = 0;
        foreach ($data as $v) {
            $size += $v['Data_length'] + $v['Data_free'] + $v['Index_length'];;
        }
        return $size;
    }
    /**
     * 获得数据表大小
     * @param $table 表名
     * @return mixed
     */
    public function getTableSize($table)
    {
        $table = C('DB_PREFIX') . $table;
        $sql = "show table status from " . C("DB_DATABASE");
        $data = $this->query($sql);
        foreach ($data as $v) {
            if ($v['Name'] == $table) {
                return $v['Data_length'] + $v['Index_length'];
            }
        }
        return 0;
    }
	/**
	 * 格式化SQL操作参数，字段加上标识符 值进行转义
	 * @param  [type] $vars 数据
	 * @return [type]       [description]
	 */
	public function formatField($vars)
	{
		//格式化的数据
		$data = array();
		foreach ($vars as $k => $v) {
			//校验字段与数据
			if($this->isField($k)){
				$data['fields'][] = "`$k`";
				$v = $this->escapeString($v);
				$data['values'][] =is_numeric($v) ? $v : "\"$v\"";
			}
		}
		return $data;
	}
	/**
	 * 检验是否为表字段
	 * @param  string  $field 字段名
	 * @return boolean 
	 */
	public function isField($field)
	{
		return isset($this->opt['fieldData'][$field]);
	}
	/**
	 * 删除表中所有数据
	 * @param  string $table 数据表
	 * @return mixed
	 */
	public function delAll($table)
	{
		return $this->exe("DELETE FROM ".C('DB_PREFIX').$table)
	}
	/**
	 * 表关联
	 * @param  [type] $join [description]
	 * @return [type]       [description]
	 */
	public function join($join)
	{
		$join = preg_replace('@__(\w+)__@',C('DB_PREFIX').'\1',$join);
		$this->opt['table'] = $join;
	}
	/**
	 * 查询初始化
	 * @return [type] [description]
	 */
	protected function optInit()
	{
		$this->cacheTime = -1;//SELECT查询缓存时间
		$this->error = null;
		$opt = array(
				'table'=>$this->table,
				'pri'=>$this->pri,
				'field'=>'*',
				'fieldData'=>$this->fieldData,
				'where'=>'',
				'like' => '',
	            'group' => '',
	            'having' => '',
	            'order' => '',
	            'limit' => '',
	            'cacheTime' => null,//查询缓存时间
			);
		return $this->opt = array_merge($this->opt,$opt);
	}
	/**
	 * 获得表主键结构 获得所有字段用于字段缓存
	 * @param  [type] $table [description]
	 * @param  [type] $full  [description]
	 * @return [type]        [description]
	 */
	protected function getAllField($table,$full=false)
	{
		if(!$full){
			$table = C('DB_PREFIX') .$table;
		}
		$name = C('DATABASE') . $table;
		//字段缓存
		if(!DEBUG && F($name,false,APP_TABLE_PATH)){
			$fieldData = F($name,false,APP_TABLE_PATH);
		}else{
			$sql = "show columns from `$table`";
			if(!$result = $this->query($sql)){
				return false;
			}
			$fieldData = array();
			foreach ($result as $res) {
				$f['field'] = $res['Field'];
				$f['type'] = $res['Type'];
				$f['null'] = $res['Null'];
				$f['key'] = $res['Key'] == 'PRI';
				$f['default'] = $res['Default'];
				$f['extra'] = $res['Extra'];
				$fieldData[$res['field']] = $f;
			}
			DEBUG || F($name,$fieldData,APP_TABLE_PATH);
		}
		return $fieldData;
	}
	protected function getPrimaryKey($table,$full)
	{
		$fieldData = $this->getAllField($table,$full);
		$pri = '';
		foreach ($fieldData as $field => $v) {
			if($v['key'] == 1){
				$pri = $field;
				break;
			}
		}
		return $pri;
	}
}