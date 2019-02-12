<?php
namespace app\admin\controller;


class Index extends Common
{
    //头部展示
    public function top(){
        return $this->fetch();
    }
    //左侧菜单展示
    public function menu(){
        //根据用户的角色显示对应的权限
        //① 根据用户的session id信息获得其角色id
        $manager_info = db('manager')->find(session('admin_id'));
        $role_id = $manager_info['mg_role_id'];
        //② 根据$role_id获得其权限的ids
        $role_info = db('role')->find($role_id);
        $auth_ids = $role_info['role_auth_ids'];
        //③ 根据$auth_ids获得权限的详情
        //给admin开放绝对权限
        if(session('admin_user')==='admin'){
            $auth_infoA = db('auth')->where("auth_level=0")->select();
            $auth_infoB = db('auth')->where("auth_level=1")->select();
        }else{
            $auth_infoA = db('auth')->where("auth_level=0 and auth_id in ($auth_ids)")->select();
            $auth_infoB = db('auth')->where("auth_level=1 and auth_id in ($auth_ids)")->select();
        }

        $this->assign('auth_infoA',$auth_infoA);
        $this->assign('auth_infoB',$auth_infoB);
        return $this->fetch();
    }
    //背景页面展示
    public function drag(){
        return $this->fetch();
    }
    //主页展示
    public function main(){
        $this->setPageInfo('主页','主页展示',url('index/main'));
        return $this->fetch();
    }

    public function index()
    {
        return $this->fetch();
    }

    //清除缓存
    public function clear_cache(){
        //清除temp目录
        $my_files = (array)glob(TEMP_PATH . DS . '/*.php');
        array_map(function($v){ if(file_exists($v)) @unlink($v); }, $my_files);
        $this->success( '清除成功', url('index/main') );
    }
}
