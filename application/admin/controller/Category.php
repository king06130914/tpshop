<?php
namespace app\admin\controller;

use think\Request;

class Category extends Common
{
    public function index(){
        $category = db('category');

        //判断是否有查询
        $where = array();
        $search = array();
        if(!empty($_GET['cat_name'])){
            $cat_name = input('get.cat_name');
            $where['cat_name'] = array('like',"%{$cat_name}%");
            $search['cat_name'] = $cat_name;
            $this->assign('search',$search);
        }

        //获取分页信息
        $data = fpage($category, $where, 'bpath', 10, '*,concat(cat_path,cat_id) as bpath');
        $this->assign('page_list', $data['page_list']);
        $this->assign('info', $data['info']);
        $this->assign('ge',"--/");
        $this->setPageInfo('商品分类','添加商品分类',url('add'));
        return $this->fetch();
    }

    //添加商品分类
    public function add(){
        $category = db('category');
        if(Request::instance()->isPost()){
            $data = input("post.");

            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("category")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $info = $category->where('cat_name', $data['cat_name'])->find();
            if($info){
                $this->error("该名称已存在，不能重复添加！");
            }

            //剩下操作
            if(model('category')->saveData($data)){
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        }else{
            //获得所有分类信息
            $cat_info = $category->field("*,concat(cat_path,cat_id) as bpath")->order('bpath')->select();
            $this->assign('cat_info',$cat_info);
            $this->assign('ge',"--/");
            $this->setPageInfo('添加商品分类','商品分类',url('index'));
            return $this->fetch();
        }

    }

    //修改分类
    public function edit(){
        $category = db('category');
        //两个逻辑：展示表单，收集表单
        if(Request::instance()->isPost()){
            $data = input("post.");

            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("category")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $map['cat_name'] = $data['cat_name'];
            $map['cat_id'] = array('neq',$data['cat_id']);
            $info = $category->where($map)->find();
            if($info){
                $this->error("该名称已存在！");
            }

            //剩下操作
            if(model('category')->updData($data)){
                $this->success('编辑成功', url('index'));
            }else{
                $this->error("编辑失败！");
            }
        }else{
            $id = input('param.id');
            //获取当前要修改的分类信息
            $info = $category->find($id); //一维数组
            $this -> assign('info', $info);
            //获得所有分类信息
            //获得所有分类信息
            $cat_info = $category->field("*,concat(cat_path,cat_id) as bpath")->order('bpath')->select();
            $this->assign('cat_info',$cat_info);
            $this->assign('ge',"--/");
            $this->setPageInfo('修改商品分类','商品分类',url('index'));
            return $this->fetch();
        }
    }

    //删除管理员
    public function del(){
        $id = input('param.id');

        $category = model('category');
        //获取所有的父id
        $pid_list = $category->getAllPid();
        //判断此分类是否在此父id数组中
        if(in_array($id,$pid_list)){
            echo "<script>alert('你底下还有子类，不能删除！'); history.back(-1);</script>";
        }else{
            //执行删除
            $category->where('cat_id', $id)->delete();
            $this->redirect('index');
        }
    }
}