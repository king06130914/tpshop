<?php
namespace app\admin\controller;

use think\Request;

class Article extends Common
{
    public function index(){
        $article = db('article');
        //判断是否有查询
        $where = array();
        if(Request::instance()->isGet()){
            if (!empty($_GET['article_cat_id'])) {
                $article_cat_id = input('get.article_cat_id');
                $where['article_cat_id'] = $article_cat_id;
            }
            if (!empty($_GET['title'])) {
                $title = input('get.title');
                $where['article_title'] = array('like',"%{$title}%");
            }
            $this->assign('search',$where);
        }

        //获取分页信息
        $data = fpage($article, $where, 'article_id desc', 5);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);

        //获取文章分类信息
        $cats = db('article_cat')->select();
        $article_cat = array_column($cats, 'cat_name', 'cat_id');
        $this->assign('cats',$cats);
        $this->assign('article_cat',$article_cat);
        $this->setPageInfo('文章列表','添加新文章',url('add'));
        return $this->fetch();
    }

    //添加文章
    public function add()
    {
        if (Request::instance()->isPost()) {
            $article = db('article');
            //获取当前时间
            $time = time();

            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("article")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //剩下操作
            $data['article_create_time'] = $time;
            $res = $article->insert($data);
            if($res){
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        } else {
            //获取文章分类信息
            $this->assign('cats', db('article_cat')->select());
            $this->setPageInfo('添加文章','文章列表',url('index'));
            return $this->fetch();
        }
    }

    //修改文章
    public function edit(){
        $article = db('article');
        if (Request::instance()->isPost()) {
            //获取当前时间
            $time = time();
            $data = input("post.");

            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("article")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //剩下操作
            $data['article_update_time'] = $time;
            $res = $article
                ->where('article_id', $data['article_id'])
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

            //获取文章分类信息
            $this->assign('cats', db('article_cat')->select());
            $this->setPageInfo('编辑文章信息','文章列表',url('index'));
            return $this->fetch();
        }
    }

    //删除文章
    public function del(){
        $id = input('param.id');
        //执行删除
        db('article')->delete($id);
        $this->redirect('index');
    }
}