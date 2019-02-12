<?php
namespace app\index\model;

use think\Model;

class User extends Model
{
    public function checkNamePwd($name,$pwd){
        //①首先验证名字，然后验证密码
        $info = $this->where("username='$name'")->find();
        if($info!==null){
            //验证密码
            if($info['password']===md5($pwd)){
                return $info;
            }
        }
        return false;
    }
}