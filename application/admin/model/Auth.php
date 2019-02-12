<?php
namespace app\admin\model;

use think\Model;

class Auth extends Model
{
    public function saveData($authinfo){
        //两个步骤
        //①根据已有的四个字段生成一条记录
        $newid = $this->insertGetId($authinfo);
        //②根据新纪录的主键id值进一步制作auth_path和auth_level
        //auth_path处理：顶级/非顶级权限
        if($authinfo['auth_pid']==0){
            //顶级
            $path = $newid;
        }else{
            //非顶级全路径：上级权限全路径-本身记录id值
            $pinfo = $this->find($authinfo['auth_pid']);
            $p_info = $pinfo['auth_path'];
            $path = $p_info.'-'.$newid;
        }

        //auth_level处理：全路径变成数组后元素个数减一的结果
        $level = count(explode('-',$path))-1;

        $data = array(
            'auth_path' => $path,
            'auth_level' => $level,
        );
        return $this->where('auth_id', $newid)->update($data);
    }

    //自定义方法实现权限信息的修改
    public function modData($authinfo){
        $auth_id = $authinfo['auth_id'];
        //根据新纪录的主键id值进一步制作auth_path和auth_level
        //auth_path处理：顶级/非顶级权限
        if($authinfo['auth_pid']==0){
            //顶级
            $path = $auth_id;
            $auth_c = '';
            $auth_a = '';
        }else{
            //非顶级全路径：上级权限全路径-本身记录id值
            $pinfo = $this->find($authinfo['auth_pid']);
            $p_info = $pinfo['auth_path'];
            $path = $p_info.'-'.$auth_id;

            $auth_c = $authinfo['auth_c'];
            $auth_a = $authinfo['auth_a'];
        }

        //auth_level处理：全路径变成数组后元素个数减一的结果
        $level = count(explode('-',$path))-1;
        //获取修改数据
        $data = array(
            'auth_name' => $authinfo['auth_name'],
            'auth_pid' => $authinfo['auth_pid'],
            'auth_c' => $auth_c,
            'auth_a' => $auth_a,
            'auth_path' => $path,
            'auth_level' => $level,
        );
        //执行修改
        return $this->where('auth_id', $auth_id)->update($data);
    }

    //查询所有的父id，判断删除的分类下是否有子分类
    public function getAllPid(){
        $auth_pid = $this->field('auth_pid')->select();
        $pid_list = array();
        foreach($auth_pid as $v){
            $pid_list[] = $v['auth_pid'];
        }
        return $pid_list;
    }
}