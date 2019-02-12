<?php
namespace app\admin\controller;

use think\Request;

class Payment extends Common
{
    //支付方式列表
    public function index(){
        //获取分页信息
        $data = fpage(db('payment'), $where='', 'pay_id desc', 5);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);
        //分配支付方式启用状态
        $enabled = array('0'=>'禁用','1'=>'启用');
        $this->assign('enabled',$enabled);
        $this->setPageInfo('支付方式列表','添加支付方式',url('add'));
        return $this->fetch();
    }

    //添加支付方式
    public function add(){
        if (Request::instance()->isPost()) {
            $payment = db('payment');

            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("payment")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $info = $payment->where('pay_name', $data['pay_name'])->find();
            if($info){
                $this->error("该名称已存在，不能重复添加！");
            }

            //剩下操作
            $res = $payment->insert($data);
            if($res){
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        } else {
            $this->setPageInfo('添加支付方式','支付方式列表',url('index'));
            return $this->fetch();
        }
    }

    //修改支付方式
    public function edit(){
        $payment = db('payment');
        if (Request::instance()->isPost()) {
            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("payment")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $map['pay_name'] = $data['pay_name'];
            $map['pay_id'] = array('neq',$data['pay_id']);
            $info = $payment->where($map)->find();
            if($info){
                $this->error("该名称已存在！");
            }

            //剩下操作
            $res = $payment->update($data);
            if($res){
                $this->success('编辑成功', url('index'));
            }else{
                $this->error("编辑失败！");
            }
        } else {
            $id = input('param.id');
            //根据id获取用户信息
            $info = $payment->find($id);
            if(!$info){
                $this->error("参数错误！");
            }
            $this->assign('info', $info);
            $this->setPageInfo('编辑支付方式信息','支付方式列表',url('index'));
            return $this->fetch();
        }
    }

    //删除支付方式
    public function del(){
        $id = input('param.id');
        //执行删除
        db('payment')->delete($id);
        $this->redirect('index');

    }
}