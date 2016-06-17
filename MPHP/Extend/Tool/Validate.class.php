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
* 表单验证类
*/
class Validate
{
	//不为空
	public function _nonull($name,$value,$msg,$arg)
	{
		if(empty($value)){
			return $msg;
		}else{
			return true;
		}
	}
	//验证邮箱
	public function _email($name,$value,$msg,$arg)
	{
		$preg = "/^\w+(\.\w+)*@\w+(\.\w+)+$/i";
		if(preg_match($preg,$value)){
			return true;
		}else{
			return $msg;
		}
	}
	//最大长度验证
	public function _maxlen($name,$value,$msg,$arg)
	{
		if(mb_strlen($value,'utf-8') <= $arg){
			return true;
		}else{
			return $msg;
		}
	}
	//最小长度
	public function _minlen($name,$value,$msg,$arg)
	{
		if(mb_strlen($value,'utf-8') >= $arg){
			return true;
		}else{
			return $msg;
		}
	}
	//验证网站
	public function _http($name,$value,$msg,$arg)
	{
		$preg = '/^(https?://)?(\w+\.)+[a-z]{2,8}$/i';
		if(preg_match($preg,$value)){
			return true;
		}else{
			return $msg;
		}
	}
	/**
     * 电话号码
     *
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     *
     * @return bool
     */
    public function _tel($name, $value, $msg, $arg)
    {
        $preg = "/(?:\(\d{3,4}\)|\d{3,4}-?)\d{8}/";
        if (preg_match($preg, $value)) {
            return true;
        } else {
            return $msg;
        }
    }

    /**
     * 手机号验证
     *
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     *
     * @return bool
     */
    public function _phone($name, $value, $msg, $arg)
    {
        $preg = "/^\d{11}$/";
        if (preg_match($preg, $value)) {
            return true;
        } else {
            return $msg;
        }
    }

    /**
     * 身份证验证
     *
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     *
     * @return bool
     */
    public function _identity($name, $value, $msg, $arg)
    {
        $preg = "/^(\d{15}|\d{18})$/";
        if (preg_match($preg, $value)) {
            return true;
        } else {
            return $msg;
        }
    }

    /**
     * 用户名验证
     *
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     *
     * @return bool
     */
    public function _user($name, $value, $msg, $arg)
    {
        //用户名长度
        $len = mb_strlen($value, 'utf-8');
        $arg = explode(',', $arg);
        if ($len >= $arg[0] && $len <= $arg[1]) {
            return true;
        } else {
            return $msg;
        }
    }

    /**
     * 数字范围
     *
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     *
     * @return bool
     */
    public function _num($name, $value, $msg, $arg)
    {
        $arg = explode(',', $arg);
        if ($value >= $arg[0] && $value <= $arg[1]) {
            return true;
        } else {
            return $msg;
        }
    }

    /**
     * 正则验证
     *
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     *
     * @return bool
     */
    public function _regexp($name, $value, $msg, $preg)
    {
        if (preg_match($preg, $value)) {
            return true;
        } else {
            return $msg;
        }
    }

    /**
     * 两个表单比对
     *
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     *
     * @return bool
     */
    public function _confirm($name, $value, $msg, $arg)
    {
        if ($value == $_POST[$arg]) {
            return true;
        } else {
            return $msg;
        }
    }

    /**
     * 中文验证
     *
     * @param $name  变量名
     * @param $value 变量值
     * @param $msg   错误信息
     *
     * @return bool
     */
    public function _china($name, $value, $msg, $arg)
    {
        if (preg_match('/^[\x{4e00}-\x{9fa5}a-z0-9]+$/ui', $value)) {
            return true;
        } else {
            return $msg;
        }
    }
}