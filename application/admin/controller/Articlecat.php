<?php
namespace app\admin\controller;

use think\Request;

class Articlecat extends Common
{
    public function index(){
        $articleCat = db('article_cat');
        //判断是否有查询
        $where = array();
        $search = array();
        if(Request::instance()->isGet()){
            if (!empty($_GET['cat_id'])) {
                $cat_id = input('get.cat_id');
                $where['cat_id'] = $cat_id;
                $search['cat_id'] = $cat_id;
            }
            if (!empty($_GET['cat_name'])) {
                $cat_name = input('get.cat_name');
                $where['cat_name'] = array('like',"%{$cat_name}%");
                $search['cat_name'] = $cat_name;
            }
            $this->assign('search',$search);
        }

        //获取分页信息
        $data = fpage($articleCat, $where, 'cat_id desc', 5);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);

        //获取文章分类信息
        $this->assign('cats',$articleCat->select());
        $this->setPageInfo('文章分类列表','添加新分类',url('add'));
        return $this->fetch();
    }

    //添加文章分类
    public function add()
    {
        if (Request::instance()->isPost()) {
            $article = db('article_cat');

            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("article_cat")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //剩下操作
            $res = $article->insert($data);
            if($res){
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        } else {
            $this->setPageInfo('添加文章分类','文章分类列表',url('index'));
            return $this->fetch();
        }
    }

    //修改文章分类
    public function edit(){
        $article = db('article_cat');
        if (Request::instance()->isPost()) {
            $data = input("post.");

            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("article_cat")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            $res = $article
                ->where('cat_id', $data['cat_id'])
                ->update($data);
            if($res){
                $this->success('编辑成功', url('index'));
            }else{
                $this->error("编辑失败！");
            }
        } else {
            $id = input('param.id');
            //根据id获取用户信息
            $info = $article->find($id);
            if(!$info){
                $this->error("参数错误！");
            }
            $this->assign('info', $info);
            $this->setPageInfo('编辑文章信息','文章列表',url('index'));
            return $this->fetch();
        }
    }

    //删除文章分类
    public function del(){
        $id = input('param.id');
        //执行删除
        db('article_cat')->delete($id);
        $this->redirect('index');
    }
}