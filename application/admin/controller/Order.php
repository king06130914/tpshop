<?php
namespace app\admin\controller;

use think\Request;

class Order extends Common
{
    //订单列表
    public function index(){
        //分配订单状态信息
        $status = array(0=>'取消',1=>'未确认',2=>'已确认',3=>'已发货',4=>'已完成');
        $this->assign('status',$status);
        //获取下单用户信息
        $user = model('user');
        $this->assign('user',$user->orderUser());
        //获取收货人地址
        $address = model('address');
        $this->assign('address',$address->userAddress());

        //判断是否有查询
        $where = array();
        if(!empty($_GET['order_sn'])){
            $where['order_sn'] = input('get.order_sn');
        }
        if(isset($_GET['order_status']) && $_GET['order_status'] != null){
            $where['order_status'] = input('get.order_status');
        }
        $this->assign('search',$where);

        //获取分页信息
        $data = fpage(db('order'), $where, 'order_id desc', 5);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);
        $this->setPageInfo('订单列表','订单查询',url('view'));
        return $this->fetch();
    }

    //修改订单状态
    public function edit(){
        $order = db('order');
        if (Request::instance()->isPost()) {
            $info = input("post.");

            //修改订单交易表里面的信息
            model('order')->addDeal($info);
            //剩下操作
            $res = $order
                ->where('order_id', $info['deal_id'])
                ->update(array('order_status' => $info['order_status']));
            if($res){
                $this->success('编辑成功', url('index'));
            }else{
                $this->error("编辑失败！");
            }
        } else {
            $id = input('param.id');
            //根据id获取用户信息
            $info = $order->find($id);
            if(!$info){
                $this->error("参数错误！");
            }
            $this->assign('info', $info);

            //分配订单状态信息
            $status = array(0=>'取消',1=>'未确认',2=>'已确认',3=>'已发货',4=>'已完成');
            $this->assign('status',$status);
            $this->setPageInfo('修改订单状态','订单列表',url('index'));
            return $this->fetch();
        }
    }

    //订单详情
    public function detail(){
        $order_goods = db('order_goods');
        //判断是否有查询
        $map = array();
        $search = array();
        if(!empty($_POST['goods_name'])){
            $goods_name = input('post.goods_name');
            $map['goods_name'] = array('like',"%{$goods_name}%");

            $search['goods_name'] = $goods_name;
            $this->assign('search',$search);
        }
        //获取当前订单的订单id号
        $map['order_id'] = input('param.id');
        //执行查询
        $info = $order_goods->where($map)->select();
        $this->assign('info',$info);
        $this->setPageInfo('订单详情','订单列表',url('index'));
        return $this->fetch();
    }

    //订单查询
    public function view(){
        //判断是否有查询
        $where = array();
        if(!empty($_GET['deal_order_sn'])){
            $where['deal_order_sn'] = input('get.deal_order_sn');
        }
        if(!empty($_GET['deal_status']) || (isset($_GET['deal_status']) && $_GET['deal_status'] == '0')){
            $where['deal_status'] = input('get.deal_status');
        }
        $this->assign('search',$where);

        //获取分页信息
        $data = fpage(db('orderDeal'), $where, 'deal_id desc', 10);
        $this->assign('page_list',$data['page_list']);
        $this->assign('info',$data['info']);

        $status = array(0=>'取消',1=>'未确认',2=>'已确认',3=>'已发货',4=>'已完成');
        $this->assign('status',$status);

        $this->setPageInfo('订单查询','订单列表',url('index'));
        return $this->fetch();
    }
}