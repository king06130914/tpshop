<?php
namespace app\index\controller;

use think\Request;

class Account extends Common
{
    public function __construct(){
        parent::__construct();
        $web_user = session('web_user');
        if(empty($web_user)){
            $this->redirect('login/login');
        }
    }
    //账户安全
    public function safe(){
        if(Request::instance()->isPost()){
            $web_id = session('web_id');
            $data = input('post.');
            //获取表单数据
            $pw = isset($data['oldpassword'])?$data['oldpassword']:'';
            $npw = isset($data['newpassword'])?$data['newpassword']:'';
            $repw = isset($data['repassword'])?$data['repassword']:'';
            if($npw !== $repw){
                echo "<script>alert('两次输入的密码不一致！');history.back(-1);</script>";
                exit;
            }
            //利用model模型的自定义方法校验用户名和密码
            $users = db('User');
            $userInfo = $users->where(array(
                'password'=>md5($pw),
                'user_id'=>$web_id,
            ))->find();
            if($userInfo){
                $userData = $users->where('user_id='.$web_id)
                    ->setField('password',md5($npw));;
                if($userData){
                    echo "<script>alert('修改密码成功！');history.back(-1);</script>";
                }else{
                    echo "<script>alert('修改密码失败！');history.back(-1);</script>";
                }
            }else{
                echo "<script>alert('原密码不正确！');history.back(-1);</script>";
            }
        }
        //显示页面信息
        $this->setPageInfo("账户安全","详情关键字","描述");
        return $this->fetch();
    }

    //资金管理
    public function money(){
        //显示页面信息
        $this->setPageInfo("资金管理","详情关键字","描述");
        return $this->fetch();
    }

    //我的红包
    public function packet(){
        //显示页面信息
        $this->setPageInfo("我的红包","详情关键字","描述");
        return $this->fetch();
    }
}
