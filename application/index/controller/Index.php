<?php
namespace app\index\controller;

use think\Request;

class Index extends Common
{
    public function index()
    {
        //商品类Goods
        $mod_goods = model('goods');
        //获取热门商品
        $getHotgoods=$mod_goods->getHotGoods(6);
        $getHotBestGoods=$mod_goods->getHotBestGoods(5);
        // 获取促销中的商品
        $goodsPromote = $mod_goods ->getPromoteGoods();

        $type = db('category');
        //取出数码家电下二级分类
        $map = array();
        $map['cat_pid'] = array('in','1,2');
        $typeData = $type->where($map)->select();
        //把二级分类的所有type_id组合成字符串
        $data = array();
        foreach($typeData as $v){
            $data[] = $v['cat_id'];
        }
        $typeData2 = implode(',',$data);
        //先查询数码家电下所有分类
        $where = array();
        $where['cat_pid'] = array('in',$typeData2);
        $typeData3 = $type->where($where)->select();
        $info ='';
        foreach($typeData3 as $v){
            $info .= $v['cat_id'].',';
        }
        $allTypedata = $info.$typeData2.',1,2';
        //先查询数码家电下所有分类的商品
        $where = array();
        $where['cat_id'] = array('in',$allTypedata);
        $goodsData = db('goods')->field('goods_id,goods_name,shop_price,goods_img')
            ->where($where)->order('goods_id desc')->select();

        //获取类型数据
        $typeData = db("type")->select();

        //获取分类数据
        $cat = db("category")->select();
        $catData = model('category')->getTree($cat);

        //获取类型数据
        $adsData = db("ads")->select();

        //猜你喜欢
        $sugglike = array();
        if(session('web_id')){
            $condition = array(
                'a.user_id' => session('web_id'),
            );
            $types = db('collection')->alias('a')->field('b.cat_id')
                ->join('sz_goods b', 'a.goods_id = b.goods_id','left')
                ->where($condition)->select();
            if(isset($types) && is_array($types)){
                $types = array_column($types,'cat_id');
                $types = array_unique($types);
            }
            $likewhere = array();
            $likewhere['cat_id'] = array('in', $types);

            $sugglike = db('goods')->where($likewhere)->select();
        }else{
            $sugglike = db('goods')->order('sales_volume desc')->limit(6)->select();
        }

        //友情链接
        $linkData = db('links')->select();

        //获取资讯信息
        $articleData = db("Article")->field('a.*,b.cat_name')->alias('a')
                ->join('sz_article_cat b', 'a.article_cat_id = b.cat_id', 'left')
                ->where('a.article_cat_id', 12)
                ->order('a.article_id desc')->select();

        $this->assign(array(
            'getHotgoods' => $getHotgoods, //热门商品数据
            'getHotBestGoods' => $getHotBestGoods, //特卖商品数据
            'goodsPromote' => $goodsPromote, //促销商品数据
            'typeData' => $typeData,    //类型数据
            'catData' => $catData,    //分类数据
            'cat' => $cat,    //分类数据
            'goodsData' => $goodsData,  //分类下商品信息
            'adsData' => $adsData,     //广告数据
            'sugglike' => $sugglike,     //广告数据
            'linkData' => $linkData,  //友情链接数据
            'articleData' => $articleData, //文章数据
        ));
        // 设置页面信息
        $this->setPageInfo("嘉悠商城","关键字","描述","1");
        return $this->fetch();
    }

