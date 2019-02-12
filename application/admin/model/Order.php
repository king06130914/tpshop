<?php
namespace app\admin\model;

use think\Model;

class Order extends Model
{
    public function addDeal($info){
        //修改订单交易表里面的信息
        $orderDeal = db('order_deal'); // 实例化User对象
        $info['deal_status'] = $info['order_status'];
        unset($info['order_status']);
        $info['deal_time'] = time();
        $info['deal_admin_name'] = session('admin_user');

        $where = array('deal_order_sn' => $info['deal_order_sn']);
        $deal_order_sn = $orderDeal->where($where)->find();
        //判断订单交易表里是否有此订单编号
        if($deal_order_sn){
            $orderDeal->update($info);
        }else{
            $orderDeal->insert($info);
        }
    }
}