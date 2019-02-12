<?php
namespace app\admin\controller;

use think\Request;

class User extends Common
{
    public function index()
    {
        $where = array();
        $search = array();
        //判断是否有查询
        if(!empty($_GET['name'])){
            //用户名和登录名都可以作为模糊查询的对象
            $name = input('get.name');
            $where['username'] = array('like',"%{$name}%");
            $search['name'] = $name;
        }
        $this->assign('search',$search);

        //获取分页信息
        $data = fpage(db('user'), $where, 'user_id desc', 5);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);
        $this->setPageInfo('会员列表','用户评论',url('comment'));
        return $this->fetch();
    }

    //修改会员密码
    public function edit(){
        $user = db('user');
        //两个逻辑：展示表单，收集表单
        if(Request::instance()->isPost()){
            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("user")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //剩下操作
            $data['password'] = md5($data['password']);
            $res = $user
                ->where('user_id', $data['user_id'])
                ->update($data);
            if($res){
                $this->success('修改密码成功', url('index'));
            }else{
                $this->error("修改密码失败！");
            }
        } else {
            $id = input('param.id');
            //根据id获取用户信息
            $info = $user->find($id);
            if(!$info){
                $this->error("参数错误！");
            }
            $this->assign('info', $info);
            $this->setPageInfo('修改会员密码','会员列表', url('index'));
            return $this->fetch();
        }
    }

    //删除会员
    public function del(){
        $id = input('param.id');
        //执行删除
        db('user')->delete($id);
        $this->redirect('index',array(),2,'删除会员成功！');
    }

    //用户评论
    public function comment(){
        $where = array();
        $search = array();
        //判断是否有查询
        if(Request::instance()->isGet()){
            if(!empty($_GET['sender'])){
                $sender = input('get.sender');
                $where['sender'] = array('like',"%{$sender}%");
                $search['sender'] = $sender;
            }
            if(isset($_GET['state']) && $_GET['state'] != ''){
                $where['state'] = input('get.state');
                $search['state'] = input('get.state');
            }
        }
        $this->assign('search', $search);

        //获取分页信息
        $data = fpage(db('message'), $where, 'id desc', 10);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);
        //分配显示状态
        $state = array('0'=>'隐藏','1'=>'显示');
        $this->assign('state',$state);
        $this->setPageInfo('用户评论','会员列表', url('index'));
        return $this->fetch();
    }

    //删除评论
    public function com_del(){
        $id = input('param.id');
        //执行删除
        db('message')->delete($id);
        $this->redirect('comment');
    }

    //评论详情
    public function detail(){
        $id = input('param.id');
        $message = db('message');
        if(Request::instance()->isPost()){
            $data = input("post.");

            //剩下操作
            if($message->update($data)){
                $this->success('修改评论状态成功', url('comment'));
            }else{
                $this->error("修改评论状态失败！");
            }
        }
        //分配显示状态
        $state = array('0'=>'隐藏','1'=>'显示');
        $this->assign('state',$state);
        //获取当前评论的详细信息
        $this->assign('info', $message->find($id));
        $this->setPageInfo('评论详情','用户评论',url('comment'));
        return $this->fetch();
    }
}
