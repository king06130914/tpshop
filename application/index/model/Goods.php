<?php
namespace app\index\model;

use think\Model;

//公共控制器
class Goods extends Model
{
    //属性id转成属性字符串
    public function convertGoodsAttr($gaid){
        if($gaid){
            $sql = "select GROUP_CONCAT(CONCAT(b.attr_name,':',a.attr_value) SEPARATOR '<br />') gastr from sz_goods_attr a LEFT JOIN sz_attribute b ON a.attr_id = b.attr_id where a.goods_attr_id IN ($gaid)";
            $ret = $this->query($sql);
            return $ret[0]['gastr'];
        }else{
            return '';
        }
    }

    //获取热门商品
    public function getHotGoods($limit=8){
        $mod = $this->field('goods_id,goods_name,goods_brief,shop_price,goods_thumb,goods_img')->where(array(
            'is_hot' => array('eq',1),
            'is_onsale' => array('eq',1),
        ))->limit($limit)->order('sort_num DESC')->select()->toArray();
        return $mod;
    }

    //获取在促销期间的商品
    public function getPromoteGoods($limit=11){
        $now = time();
        $mod = $this->field('goods_id,goods_name,promote_price,goods_img,promote_end_time')->where(array(
            // 'is_best'	=>	array('neq',1),
            'is_onsale'	 => array('eq',1),  			//是否在售
            'is_promote' => array('eq',1),				//是否热销商品
//            'promote_start_time' => array('elt',$now),	//开始促销时间
//            'promote_end_time' => array('egt',$now), 	//促销结束时间
        ))->limit($limit)->order('sort_num ASC')->select()->toArray();
        return $mod;
    }

    //获取热门中的推荐(is_best)商品
    public function getHotBestGoods($limit=5){
        $mod = $this->field('goods_id,goods_name,goods_brief,shop_price,goods_thumb,goods_img')
            ->where(array(
                'is_hot' => array('eq',1),
                'is_best' => array('eq',1),
                'is_onsale' => array('eq',1),
            ))->limit($limit)->order('sort_num ASC')->select()->toArray();
        return $mod;
    }

    //前台商品信息搜索方法
    public function search_goods(){
        // 分类搜索
        $typeId = input('param.type_id');
        $catId = input('param.cat_id');

        $where = array();
        //按分类搜索的
        if($catId){
            //通过此分类获取此分类下的所有子类
            $catModel = db('category');
            $catData = $catModel->where('cat_pid='.$catId)->select();
            $catIds = '';
            if($catData){
                foreach($catData as $k=>$v){
                    $catIds .= $v['cat_id'].',';
                }
                $catData1 = $catModel->where(array(
                    'cat_pid'=>array('in',trim($catIds,',')),
                ))->select();
                foreach($catData1 as $k=>$v){
                    $catIds .= $v['cat_id'].',';
                }
                $catIds .= $catId;
            }else{
                $catIds = $catId;
            }

            if(strstr($catIds, ',')){
                $where['cat_id'] =  array('in', $catIds);
            }else{
                $where['cat_id'] =  $catIds;
            }
        }

        //按类型搜索的
        if($typeId){
            $where['type_id'] = $typeId;
        }
        //头部输入框搜索
        $search_goods = input('get.search_goods');
        if($search_goods){
            $where['goods_name'] = array('like','%'.$search_goods.'%');
        }
        //品牌的搜索
        $brand_id = input('param.brand');
        if($brand_id){
            $where['brand_id'] = $brand_id;
        }
        //价格搜索
        $price = input('param.price');
        if($price){
            $price = explode('-',$price);
            $where['shop_price'] = array('between',array($price[0],$price[1]));
        }
        //商品属性的搜索
        $sa = input('param.search_attr');
        if($sa){
            $gaModel = db('Goods_attr');
            $sa = explode('-',$sa);
            //先定义一个数组，第一个满足条件的属性id
            $_att1 = null;
            //循环每个属性
            foreach($sa as $k=>$v){
                if($v != '0'){
                    $_v = explode(',',$v);
                    //到商品属性表中搜索有这个属性以及值的商品的id
                    $_attrGoodsId = $gaModel->field('GROUP_CONCAT(goods_id) goods_id')
                        ->where(array(
                            'attr_id' => $_v[1],
                            'attr_value' => $_v[0],
                        ))->find();
                    //如果是第一个就保存起来
                    if($_att1 == null){
                        $_att1 = explode(',',$_attrGoodsId['goods_id']);
                    }else{
                        //如果$_attr1不为空，保存的就是上一次满足条件的商品id，那么就和这次的取交集
                        $_attrGoodsId = explode(',',$_attrGoodsId);
                        $_att1 = array_intersect($_att1,$_attrGoodsId);
                        //如果已经是空了，就直接退出，不用再取比较了
                        if(empty($_att1))
                            break;
                    }
                }
            }
            //$_att1保存的就是满足所有条件的商品id
            if($_att1)
                $where['goods_id'] = array('in',$_att1);
        }
        /********排序*********/
        $orderBy = 'sales_volume';  //排序字段
        $orderWay = 'DESC'; //排序方式
        //接收用户传的排序参数
        $ob = input('param.ob');
        $ow = input('param.ow');
        if($ob && in_array($ob,array('xl','shop_price','add_time'))){
            $orderBy = $ob;
            //如果是价格排序，才接受ow变量
            if($ob == 'shop_price' && $ow && in_array($ow,array('asc','desc'))){
                $orderWay = $ow;
            }
        }

        $where['is_onsale'] = 1;
        /*************取出商品*************/
        $data = $this->field('goods_id,goods_name,goods_img,shop_price')
            ->where($where)->group('goods_id')
            ->order("$orderBy $orderWay")
            ->paginate('2',false,['query'=>request()->param()]);
        return $data;
    }
}