<?php
namespace app\index\controller;

use think\Request;

class Login extends Common
{
    public function login(){
        if(Request::instance()->isPost()){
            $data = input("post.");
            $user = db('user');

            //获取当前时间
            $time = time();

            //判断验证码是否在正确
            $captcha = new \think\captcha\Captcha();
            $result=$captcha->check($data['chkcode']);
            if($result===false){
                $res = array('state' => '400', 'info' => '验证码错误！');
                return json($res);
            }

            //利用model模型的自定义方法校验用户名和密码
            $info = model('user')->checkNamePwd($data['username'], $data['password']);
            if($info){
                //持久化用户信息，并做页面跳转
                session('web_user', $info['username']);
                session('web_id', $info['user_id']);
                session('login_time', $time);
                //把用户上一次登录时间插入数据库
                date_default_timezone_set('Asia/Shanghai');
                if(!empty($_COOKIE['lastLoginTime']))
                {
                    //更新数据库中的最后一次登录时间
                    $updateData['last_time'] = $_COOKIE['lastLoginTime'];
                    $user->where('user_id', $info['user_id'])->update($updateData);
                    //取出之后，再更新登录时间
                    setcookie('lastLoginTime',$time,$time+24*3600*365);
                } else {
                    setcookie('lastLoginTime',$time,$time+24*3600*365);
                }

                //把购物车中的数据从cookie移动到数据库
                model('cart')->moveDataToDb();
                //从session中取出有没有要跳回的地址
                $returnUrl = session('returnUrl');
                if($returnUrl){
                    //从session中删除掉，下次再登录就正常跳到首页
                    session('returnUrl',null);
                    $res = array('state' => '200', 'info' => '恭喜您，登录成功！','url' => $returnUrl);
                }else{
                    $res = array('state' => '200', 'info' => '恭喜您，登录成功！','url' => '');
                }
                return json($res);
            }else{
                $res = array('state' => '401', 'info' => '用户名或密码错误！');
                return json($res);
            }
        }else{
            // 临时关闭当前模板的布局功能
            $this->view->engine->layout(false);
            return $this->fetch();
        }

    }

    //注册页面
    public function regist(){
        if(Request::instance()->isAjax()){
            $data = input("post.");
            $user = db('user');

            //获取当前时间
            $time = time();

            //判断验证码是否在正确
            $captcha = new \think\captcha\Captcha();
            $result=$captcha->check($data['chkcode']);
            if($result===false){
                $res = array('code' => '400', 'msg' => '验证码错误！');
                return json($res);
            }

            $validate = validate("Vdate"); //使用验证
            if(!$validate->scene("regist")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证邮箱格式
            if(!self::isEmail($data['user_email'])){
                $res = array('code' => '402', 'msg' => '邮箱格式不合法，请重新输入！');
                return json($res);
            }

            //验证邮箱格式
            if(!self::isPhone($data['user_tel'])){
                $res = array('code' => '402', 'msg' => '手机格式不合法，请重新输入！');
                return json($res);
            }

            //拼装数据
            $insertData = array(
                'username' => $data['username'],
                'name' => $data['username'],
                'password' => md5($data['password']),
                'user_email' => $data['user_email'],
                'user_tel' => $data['user_tel'],
                'user_time' => $time,
                'last_time' => $time,
            );
            $res = $user->insert($insertData);
            if($res){
                $res = array('code' => '200', 'msg' => '恭喜您，注册成功！');
                return json($res);
            }else{
                $res = array('code' => '401', 'msg' => '注册失败，请重试！');
                return json($res);
            }
        }else{
            // 临时关闭当前模板的布局功能
            $this->view->engine->layout(false);
            return $this->fetch();
        }
    }

    //生成验证码
    public function verifyImg(){
        $captcha = new \think\captcha\Captcha();
        $captcha->imageW = 121;
        $captcha->imageH = 32;  //图片高
        $captcha->fontSize = 18;  //字体大小
        $captcha->length = 4;  //字符数
        $captcha->fontttf = '4.ttf';  //字体
        $captcha->expire = 30;  //有效期
        $captcha->useNoise = false;  //不添加杂点
        return $captcha->entry();
    }

    //退出
    public function logout(){
        session(null);//清空session
        $this->redirect('login');
    }
}