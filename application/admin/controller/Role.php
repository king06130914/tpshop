<?php
namespace app\admin\controller;

use think\Request;

class Role extends Common
{
    //角色列表
    public function index()
    {
        //判断是否有查询
        $where = array();
        if(!empty($_GET['role_id'])){
            $where['role_id'] = input('get.role_id');
        }
        $this->assign('search',$where);

        //获取分页信息
        $data = fpage(db('role'), $where, 'role_id desc', 5);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);
        $this->assign('roleInfo', db('role')->select());
        $this->setPageInfo('角色管理','添加角色', url('add'));
        return $this->fetch();
    }

    //添加角色
    public function add()
    {
        $role = db('role');
        if (Request::instance()->isPost()) {
            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("role")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $info = $role->where('role_name', $data['role_name'])->find();
            if($info){
                $this->error("该角色已存在，不能重复添加！");
            }

            //剩下操作
            $res = $role->insert($data);
            if($res){
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        } else {
            //获取角色信息
            $roleInfo = $role->select();
            // 模板变量赋值
            $this->assign('roleInfo', $roleInfo);
            $this->setPageInfo('添加角色','角色列表',url('index'));
            return $this->fetch();
        }
    }

    //修改角色
    public function edit(){
        $role = db('role');
        if (Request::instance()->isPost()) {
            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("role")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证角色唯一性
            $map['role_name'] = $data['role_name'];
            $map['role_id'] = array('neq',$data['role_id']);
            $info = $role->where('role_name', $data['role_name'])->where($map)->find();
            if($info){
                $this->error("该角色已存在！");
            }

            //剩下操作
            $res = $role
                ->where('role_id', $data['role_id'])
                ->update($data);
            if($res){
                $this->success('编辑成功', url('index'));
            }else{
                $this->error("编辑失败！");
            }
        } else {
            $id = input('param.id');
            //根据id获取用户信息
            $info = $role->find($id);
            if(!$info){
                $this->error("参数错误！");
            }
            $this->assign('info', $info);
            $this->setPageInfo('编辑管理员信息','管理员列表',url('index'));
            return $this->fetch();
        }
    }

    //删除角色
    public function del()
    {
        $id = input('param.id');
        //执行删除
        db('role')->delete($id);
        $this->redirect('index');
    }

    //给角色分配权限
    public function distribute(){
        $role_id = input('param.id');
        $role = model('role');
        //两个逻辑：展示表单，收集表单
        if(Request::instance()->isPost()){
            if(!isset($_POST['auth_id']) || !isset($_POST['id'])){
                $this->error("系统错误！");
            }

            //调用saveAuth给角色分配权限
            if($role->saveAuth($_POST['auth_id'],$_POST['id'])){
                $this->success('分配权限成功', url('index'));
            }else{
                $this->error("分配权限失败！");
            }
        }else{
            //获得被分配权限角色的信息
            $role_info = db('role')->where('role_id', $role_id)->find();
            //把当前角色的权限ids变成数组
            $authidsarr = explode(',',$role_info['role_auth_ids']);

            //获得权限信息
            $auth = db('auth');
            $auth_infoA = $auth->where("auth_level=0")->select();
            $auth_infoB = $auth->where("auth_level=1")->select();

            $this->assign('info',$role_info);
            $this->assign('authidsarr',$authidsarr);
            $this->assign('auth_infoA',$auth_infoA);
            $this->assign('auth_infoB',$auth_infoB);
            $this->setPageInfo('给角色分配权限','角色管理',url('index'));
            return $this->fetch();
        }
    }

}
