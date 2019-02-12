<?php
namespace app\admin\controller;

use think\Controller;

class Common extends Controller {
    public function _initialize(){
        $request=  \think\Request::instance();
        //实现访问权限控制器过滤功能（防止翻墙访问）
        //①获得当前请求的controller和action
        $nowac = $request->controller()."-".$request->action();
        //②获得当前用户的角色权限
        $adminid = session('admin_id');
        $manager_info = db('manager')->find($adminid);
        $role_id = $manager_info['mg_role_id'];
        $role_info = db('role')->find($role_id);
        $auth_ac = $role_info['role_auth_ac'];

        //禁止未登录用户访问后台
        $ac = "Public-login,Public-verifyImg";//未登录系统也允许访问的操作
        if(empty($adminid) && strpos($ac,$nowac)===false){
            $this->redirect('login/login');
        }

        //A.判断当前请求的controller和action是否在其权限列表组中出现
        //B.不要限制admin用户
        //C.允许开放一些不加限制的权限
        $allowac = "Index-index,Index-menu,Index-drag,Index-main,Index-top";
        $adminname = session('admin_user');
        if(strpos($auth_ac,$nowac)===false && $adminname !== 'admin' && strpos($allowac,$nowac)===false){
            exit('没有权限访问');
        }
    }
    //设置页面头部信息
    public function setPageInfo($page_title,$page_btn_name,$page_btn_link)
    {
        //设置页面
        $this->assign("page_title",$page_title);
        $this->assign("page_btn_name",$page_btn_name);
        $this->assign("page_btn_link",$page_btn_link);
    }
}