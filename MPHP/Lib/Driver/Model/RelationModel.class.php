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
* 关联模型
*/
//一对一
defined("HAS_ONE") or define("HAS_ONE", "HAS_ONE");
//一对多 主表 VS 从表  用户表（主表）  VS  用户信息表
defined("HAS_MANY") or define("HAS_MANY", "HAS_MANY");
//一对多  从表 VS 主表 用户信息表（主表） VS  用户表
defined("BELONGS_TO") or define("BELONGS_TO", "BELONGS_TO");
//多对多
defined("MANY_TO_MANY") or define("MANY_TO_MANY", "MANY_TO_MANY");
class RelationModel extends Model 
{
	//关联模型定义
	public $relation = array();
	//关联查询
	public function select($data=array())
	{
		$result = call_user_func(array($this->db,__FUNCTION__),$data);
		//重置模型
		$this->reset();
		//主表查询没有结果或失败
		if(!$result)
		{
			return $result;
		}
		//关联处理操作
		foreach ($this->relation as $table => $value) {
			//主表关联字段
			$pk = $set['parent_key'];
			//从表关联字段
			$fk = $set['foreign_key'];
			//从表模型对象
			$db = M($table);
			//附表设置了字段定义
			$field = isset($set['field']) ? $set['field'] : '';
			switch ($set['type']) {
				case HAS_ONE:
					//一对一
					foreach ($result as $n => $d) {
						$s = $db->field($field)->where($fk .'='. $d[$pk])->find();
						if(is_array($s)){
							$result[$n] = array_merge($d,$s);
						}
					}
					break;
				case HAS_MANY:
					//一对多
					foreach ($result as $n => $d) {
						$s = $db->field($field)->where($fk .'='. $d[$pk])->all();
						if(is_array($s)){
							$result[$n][$table] = $s;
						}
					}
					break;
				case BELONGS_TO:
					foreach ($result as $n => $d) {
                        $s = $db->field($field)->where($fk . '=' . $d[$pk])->find();
                        if (is_array($s)) {
                            $result[$n] = array_merge($d, $s);
                        }
                    }
					break;
				case MANY_TO_MANY:
					foreach ($result as $n => $d) {
						$s = $db->table($set['relation_table'])->where($pk.'='.$d[$pk])->getField($fk,true);
						if(is_array($s)){
							$map[$fk] = array('IN',$s);
							$result[$n][$table] = $db->table($table)->where($map)->all();
						}
					}
					break;
			}
		}
		return $result;
	}
	/**
	 * 关联擦插入
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function insert($data=array())
	{
		$this->data($data);
		$insertData = $this->data;
		//插入主表数据
		$pid = call_user_func(array($this->db,__FUNCTION__),$insertData);
		/**
         * 重置模型
         */
        $this->__reset();
        //插入失败或者没有定义关联join属性
        if (!$pid) {
            return $pid;
        }
        //记录操作结果
      	$result = array();
      	$result[$this->table] = $pid;
      	//处理表关联
      	foreach ($this->relation as $table => $set) {
      		//没有从表更新数据时不操作
      		if(empty($insertData[$table])){
      			continue;
      		}
      		//主表字段
      		$pk = $set['parent_key'];
      		//从表终端
      		$fk = $set['foreign_key'];
      		//从表模型
      		$db = M($table);
      		switch ($set['type']) {
      			case HAS_ONE:
      			case HAS_MANY:
      				//从表插件数据中添加主键值
      				$insertData[$table][$fk] = $pid;
      				$result[$table] = $db->insert($insertData[$table]);
      				break;
      			case BELONGS_TO:
      				//因为是BELONGS_TO 关系 先插入从表数据
      				$fid = $db->insert($insertData[$table]);
      				//更新主表数据
      				$db->table($this->table)->where($pk.'='.$pid)->update(array($pk=>$fid));
      				$result[$table] = $fid;
      				break;
      			case MANY_TO_MANY:
      				//向从表中插入数据
      				$fid = $db->insert($insertData[$table]);
      				$result[$table] = $fid;
      				//中间表插入数据
      				$db->table($set['relation_table'])->insert(array($pk=>$pid,$fk=>$fid));
      				break;
      		}
      	}
      	return $result;
	}
	/**
	 * 更新数据
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function update($data = array())
	{
		$this->data($data);
		$updateData = $this->data;
		//更新主表数据
		$status = call_user_func(array($this->db,__FUNCTION__),$updateData);
		/**
         * 重置模型
         */
        $this->__reset();
        /**
         * 主表更新
         */
        if (!$status) {
            return $status;
        }
        //主表更新id
        $pid = $updateData[$this->db->pri];
        //处理关联
        foreach ($this->relation as $table => $set) {
        	//没有更新数据时不操作
        	if(empty($updateData)){
        		continue;
        	}
        	//从表字段
        	$fk = $set['foreign_key'];
        	//从表模型
        	$db = M($table);
        	switch ($set['type']) {
        		case HAS_ONE:
        			$db->where("$fk=$pid")->update($insertData[$table]);
        			break;
        		case HAS_MANY:
        		case BELONGS_TO:
        		case MANY_TO_MANY:
        			$db->update($insertData[$table]);
        			break;
        	}
        }
        return $status;
	}
	/**
	 * 关联
	 * @param  array  $data 删除条件
	 * @return [type]       [description]
	 */
	public function delete($data=array())
	{
		//条件
		$this->where($data);
		$where = preg_replace('/^\s+where/i','',$this->db->opt['where']);
		//主表数据
		$parentData = $this->where($where)->select();
		//删除主表数据
		$status = call_user_func(array($this->db, __FUNCTION__), $where);
		/**
         * 重置模型
         */
        $this->__reset();
        //主表无数据返回
        if(!$this->db->getAffectedRows)
        {
        	return true;
        }
        /**
         * 删除主表失败
         */
        if (!$status) {
            return $status;
        }
        foreach ($this->relation_table as $table => $set) {
        	/**
             * 主表字段
             */
            $pk = $set['parent_key'];
            /**
             * 从表字段
             */
            $fk = $set['foreign_key'];
            /**
             * 从表模型
             */
            $db = M($table);
            switch ($set['type']) {
            	case HAS_ONE:
            	case HAS_MANY:
            		foreach ($parentData as $p) {
            			$db->where($fk.'='.$p[$pk])->delete();
            		}
            		break;
            	case BELONGS_TO:
            		break;
            	case MANY_TO_MANY:
            		foreach ($parentData as $p) {
            			$db->table($set['realtion_table'])->where($pk.'='.$p[$pk])->delete();
            		}
            		break;
            }
        }
        return $status;
	}
}