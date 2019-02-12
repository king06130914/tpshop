<?php
namespace app\admin\controller;

use think\Request;

class Type extends Common
{
    public function index(){
        $type = db('type');

        //获取分页信息
        $data = fpage($type, $where = array(), 'type_id asc');
        $this->assign('page_list', $data['page_list']);
        $this->assign('info', $data['info']);

        //状态
        $state = array('0' => 'no', '1' => 'yes');
        $this->assign('state', $state);
        $this->setPageInfo('商品类型','添加商品类型',url('add'));
        return $this->fetch();
    }

    //添加商品类型
    public function add(){
        $type = db('type');
        if(Request::instance()->isPost()){
            $data = input("post.");

            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("type")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $info = $type->where('type_name', $data['type_name'])->find();
            if($info){
                $this->error("该名称已存在，不能重复添加！");
            }

            //剩下操作
            if($type->insert($data)){
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        }else{
            $this->setPageInfo('添加商品类型','商品类型',url('index'));
            return $this->fetch();
        }
    }

    //修改商品类型
    public function edit(){
        $type = db('type');
        if (Request::instance()->isPost()) {
            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("type")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $map['type_name'] = $data['type_name'];
            $map['type_id'] = array('neq',$data['type_id']);
            $info = $type->where($map)->find();
            if($info){
                $this->error("该名称已存在！");
            }

            //剩下操作
            $res = $type->update($data);
            if($res){
                $this->success('编辑成功', url('index'));
            }else{
                $this->error("编辑失败！");
            }
        } else {
            $id = input('param.id');
            //根据id获取用户信息
            $info = $type->find($id);
            if(!$info){
                $this->error("参数错误！");
            }
            $this->assign('info', $info);
            $this->setPageInfo('编辑商品类型','商品类型',url('index'));
            return $this->fetch();
        }
    }
}