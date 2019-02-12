<?php
namespace app\admin\model;

use think\Model;

class User extends Model
{
    public function orderUser(){
        //获取用户所有信息，只要id和name字段
        $users = $this->field('user_id,username')->select();
        $data = array();
        //id和name字段拼成一个数组
        foreach($users as $v){
            $data[$v['user_id']] = $v['username'];
        }
        return $data;
    }
}