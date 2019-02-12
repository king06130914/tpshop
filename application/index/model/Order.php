<?php
namespace app\index\model;

use think\Model;

//公共控制器
class Order extends Model
{
    public function createOrder($data){
        $mid = session('web_id');
        //判断购物车中是否有商品
        $ids = isset($data['cart_id']) ? $data['cart_id'] : '';
        $cartdata = model('cart')->cartList($ids);
        if(count($cartdata) <= 0){
            $this->error = '必须先购买商品才能下单';
            return false;
        }
        //计算总价
        $total = 0;
        foreach($cartdata as $k=>$v){
            $total += $v['shop_price'] * $v['goods_number'];
        }

        //订单数据
        $orderData = array(
            'order_sn' => date('YmdHis').rand(100000, 999999),
            'order_u_id' => $mid,
            'order_status' => 2,
            'order_ship_name' => isset($data['order_ship_name'])?$data['order_ship_name']:'',
            'order_pay_name' => isset($data['order_pay_name'])?$data['order_pay_name']:'',
            'order_remarks' => isset($data['order_remarks'])?$data['order_remarks']:'',
            'order_amount' => $total,
            'order_create_time' => time(),
        );
        //下单前把其他信息补上即可
        //判断是否使用新地址
        $address = isset($data['order_consignee_id'])?$data['order_consignee_id']:'';
        $addressModel = db('address');
        if($address){
            //使用已有的地址
            $addressinfo = $addressModel->find($address);
            $orderData['order_consignee_id'] = $data['order_consignee_id'];
            $orderData['shr_name'] = $addressinfo['consignee'];
            $orderData['order_u_name'] = $addressinfo['email'];
        }else{
            //先添加新地址，取出新地址的id
            $addressModel = db('address');
            $info = $addressModel->create($data);
            $info['user_id'] = $mid;
            $addressId = $addressModel->insert($info,true,true);

            $orderData['order_consignee_id'] = $addressId;
            $orderData['shr_name'] = $data['consignee'];
            $orderData['order_u_name'] = $data['email'];
        }

        //插入订单数据
        $orderId = $this->insert($orderData,true,true);

        if($orderId){
            $where['a.cart_id'] = array('in', $ids);
            $cartData = db('cart')->alias('a')
                ->field('a.goods_id,a.goods_attr_id as goods_attr,a.goods_number, b.goods_name,b.goods_img,b.shop_price')
                ->join('sz_goods b', 'a.goods_id = b.goods_id','left')
                ->where($where)->select();
            if(isset($cartData) && is_array($cartData)){
                foreach ($cartData as $key => &$val){
                    $val['subtotal'] = $val['shop_price'] * $val['goods_number'];
                    $val['goods_price'] = $val['shop_price'];
                    $val['order_id'] = $orderId;
                }
                $res = db('order_goods')->insertAll($cartData,true);
                if($res){
                    return $orderId;
                }
                return false;
            }
        }
    }
}