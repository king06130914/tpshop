<?php
namespace app\admin\controller;

use think\Request;

class Manager extends Common
{
    //管理员列表
    public function index()
    {
        //判断是否有查询
        $where = array();
        if(!empty($_GET['role_id'])){
            $where['mg_role_id'] = input('get.role_id');
        }
        $this->assign('search', $where);

        //获取全部的角色信息
        $rinfo = model('role')->getRoleInfo();
        $this->assign('rinfo',$rinfo);
        //获取分页信息
        $data = fpage(db('manager'), $where, 'mg_id desc', 5);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);
        $this->assign('roleInfo',db('role')->select());
        $this->setPageInfo('管理员列表','添加管理员',url('add'));
        return $this->fetch();
    }

    //添加管理员
    public function add()
    {
        if (Request::instance()->isPost()) {
            $manager = db('manager');
            //获取当前时间
            $time = time();

            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("manager")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $info = $manager->where('mg_name', $data['mg_name'])->find();
            if($info){
                $this->error("该名称已存在，不能重复添加！");
            }

            //剩下操作
            $data['mg_pwd'] = md5($data['mg_pwd']);
            $data['mg_time'] = $time;
            $res = $manager->insert($data);
            if($res){
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        } else {
            //获取角色信息
            $roleInfo = db('role')->select();
            // 模板变量赋值
            $this->assign('roleInfo', $roleInfo);
            $this->setPageInfo('添加管理员','管理员列表',url('index'));
            return $this->fetch();
        }
    }

    //修改管理员
    public function edit(){
        $manager = db('manager');
        if (Request::instance()->isPost()) {
            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("manager_edit")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $map['mg_name'] = $data['mg_name'];
            $map['mg_id'] = array('neq',$data['mg_id']);
            $info = $manager->where($map)->find();
            if($info){
                $this->error("该名称已存在！");
            }

            //剩下操作
            $res = $manager
                ->where('mg_id', $data['mg_id'])
                ->update($data);
            if($res){
                $this->success('编辑成功', url('index'));
            }else{
                $this->error("编辑失败！");
            }
        } else {
            $id = input('param.id');
            //根据id获取用户信息
            $info = $manager->find($id);
            if(!$info){
                $this->error("参数错误！");
            }
            $this->assign('info', $info);

            //获取角色信息
            $roleInfo = db('role')->select();
            // 模板变量赋值
            $this->assign('roleInfo', $roleInfo);
            $this->setPageInfo('编辑管理员信息','管理员列表',url('index'));
            return $this->fetch();
        }
    }

    //删除管理员
    public function del(){
        $id = input('param.id');

        $manager = db('manager');
        //获取当前要删除的管理员信息
        $man = $manager->find($id);
        //判断要删除的是不是admin
        if($man['mg_name'] == 'admin'){
            echo "<script>alert('超级管理员不能被删除！'); history.back(-1);</script>";
        }else{
            //执行删除
            $manager->delete($id);
            $this->redirect('index');
        }
    }

    //修改密码
    public function pwd(){
        $manager = db('manager');
        if (Request::instance()->isPost()) {
            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("manager_pwd")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //剩下操作
            $res = $manager
                ->where('mg_id', $data['mg_id'])
                ->update(array('mg_pwd' => md5($data['mg_pwd'])));
            if($res){
                $this->success('修改口令成功', url('index'));
            }else{
                $this->error("修改口令失败！");
            }
        } else {
            $this->setPageInfo('修改管理员密码','管理员列表', url('index'));
            return $this->fetch();
        }
    }

}
