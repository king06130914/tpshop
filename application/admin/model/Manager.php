<?php
namespace app\admin\model;

use think\Model;

class Manager extends Model
{
    public function checkNamePwd($name,$pwd){
        //①首先验证名字，然后验证密码
        $info = $this->where("mg_name='$name'")->find()->toArray();
        if($info!==null){
            //验证密码
            if($info['mg_pwd']===md5($pwd)){
                return $info;
            }
        }
        return false;
    }
}