    public function detail()
    {
        //接收商品信息
        $goodsId = input('param.goods_id');

        $goodsModel = db('goods');
        //取出商品的基本信息
        $info = $goodsModel->find($goodsId);
        //根据当前商品id查询商品相册表里面的数据
        $goodgal = db('galary')->field('img_id,img_url,thumb_url')->where('goods_id='.$goodsId)->order('img_id desc')->select();

        /*********取出商品属性********/
        //取出商品的单选属性
        $gaModel = db('goods_attr');
        $where['a.goods_id'] = $goodsId;
        $where['b.attr_type'] = 1;
        $gaData_1 = $gaModel->alias('a')->field('a.*, b.attr_name')
            ->join('sz_attribute b','a.attr_id=b.attr_id','left')
            ->where($where)->select();

        //把属性相同的放到一起
        $gaData1 = array();
        if(is_array($gaData_1)){
            foreach($gaData_1 as $k=>$v){
                $gaData1[$v['attr_name']][] = $v;
            }
        }

        //取出商品唯一属性
        $where['b.attr_type'] = 0;
        $gaData2 = $gaModel->alias('a')->field('a.*,b.attr_name')
            ->join('sz_attribute b','a.attr_id=b.attr_id','left')
            ->where($where)->select();

        //通过品牌id取出该商品的品牌的信息
        $gbData = db('Brand')->find($info['brand_id']);

        //用户还喜欢
        $type_id = $info['type_id'];
        $likeData = $goodsModel->where('type_id='.$type_id)->select();
        session("like.$type_id", $type_id, 'think');

        //输出导航条
        $category = model('category');
        $pathData = $category->gatPathData();
        //查询该商品的评论
        $message = db('Message');
        $messageData = $message->where(array('goods_id'=>$goodsId))
            ->order('add_time desc')
            ->paginate('2',false,['query'=>request()->param()]);

        //通过商品id查出订单id，再查出订单状态
        $orderstate = db('Order_goods')->field('b.order_status')->alias('a')
            ->join('sz_order b', 'a.order_id = b.order_id','left')
            ->where(array(
                'order_u_id'=>session('web_id'),
                'goods_id'=>$goodsId,
            ))->select();
        $orderInfo = array();
        if(isset($orderstate) && is_array($orderstate)){
            $orderInfo = array_column($orderstate,'order_status');
            $orderInfo = array_unique($orderInfo);
        }

        //获取类型数据
        $typeData = db("type")->select();

        //获取分类数据
        $cat = db("category")->select();
        $catData = model('category')->getTree($cat);

        //把取出来的属性assign到页面中
        $this->assign(array(
            'goodsInfo' => $info,  	    // 基本信息
            'goodgal' => $goodgal,		// 图片
            'typeData' => $typeData,	//类型数据
            'catData' => $catData,		// 分类数据
            'gaData1' => $gaData1,	    // 单选属性
            'gaData2' => $gaData2,	    // 唯一属性
            'gbData' => $gbData,	    // 商品的品牌信息
            'likeData' => $likeData,	// 用户还喜欢
            'pathData' => $pathData,    //导航条数据
            'messageData' => $messageData,  //评论信息
            'orderInfo' => $orderInfo,     //订单状态
        ));
        //把点击的商品存到session
        session("historyData.$goodsId", $info, 'think');
        //设置页面信息
//        $this->setPageInfo($info['goods_name']."-商品详情页-","详情关键字","描述","0",array('ShopShow','MagicZoom'),array('MagicZoom','ShopShow','shade'));
        $this->setPageInfo($info['goods_name']."-商品详情页-","详情关键字","描述","0",array('ShopShow','MagicZoom'),array('MagicZoom','ShopShow','shade'));
        return $this->fetch();
    }

    //加载新闻公告模板
    public function news(){
        $id = input('param.id');
        $article = db('article')->where('article_id', $id)->find();

        //获取类型数据
        $typeData = db("type")->select();

        //获取分类数据
        $cat = db("category")->select();
        $catData = model('category')->getTree($cat);

        //获取浏览历史数据
        $historyData = session('historyData');
        if(isset($historyData) && is_array($historyData)){
            $historyData = array_reverse($historyData);
        }

        $this->assign(array(
            'article' => $article,
            'typeData' => $typeData,    //类型数据
            'catData' => $catData,    //分类数据
            'historyData' => $historyData,//浏览历史数据
        ));
        $this->setPageInfo("新闻公告页","详情关键字","描述");
        return $this->fetch();
    }

    //ajax加入收藏
    public function addcollect(){
        $collect = db('Collection');
        if(Request::instance()->isAjax()){
            $id = input('post.id');
            $attr = input('post.attr');
            $mid = session('web_id');
            if($mid){
                $where = array(
                    'goods_id'=>$id,
                    'user_id'=>$mid,
                    'goods_attr_id'=>isset($attr)?trim($attr,','):'',
                );
                $findData = $collect->where($where)->find();
                if($findData){
                    $res = array('state' => '401', 'info' => '此商品已经收藏过！');
                    return json($res);
                }
                //添加数据到收藏中
                $info = $collect->insert(array(
                    'goods_id'=>$id,
                    'user_id'=>$mid,
                    'goods_attr_id'=>isset($attr)?trim($attr,','):'',
                    'add_time'=>time(),
                ));
                if(!$info){
                    $res = array('state' => '400', 'info' => '收藏失败！');
                }else{
                    $res = array('state' => '200', 'info' => '收藏成功！');
                }
            }else{
                $res = array('state' => '401', 'info' => '请先登录！');
            }
            return json($res);
        }
    }

    //ajax清空浏览历史
    public function emptyHistory(){
        if(Request::instance()->isAjax()) {
            //清空浏览历史
            if (session('?historyData')) {
                session("historyData", null);
                $res = array('state' => '200', 'info' => '清空浏览历史成功！');
            } else {
                $res = array('state' => '400', 'info' => '浏览历史为空！');
            }
            return json($res);
        }
    }
}