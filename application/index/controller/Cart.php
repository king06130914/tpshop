<?php
namespace app\index\controller;

use think\Request;

class Cart extends Common
{
    //加载购物车页面1--查看购物车
    public function buycar1()
    {
        $this->setPageInfo("查看购物车", "查看购物车", "查看购物车", "0", array(), array('shade'));
        return $this->fetch();
    }

    //加载购物车页面2--确认订单
    public function buycar2()
    {
        /***************把用户选择的商品存到session中，如果没有选择就不能进入这个页面***********************/
        $data = input('post.');
        //先判断表单中是否选择了
        if(!isset($data['buythis'])){
            //再判断session中是否存在
            $buythis = session('buythis');
            if(!$buythis){
                echo "<script>alert('必须要选择购物车中的一件商品!');history.back(-1);</script>";
                exit();
            }
        }else{
            session('buythis',$data['buythis']);
        }

        //保存下是否清空购物车
        if(isset($data['clear'])){
            session('clear','1');
        }

        $mid = session('web_id');
        //如果用户没有登录，就跳到登录页面，如果登录就跳回来
        if (!$mid) {
            //把当前页面的地址存到session，这样登录之后就跳回来
            session('returnUrl', 'cart/buycar1');
            return redirect(url('login/login'));
        }

        //如果是下单的表单就处理
        if(Request::instance()->isPost() && isset($_POST['order_ship_name'])){
            //下订单
            $orderInfo = input('post.');
            $newId = model('order')->createOrder($orderInfo);

            if($newId){
                //如果下单成功，判断是否清空购物车
                $clear = session('clear');
                if($clear){
                    //清空购物车
                    $ids = $orderInfo['cart_id'];
                    $where['cart_id'] = array('in', $ids);
                    db('cart')->where($where)->delete();
                    session('clear', null);
                }
                session('buythis',null);
                $this->success('下单成功！', url('buycar3','order_id='.$newId));
                exit;
            }
            $this->error(model('order')->getError());
        }
        //取出购物车中的商品
        $ids = array();
        if(isset($data['buythis']) && is_array($data['buythis'])){
            foreach ($data['buythis'] as $key=>$val){
                $_val = explode('-',$val);
                $where = array(
                    'goods_id' => $_val[0],
                    'goods_attr_id' => $_val[1],
                    'goods_number' => $_val['2'],
                    'user_id' => $mid,
                );
                $cart = db('cart')->field('cart_id')->where($where)->find();
                $ids[] = $cart['cart_id'];
            }
        }

        //购物车数据
        $cartData = model('cart')->cartList($ids);

        //取出收货人信息
        $addressData = db('address')->where('user_id='.$mid)->select();

        //取出配送方式
        $shipData = db('shipping')->where('enabled', '1')->select();

        //取出支付方式
        $payData = db('payment')->where('enabled', '1')->select();

        $this->assign(array(
            'cartData2'=>$cartData,
            'shipData'=>$shipData,
            'payData'=>$payData,
            'addressData'=>$addressData,
        ));
        //显示表单
        $this->setPageInfo("确认订单","详情关键字","描述","0",array(),array('shade'));
        return $this->fetch();
    }

    //加载购物车页面3--成功提交订单
    public function buycar3(){
        $order_id = input('param.order_id');
        $orderModel = db('order');
        //从订单表中查出订单编号
        $orderData = $orderModel->field('order_sn,order_ship_name,order_pay_name,order_amount')->find($order_id);
        $this->assign('orderData',$orderData);
        $this->setPageInfo("成功提交订单","详情关键字","描述","0",array(),array('shade'));
        return $this->fetch();
    }

    //添加到购物车
    public function addCart()
    {
        if (Request::instance()->isAjax()) {
            $num = input('post.num');
            $id = input('post.id');
            if (input('post.attr')) {
                $attr = input('post.attr');
                $attr = trim($attr, ',');
            } else {
                $attr = '';
            }
            $info = model('cart')->addToCart($id, $attr, $num);
            if(!$info){
                $res = array('state' => '400', 'info' => '添加到购物车失败！');
            }else{
                $res = array('state' => '200', 'info' => '添加到购物车成功！');
            }
            return json($res);
        }
    }

    //ajax修改数据
    public function ajaxUpdateData(){
        $gid = input('post.gid');
        $gaid = input('post.gaid');
        $gn = input('post.gn');
        model('cart')->updateData($gid,$gaid,$gn);
    }

    //删除购物车商品
    public function delcart(){
        $data = input('post.');
        $where = array(
            'goods_id' => isset($data['goods_id'])?$data['goods_id']:'',
            'goods_attr_id' => isset($data['goods_attr_id'])?$data['goods_attr_id']:''
        );
        $m = db('cart')->where($where)->delete();
        if($m){
            $res = array('state' => '200', 'info' => '删除成功！');
        }else{
            $res = array('state' => '400', 'info' => '删除失败！');
        }
        return json($res);
    }

    //收藏商品
    public function collectGoods(){
        $data = input('post.');
        $insertData = array(
            'goods_id' => isset($data['goods_id'])?$data['goods_id']:'',
            'goods_attr_id' => isset($data['goods_attr_id'])?$data['goods_attr_id']:'',
            'user_id' => session('web_id'),
            'add_time' => time(),
        );
        $m = db('collection')->insert($insertData);
        if($m){
            $res = array('state' => '200', 'info' => '收藏成功！');
        }else{
            $res = array('state' => '400', 'info' => '收藏失败！');
        }
        return json($res);
    }
}
 