<?php
namespace app\admin\model;

use think\Model;

class Role extends Model
{
    //获取全部角色信息
    public function getRoleInfo(){
        $rrinfo = $this->select();
        $rinfo = array();
        foreach($rrinfo as $v){
            $rinfo[$v['role_id']] = $v['role_name'];
        }
        return $rinfo;
    }

    public function saveAuth($authid, $roleid){
        //① 根据 $authid 制作ids字符串
        $auth_ids = implode(',',$authid);
        //① 根据 $authid 获得对应的控制器和操作方法
        $map['auth_id'] = array('in', $auth_ids);
        $auth_info = db('auth')->where($map)->select();
        //拼装控制器和操作方法
        $s = '';
        foreach($auth_info as $v){
            if(!empty($v['auth_a']))
                $s .= $v['auth_c']."-".$v['auth_a'].",";
        }
        //去除右侧逗号
        $s = rtrim($s,',');

        $data = array(
            'role_auth_ids' => $auth_ids,
            'role_auth_ac' => "$s",
        );
        return $this->where('role_id', $roleid)->update($data);
    }
}