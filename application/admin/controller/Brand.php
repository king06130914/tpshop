<?php
namespace app\admin\controller;

use think\Request;

class Brand extends Common
{
    public function index(){
        $brand = db('brand');
        //判断是否有查询
        $where = array();
        $search = array();
        if(!empty($_GET['brand_name'])){
            $brand_name = input('get.brand_name');
            $where['brand_name'] = array('like',"%{$brand_name}%");
            $search['brand_name'] = $brand_name;
            $this->assign('search',$search);
        }

        //获取分页信息
        $data = fpage($brand, $where, 'brand_id desc', 10);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);
        $this->setPageInfo('商品品牌','添加品牌',url('add'));
        return $this->fetch();
    }

    //添加品牌
    public function add (){
        $brand = db('brand');
        //两个逻辑：展示表单，收集表单
        if(Request::instance()->isPost()){
            $data = input("post.");

            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("brand")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证链接是否是url
            if(!is_url($data['brand_url'])){
                $this->error("请输入正确的链接地址！");
            };

            $imgData = uploadFile('brand_pic', date('Y-m-d'), 80, 80);
            $data['brand_logo'] = isset($imgData['img'])?$imgData['img']:'';

            //剩下操作
            $res = $brand->insert($data);
            if($res){
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        }else{
            $this->setPageInfo('添加品牌','商品品牌',url('index'));
            return $this->fetch();
        }
    }

    //修改品牌
    public function edit(){
        $brand = db('brand');
        //两个逻辑：展示表单，收集表单
        if(Request::instance()->isPost()){
            $data = input("post.");

            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("brand")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证链接是否是url
            if(!is_url($data['brand_url'])){
                $this->error("请输入正确的链接地址！");
            };

            //查询原来的图片路径
            $info = $brand->find($data['brand_id']);

            $imgData = uploadFile('brand_pic', date('Y-m-d'), 80, 80);
            if(isset($imgData['img'])){
                $data['brand_logo'] = $imgData['img'];
                //删除原来的logo图片
                $path_parts = pathinfo($info['brand_logo']);
                $thumb_path = $path_parts['dirname'].'/../thumb/'.$path_parts['basename'];
                @unlink('./'.$info['brand_logo']);
                @unlink('./'.$thumb_path);
            }

            //剩下操作
            $res = $brand
                ->where('brand_id', $data['brand_id'])
                ->update($data);
            if($res){
                $this->success('编辑成功', url('index'));
            }else{
                $this->error("编辑失败！");
            }
        }else{
            $id = input('param.id');
            //获取当前要修改的数据信息
            $info = $brand->find($id); //一维数组
            $this -> assign('info', $info);
            $this->setPageInfo('编辑品牌信息','商品品牌',url('index'));
            return $this->fetch();
        }
    }

    //删除品牌
    public function del(){
        $id = input('param.id');

        $brand = db('brand');
        //查询要删除的数据信息
        $list = $brand->find($id);
        //执行删除
        $z = $brand->delete($id);
        if($z){
            //删除此品牌的图片
            @unlink('./'.$list['brand_logo']);

            //删除此品牌的缩略图
            $path_parts = pathinfo($list['brand_logo']);
            $thumb_path = $path_parts['dirname'].'/../thumb/'.$path_parts['basename'];
            @unlink('./'.$thumb_path);
            $this->redirect('index');
        }else{
            $this->error('删除品牌失败！',url('index'));
        }
    }
}