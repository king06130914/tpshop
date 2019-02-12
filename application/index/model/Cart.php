<?php
namespace app\index\model;

use think\Model;

//公共控制器
class Cart extends Model
{
    //加入购物车
    public function addToCart($goods_id,$goods_attr_id,$goods_number = 1){
        $mid = session('web_id');
        //如果登录了就加入数据库中，没有登录就放到cookie中
        if($mid){
            $cart = db('cart');
            $has = $cart->where(array(
                'user_id' => array('eq',$mid),
                'goods_id' => array('eq',$goods_id),
                'goods_attr_id' => array('eq',$goods_attr_id),
            ))->find();
            //判断商品是否已经存在
            if($has){
                $res = $cart->where('cart_id='.$has['cart_id'])->setInc('goods_number',$goods_number);
            }else{
                $res = $cart->insert(array(
                    'goods_id' => $goods_id,
                    'goods_attr_id' => $goods_attr_id,
                    'goods_number' => $goods_number,
                    'user_id' => $mid,
                ));
            }

            if(!$res){
                return false;
            }
        }else{
            //先从cookie中取出购物车的数组
            $cart = isset($_COOKIE['cart']) ? unserialize($_COOKIE['cart']) : array();
            //把商品加入这个数组中
            $key = $goods_id.'-'.$goods_attr_id;
            //判断数组中有没有这件商品
            if(isset($cart[$key])){
                $cart[$key] += $goods_number;
            }else{
                $cart[$key] = $goods_number;
            }
            //把这个数组存回到cookie
            $aMonth = 30 * 86400;
            setcookie('cart',serialize($cart),time()+$aMonth,'/');
        }
        return true;
    }

    //购物车列表
    public function cartList($ids = array()){
        $mid = session('web_id');
        if($mid){
            $where['user_id'] = $mid;
            if(!empty($ids)){
                $where['cart_id'] = array('in',$ids);
            }
            $_cart = $this->where($where)->select();
        }else{
            $_cart_ = isset($_COOKIE['cart']) ? unserialize($_COOKIE['cart']) : array();
            //转换成二维数组，和数据库中的结构一样
            $_cart = array();
            foreach($_cart_ as $k=>$v){
                //从下标中解析出商品id和商品属性id
                $_k = explode('-',$k);
                $_cart[] = array(
                    'goods_id' => $_k[0],
                    'goods_attr_id' => $_k[1],
                    'goods_number' => $v,
                    'user_id' => 0,
                );
            }
        }
        //循环购物车中每件商品，根据商品id取出商品详情
        $goodsModel = db('goods');
        foreach($_cart as $k=>$v){
            $ginfo = $goodsModel->field('goods_name,goods_thumb,shop_price')->find($v['goods_id']);
            $_cart[$k]['goods_name'] = $ginfo['goods_name'];
            $_cart[$k]['goods_thumb'] = $ginfo['goods_thumb'];
            $_cart[$k]['shop_price'] = $ginfo['shop_price'];
            $_cart[$k]['goods_attr_str'] = model('goods')->convertGoodsAttr($v['goods_attr_id']);
        }
        return $_cart;
    }

    //把cookie中的数据转移到数据库中，清空cookie中的数据
    public function moveDataToDb(){
        $mid = session('web_id');
        if($mid){
            //先从cookie中取出购物车的数组
            $cart = isset($_COOKIE['cart']) ? unserialize($_COOKIE['cart']) : array();
            if($cart){
                //循环每件商品加入到数据库中
                foreach($cart as $k=>$v){
                    //从下标中解析出商品id和商品属性id
                    $_k = explode('-',$k);
                    $this->addToCart($_k[0],$_k[1],$v);
                }
                //清空cookie中的数据
                setcookie('cart','',time()-1,'/');
            }
        }
    }

    //修改购物车数据
    public function updateData($gid,$gaid,$gn){
        $mid = session('web_id');
        if($mid){
            $cartModel = db('cart');
            $where = array(
                'goods_id' => $gid,
                'goods_attr_id' => $gaid,
                'user_id' => $mid
            );
            $cartModel->where($where)->setField('goods_number',$gn);
        }else{
            //先从cookie中取出购物车的数组
            $cart = isset($_COOKIE['cart']) ? unserialize($_COOKIE['cart']) : array();
            $key = $gid.'-'.$gaid;
            $cart[$key] = $gn;
            //把这个数组存回到cookie
            $aMonth = 30 * 86400;
            setcookie('cart',serialize($cart),time()+$aMonth);
        }
    }
}