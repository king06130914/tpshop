<?php
namespace app\admin\controller;

use think\Request;

class Auth extends Common
{
    //权限列表
    public function index()
    {
        //判断是否有查询
        $where = array();
        $search = array();
        if(!empty($_GET['auth_name'])){
            $auth_name = input('get.auth_name');
            $where['auth_name'] = array('like', "%{$auth_name}%");
            $search['auth_name'] = $auth_name;
        }
        $this->assign('search',$search);

        //获取分页信息
        $data = fpage(db('auth'), $where, 'auth_path', 10);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);
        $this->assign('ge',"--/");
        $this->setPageInfo('权限列表','添加权限',url('add'));
        return $this->fetch();
    }

    //添加权限
    public function add()
    {
        $auth = db('auth');
        if (Request::instance()->isPost()) {
            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("auth")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $info = $auth->where('auth_name', $data['auth_name'])->find();
            if($info){
                $this->error("该权限已存在，不能重复添加！");
            }

            //通过saveData方法制作权限的auth_path和auth_level进而实现整条数据的存储
            if(model('auth')->saveData($data)){
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        } else {
            //获得父级权限
            $auth_p_info = $auth->where('auth_level=0')->select();
            $this->assign('auth_p_info',$auth_p_info);
            $this->setPageInfo('添加权限','权限列表',url('index'));
            return $this->fetch();
        }
    }

    //修改权限
    public function edit(){
        $auth = db('auth');
        if (Request::instance()->isPost()) {
            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("auth")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //验证名称唯一性
            $map['auth_name'] = $data['auth_name'];
            $map['auth_id'] = array('neq',$data['auth_id']);
            $info = $auth->where($map)->find();
            if($info){
                $this->error("该权限已存在！");
            }

            //剩下操作
            if(model('auth')->modData($data)){
                $this -> success('修改权限成功！',url('index'));
            }else{
                $this -> error('修改权限失败！');
            }
        } else {
            $id = input('param.id');
            //根据id获取用户信息
            $info = $auth->find($id);
            if(!$info){
                $this->error("参数错误！");
            }
            $this->assign('info', $info);
            //获得父级权限
            $auth_p_info = $auth->where('auth_level=0')->select();
            $this->assign('auth_p_info',$auth_p_info);
            $this->setPageInfo('编辑权限信息','权限列表',url('index'));
            return $this->fetch();
        }
    }

    //删除权限
    public function del(){
        $id = input('param.id');

        $auth = model('auth');
        $pid_list = $auth->getAllPid();
        //判断此类底下是否还有子类
        if(in_array($id,$pid_list)){
            //有子类则不能删除
            echo "<script>alert('你底下还有子类，不能删除！'); history.back(-1);</script>";
        }else{
            //没有子类才删除
            $auth->where('auth_id', $id)->delete();
            $this->redirect('index');
        }
    }
}