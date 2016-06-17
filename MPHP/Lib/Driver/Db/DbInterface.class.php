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
* 数据库操作接口
*/
interface DbInterface
{
	public function connect();//获得连接
	public function close();//关闭数据库
	public function exe($sql);//发送没有结果集的sql
	public function query($sql);//发送有结果集的sql
	public function getInsertId();//获得最后插入ID
	public function getAffectedRows();//s受影响行数
	public function getVersion();//获得版本
	public function beginTrans();//自动提交模式true开启 false关闭
	public function commit();//提交事务
	public function rollback();//回滚事务
	public function escapeString($str);//数据安全处理
	public function link($table,$full);//获得连接
	public function table($table,$full=null);//设置表
}