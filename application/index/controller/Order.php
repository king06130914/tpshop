<?php
namespace app\index\controller;

use think\Request;

class Order extends Common
{
    public function __construct()
    {
        parent::__construct();
        $web_user = session('web_user');
        if (empty($web_user)) {
            $this->redirect('login/login');
        }
    }

    //我的订单
    public function index()
    {
        $order_status = array(0 => '取消', 1 => '未确认', 2 => '已确认', 3 => '已发货', 4 => '已完成');
        //通过用户id查询此用户的订单信息
        $orderInfo = db('Order')->where('order_u_id=' . session('web_id'))->order('order_id desc')->select();
        $this->assign('orderInfo', $orderInfo);
        $this->assign('order_status', $order_status);
        //显示页面信息
        $this->setPageInfo("我的订单", "详情关键字", "描述");
        return $this->fetch();
    }

    //订单详情
    public function orderdetail()
    {
        $order_id = input('param.order_id');
        //通过订单id查询此订单的详细信息,关联商品表查询商品的名称，价格，图片信息
        $orderInfo = db('Order_goods')->alias('a')
            ->field('a.order_id,a.goods_id,a.goods_price,a.goods_number,a.goods_attr,b.goods_name,b.goods_thumb,b.shop_price')
            ->join('sz_goods b', 'a.goods_id = b.goods_id', 'left')
            ->where('order_id=' . $order_id)->select();

        if (isset($orderInfo) && is_array($orderInfo)) {
            foreach ($orderInfo as $k => &$v) {
                $v['goods_attr_str'] = model('goods')->convertGoodsAttr($v['goods_attr']);
            }
        }

        //根据订单id查出当前订单状态
        $status = db('Order')->field('order_status')->find($order_id);
        $this->assign('status', $status);
        $this->assign('orderInfo', $orderInfo);
        //显示页面信息
        $this->setPageInfo("订单详情", "详情关键字", "描述");
        return $this->fetch();
    }

    //取消订单
    public function ajaxordercancel()
    {
        $order = db('Order');
        if (Request::instance()->isAjax()) {
            $order_id = input('post.order_id');
            //修改订单表中的状态为取消
            $m = $order->where('order_id=' . $order_id)->setField('order_status', '0');
            if ($m) {
                $res = array('state' => '200', 'info' => '取消订单成功！');
            } else {
                $res = array('state' => '400', 'info' => '取消订单失败！');
            }
            return json($res);
        }
    }

    //ajax评价商品
    public function ajaxcomment()
    {
        $message = db('Message');
        $user_id = session('web_id');
        $user = session('web_user');
        if (Request::instance()->isAjax()) {
            $comment = input('post.comment');
            $goods_id = input('post.goods_id');
            $order_id = input('post.order_id');
            $where = array(
                'a.order_u_id' => $user_id,
                'b.goods_id' => $goods_id,
            );
            if ($order_id) {
                $where['a.order_id'] = $order_id;
            }
            //通过商品id查出订单id，再查出订单状态
            $orderInfo = db('Order')->alias('a')->field('a.order_status')
                ->join('sz_order_goods b', 'a.order_id = b.order_id', 'left')
                ->where($where)->select();

            if (isset($orderInfo) && is_array($orderInfo)) {
                $orderInfo = array_column($orderInfo, 'order_status');
                $orderInfo = array_unique($orderInfo);
                foreach ($orderInfo as $key => $val) {
                    if (isset($val) && $val == 4) {
                        //获取当前用户的ip
                        $ip = ip();
                        //添加评论到评论表中
                        $insertData = array(
                            'msg' => $comment,
                            'sender' => $user,
                            'add_time' => time(),
                            'goods_id' => $goods_id,
                            'ip_address' => isset($ip)?$ip:'',
                        );
                        $id = $message->insert($insertData);
                        if (!$id) {
                            $res = array('state' => '400', 'info' => '评价失败！');
                        } else {
                            $res = array('state' => '200', 'info' => '评价成功！', 'data' => $insertData);
                        }
                        return json($res);
                    }
                }
            }
        }
    }

    //收货地址
    public function address()
    {
        $address = db('Address');
        //通过用户id查询此用户的收货地址
        $addressInfo = $address->where('user_id=' . session('web_id'))->select();

        $this->assign('addressInfo', $addressInfo);
        //显示页面信息
        $this->setPageInfo("收货地址", "详情关键字", "描述");
        return $this->fetch();
    }

    //ajax删除收货地址
    public function delAddress()
    {
        $address_id = input('post.address_id');
        if (Request::instance()->isAjax()) {
            $m = db('Address')->delete($address_id);
            if ($m) {
                $res = array('state' => '200', 'info' => '删除成功！');
            } else {
                $res = array('state' => '400', 'info' => '删除失败！');
            }
            return json($res);
        }
    }

    //ajax编辑收货地址
    public function editAddress()
    {
        if (Request::instance()->isAjax()) {
            $data = input('post.');
            $address = db('Address');
            $insertData = $address->create($data);
            $id = $address->update($insertData);

            if ($id) {
                $res = array('state' => '200', 'info' => '修改地址成功！');
            } else {
                $res = array('state' => '400', 'info' => '修改地址失败！');
            }
            return json($res);
        } else {
            $id = input('param.id');
            $addressInfo = db('address')->find($id);
            $this->assign('addressInfo', $addressInfo);
            //获取城市级联信息
            $this->get_province();
            // 临时关闭当前模板的布局功能
            $this->view->engine->layout(false);
            return $this->fetch('addressSave');
        }
    }

    //ajax添加收货地址
    public function addAddress()
    {
        if (Request::instance()->isAjax()) {
            $data = input('post.');
            $address = db('Address');
            $insertData = $address->create($data);
            $insertData['user_id'] = session('web_id');
            $id = $address->insert($insertData);

            if ($id) {
                $res = array('state' => '200', 'info' => '添加地址成功！');
            } else {
                $res = array('state' => '400', 'info' => '添加地址失败！');
            }
            return json($res);
        } else {
            //获取城市级联信息
            $this->get_province();
            // 临时关闭当前模板的布局功能
            $this->view->engine->layout(false);
            return $this->fetch('addAddress');
        }
    }
}

