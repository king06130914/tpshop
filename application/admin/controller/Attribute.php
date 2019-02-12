<?php
namespace app\admin\controller;

use think\Request;

class Attribute extends Common
{
    public function index(){
        $id = input('param.id');
        $attribute = db('attribute');
        //判断是否有查询
        $where = array();
        $where['type_id'] = $id;

        //获取分页信息
        $data = fpage($attribute, $where, 'attr_id desc');
        $this->assign('page_list', $data['page_list']);
        $this->assign('info', $data['info']);

        //获取所有类型
        $type = db('type')->select();
        $this->assign('type', $type);

        //转换类型数组
        $typeIdToName = array_column($type, 'type_name', 'type_id');
        $this->assign('typeIdToName', $typeIdToName);

        //制作属性录入方式的数组
        $input_type = array('0'=>'手工录入','1'=>'从列表中选择','2'=>'文本域');
        $this->assign('input_type',$input_type);

        //获取传递过来的类型
        $this->assign('id', $id);
        $this->setPageInfo('商品属性','添加属性',url('add', ['id'=> $id]));
        return $this->fetch();
    }

    //添加属性
    public function add (){
        $attribute = db('attribute');
        //两个逻辑：展示表单，收集表单
        if(Request::instance()->isPost()){
            $data = input("post.");

            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("attribute")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //剩下操作、
            $res = $attribute->insert($data);
            if($res){
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        }else{
            //获取所有类型
            $this->assign('type', db('type')->select());

            $id = input('param.id');
            $this->assign('id', $id);
            $this->setPageInfo('添加属性','商品属性',url('index'));
            return $this->fetch();
        }
    }

    //修改属性
    public function edit(){
        $attribute = db('attribute');
        //两个逻辑：展示表单，收集表单
        if(Request::instance()->isPost()){
            $data = input("post.");

            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("attribute")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            //剩下操作
            $res = $attribute
                ->where('attr_id', $data['attr_id'])
                ->update($data);
            if($res){
                $this->success('编辑成功', url('index'));
            }else{
                $this->error("编辑失败！");
            }
        }else{
            //获取所有类型
            $this->assign('type', db('type')->select());

            $id = input('param.id');
            //获取当前要修改的数据信息
            $info = $attribute->find($id); //一维数组
            $this -> assign('info', $info);
            $this->setPageInfo('编辑属性','商品属性',url('index'));
            return $this->fetch();
        }
    }

    //删除属性
    public function del(){
        $id = input('param.id');
        db('attribute')->delete($id);
        //获取当前类型id
        $type_id = input('param.type_id');
        $this->assign('type_id',$type_id);
        $this->redirect('index',array('id'=>$type_id),2,'删除属性成功！');
    }

    public function ajaxGetAttrByType(){
        if(isset($_GET['id'])){
            $id = input('get.id');
            $where = array();
            if($id != '0'){
                $where['type_id'] = $id;
            }
            $data = db('attribute')->where($where)->select();
            $res = array('state' => '200', 'data' => $data);
            return json($res);
        }
    }
}