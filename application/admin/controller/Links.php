<?php
namespace app\admin\controller;

use think\Request;

class Links extends Common
{
    public function index(){
        //判断是否有查询
        $where = '';
        $search = array();
        if(!empty($_GET['title'])){
            $title = input('get.title');
            $where = " title like '%".$title."%'";
            $search['title'] = $title;
        }
        $this->assign('search',$search);

        //获取分页信息
        $data = fpage(db('links'), $where, 'id desc', 5);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);
        $this->setPageInfo('友情链接列表','添加新链接',url('add'));
        return $this->fetch();
    }

    public function add(){
        if (Request::instance()->isPost()) {
            $links = db('links');
            //获取当前时间
            $time = time();

            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("links")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $info = $links->where('title', $data['title'])->find();
            if($info){
                $this->error("该名称已存在，不能重复添加！");
            }

            //验证链接是否是url
            if(!is_url($data['alink'])){
                $this->error("请输入正确的链接地址！");
            };

            //剩下操作
            $data['add_time'] = $time;
            if($links->insert($data)){
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        } else {
            $this->setPageInfo('添加新链接','友情链接列表',url('index'));
            return $this->fetch();
        }
    }

    //修改友情链接
    public function edit(){
        $links = db('links');
        if (Request::instance()->isPost()) {
            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("links")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $map['title'] = $data['title'];
            $map['id'] = array('neq',$data['id']);
            $info = $links->where($map)->find();
            if($info){
                $this->error("该名称已存在！");
            }

            //验证链接是否是url
            if(!is_url($data['alink'])){
                $this->error("请输入正确的链接地址！");
            };

            //剩下操作
            $res = $links->update($data);
            if($res){
                $this->success('编辑成功', url('index'));
            }else{
                $this->error("编辑失败！");
            }
        } else {
            $id = input('param.id');
            //根据id获取用户信息
            $info = $links->find($id);
            if(!$info){
                $this->error("参数错误！");
            }
            $this->assign('info', $info);
            $this->setPageInfo('编辑管理员信息','管理员列表',url('index'));
            return $this->fetch();
        }
    }

    //删除友情链接
    public function del(){
        $id = input('param.id');
        $links = db('links');
        //执行删除
        $links->delete($id);
        $this->redirect('index');

    }

}