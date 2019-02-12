<?php
namespace app\admin\controller;

use think\Request;

class Shipping extends Common
{
    public function index(){
        //获取分页信息
        $data = fpage(db('shipping'), $where='', 'shipping_id desc', 5);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);
        //分配送货方式启用状态
        $enabled = array('0'=>'禁用','1'=>'启用');
        $this->assign('enabled',$enabled);
        $this->setPageInfo('送货方式列表','添加送货方式',url('add'));
        return $this->fetch();
    }

    //添加送货方式
    public function add(){
        if (Request::instance()->isPost()) {
            $shipping = db('shipping');

            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("shipping")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $info = $shipping->where('shipping_name', $data['shipping_name'])->find();
            if($info){
                $this->error("该名称已存在，不能重复添加！");
            }

            //剩下操作
            $res = $shipping->insert($data);
            if($res){
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        } else {
            $this->setPageInfo('添加送货方式','送货方式列表',url('index'));
            return $this->fetch();
        }
    }

    //修改送货方式
    public function edit(){
        $shipping = db('shipping');
        if (Request::instance()->isPost()) {
            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("shipping")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $map['shipping_name'] = $data['shipping_name'];
            $map['shipping_id'] = array('neq',$data['shipping_id']);
            $info = $shipping->where($map)->find();
            if($info){
                $this->error("该名称已存在！");
            }

            //剩下操作
            $res = $shipping->update($data);
            if($res){
                $this->success('编辑成功', url('index'));
            }else{
                $this->error("编辑失败！");
            }
        } else {
            $id = input('param.id');
            //根据id获取用户信息
            $info = $shipping->find($id);
            if(!$info){
                $this->error("参数错误！");
            }
            $this->assign('info', $info);
            $this->setPageInfo('编辑送货方式信息','送货方式列表',url('index'));
            return $this->fetch();
        }
    }

    //删除送货方式
    public function del(){
        $id = input('param.id');
        //执行删除
        db('shipping')->delete($id);
        $this->redirect('index');
    }
}