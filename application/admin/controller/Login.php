<?php
namespace app\admin\controller;

use think\Controller;

class Login extends Controller
{
    /**
     * 登录
     */
    public function login(){
        //判断是否登录过
        if(session('admin_id')){
            $this->redirect(url('/admin'));
        }

        //两个逻辑：展示表单，收集表单
        if(!empty($_POST)){
            $code=input('post.captcha');
            $captcha = new \think\captcha\Captcha();
            $result=$captcha->check($code);
//            if($result===false){
//                $res = array('state' => '400', 'info' => '验证码错误！');
//                return json($res);
//            }

            //校验用户名和密码
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            //利用model模型的自定义方法校验用户名和密码
            $info = model('manager')->checkNamePwd($username, $password);
            if($info){
                //持久化用户信息，并做页面跳转
                session('admin_user', $username);
                session('admin_id', $info['mg_id']);
                $res = array('state' => '200', 'info' => '恭喜您，登录成功！');
                return json($res);
            }else{
                $res = array('state' => '401', 'info' => '用户名或密码错误！');
                return json($res);
            }
        }else{
            return $this->fetch('login/login');
        }
    }

    //显示验证码
    public function show_captcha()
    {
        $captcha = new \think\captcha\Captcha();
        $captcha->imageW = 121;
        $captcha->imageH = 32;  //图片高
        $captcha->fontSize = 14;  //字体大小
        $captcha->length = 4;  //字符数
        $captcha->fontttf = '5.ttf';  //字体
        $captcha->expire = 30;  //有效期
        $captcha->useNoise = false;  //不添加杂点
        return $captcha->entry();
    }
    //退出
    public function logout(){
        session('admin_user', null);
        session('admin_id', null);
        $this->redirect(url('login/login'));
    }

    /**
     * 邮件发送 找回密码
     */
    public function reset_pwd()
    {
        if(empty($_POST)){
            $res = array('state' => '400', 'info' => '参数异常，请重试！');
            return json($res);
        }

        $username = isset($_POST['username']) ? $_POST['username'] : '';

        //admin不能重置密码
        if($username == 'admin'){
            $res = array('state' => '401', 'info' => '此管理员用户为超级管理员，不能重置密码');
            return json($res);
        }

        //查询是否有此管理员
        $manager = db('manager');
        $info = $manager->where('mg_name', $username)->find();

        if(!$info){
            $res = array('state' => '402', 'info' => '您输入的管理员姓名不存在，请重新填写！');
            return json($res);
        }

        //修改数据库中的密码
        $res1 = $manager
            ->where('mg_name', $username)
            ->update(['mg_pwd' => md5($username)]);
        if(!$res1){
            $res = array('state' => '403', 'info' => '密码重置失败');
            return json($res);
        }

        $res = array('state' => '200', 'info' => '密码重置成功，密码与您的姓名一致！');
        return json($res);
    }
}