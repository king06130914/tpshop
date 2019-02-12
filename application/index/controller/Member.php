<?php
namespace app\index\controller;

use think\Request;

class Member extends Common
{
    public function __construct(){
        parent::__construct();
        $web_user = session('web_user');
        if(empty($web_user)){
            $this->redirect('login/login');
        }
    }

    //个人中心主页
    public function index(){
        //通过session里面存的用户名查询此用户的信息
        $userInfo = db('user')->find(session('web_id'));
        $this->assign('userInfo',$userInfo);
        //显示页面信息
        $this->setPageInfo("个人中心主页","详情关键字","描述");
        return $this->fetch();
    }

    //用户信息
    public function user(){
        //通过session里面存的用户名查询此用户的信息
        $userInfo = db('user')->find(session('web_id'));
        $this->assign('userInfo',$userInfo);
        //显示页面信息
        $this->setPageInfo("用户信息","详情关键字","描述");
        return $this->fetch();
    }

    //我的收藏
    public function collect(){
        //通过session里面存的用户名查询此用户的收藏信息,并关联商品表查询缩略图
        $colModel = db('Collection');
        $collectInfo = $colModel->alias('a')
            ->field('a.*,b.goods_thumb,b.goods_name,b.shop_price')
            ->join('sz_goods b', 'a.goods_id=b.goods_id', 'left')
            ->where(array('a.user_id'=>array('eq',session('web_id'))))
            ->order('a.id desc')->select();
        if(isset($collectInfo) && is_array($collectInfo)){
            foreach($collectInfo as $k=>&$v){
                $v['goods_attr_str'] = model('goods')->convertGoodsAttr($v['goods_attr_id']);
            }
        }
        $this->assign('collectInfo',$collectInfo);
        //显示页面信息
        $this->setPageInfo("我的收藏","详情关键字","描述");
        return $this->fetch();
    }

    //删除我的收藏
    public function delCollect(){
        $id = input('post.id');
        $colModel = db('Collection');
        $m = $colModel->delete($id);
        if($m){
            $res = array('state' => '200', 'info' => '删除成功！');
        }else{
            $res = array('state' => '400', 'info' => '删除失败！');
        }
        return json($res);
    }

    //我的评论
    public function comment(){
        $name = session('web_user');
        $message = db('Message');
        if(Request::instance()->isAjax()){
            $id = input('post.id');
            $m = $message->delete($id);
            if($m){
                $res = array('state' => '200', 'info' => '删除成功！');
            }else{
                $res = array('state' => '400', 'info' => '删除失败！');
            }
            return json($res);
        }
        //查询我们评论的内容
        $commentInfo = $message->alias('a')
            ->field('a.*,b.goods_name')
            ->join('sz_goods b', 'a.goods_id=b.goods_id', 'left')
            ->where(array('a.sender'=>array('eq',$name)))
            ->order('a.id desc')->select();
        $this->assign('commentInfo',$commentInfo);
        //显示页面信息
        $this->setPageInfo("我的评论","详情关键字","描述");
        return $this->fetch();
    }

    //我的留言
    public function msg(){
        $this->setPageInfo("我的留言","详情关键字","描述");
        return $this->fetch();
    }

    //推广链接
    public function links(){
        $this->setPageInfo("推广链接","详情关键字","描述");
        return $this->fetch();
    }
}