<?php
namespace app\index\controller;

class Search extends Common
{
    //搜索列表页
    public function search(){
        $goodsModel = db('goods');

        $typeId = input('param.type_id');
        $catId = input('param.cat_id');

        //计算分类分支
        $category = model('category');
        $pathData = $category->gatPathData();
        /*********************品牌搜索****************************/
        $where = array();
        if($catId){
            $cates = db('category')->select();
            $cateInfo = $category->getSubs($cates, $catId);
            $catIds = array_column($cateInfo, 'cat_id');
            $where['cat_id'] = array('in', $catIds);
        }
        if($typeId){
            $catId = $typeId;
            $where['type_id'] = $typeId;
        }
        //读取缓存数据,如果没有缓存就执行查询
//        $brandInfo = cache('brand'.$catId);//存储缓存
//        if(!$brandInfo){
            $brand = $goodsModel->field('DISTINCT brand_id')
                ->where($where)->select();
            $brandInfo = array();
            foreach($brand as $k => $v){
                $brandModel = db('brand');
                $info = $brandModel->field('brand_id,brand_name,brand_logo')
                    ->where(array('brand_id'=>array('eq',$v['brand_id'])))
                    ->find();
                $brandInfo[] = $info;
            }
            //把数据放到缓存里
//            cache('brand'.$catId,$brandInfo,1800);
//        }


        /********************价格分段搜索*************************/
//        //读取缓存数据,如果没有缓存就执行查询
//        $_sprice = Cache::get('price'.$catId);
//        if(!$_sprice){
            //算法: 取出这个分类下商品的最高价和最低价
            $price = $goodsModel->field('MIN(shop_price) minprice,MAX(shop_price) maxprice')
                                ->where($where)->select();
            //最高价和最低价 分七段
            $priceSection = 6;
            $_sprice = array();
            //计算每个价格区间
            $sprice = ceil(($price[0]['maxprice'] - $price[0]['minprice']) / $priceSection);
            $fristPrice = $price[0]['minprice'];
            for($i=0;$i<$priceSection;$i++){
                if($i < ($priceSection-1)){
                    $start = floor($fristPrice/100)*100;
                    $end = floor(($fristPrice+$sprice)/100)*100-1;
                    //先查询数据库判断这价格区间是否有商品,如果没有商品就删除这个价格区间
                    $where['is_onsale'] = 1;
                    $where['shop_price'] = array('between', array($start,$end));
                    $goodsCount = $goodsModel->where($where)->count();
                    $fristPrice += $sprice;
                    if($goodsCount == 0){
                        continue;
                    }
                    $_sprice[] =  $start .'-'. $end ;
                }else{
                    $start = floor($fristPrice/100)*100;
                    $end = ceil(($fristPrice+$sprice)/100)*100;
                    //先查询数据库判断这价格区间是否有商品,如果没有商品就删除这个价格区间
                    $where['is_onsale'] = 1;
                    $where['shop_price'] = array('between', array($start,$end));
                    $goodsCount = $goodsModel->where($where)->count();
                    $fristPrice += $sprice;
                    if($goodsCount == 0){
                        continue;
                    }
                    $_sprice[] =  $start .'-'. $end ;
                }
            }

//            //把计算好的放到缓存里
//            Cache::set('price'.$catId,$_sprice,1800);
//        }
        /*************************可以搜索的属性*************************************/
//        //读取筛选属性的缓存,如果没有就执行查询
//        $attrData = Cache::get('attrData'.$catId);
//        if(!$attrData && $catId){
            $typeIds = $goodsModel->field('DISTINCT type_id')
                ->where($where)->select();
            $typeIdes = array_column($typeIds, 'type_id');

            //根据筛选属性的ID取出这些属性的名称以及每个属性所拥有的值
            $attrModel = db('attribute');
            $map['type_id'] = array('in', $typeIdes);
            $map['attr_type'] = 1;
            $attrData = $attrModel->field('attr_id,attr_name')
                ->where($map)->select();

            //循环这些筛选属性,取出这些属性中有商品的值
            $gaModel = db('goods_attr');
            foreach($attrData as $k=>$v){
                $attrValues = $gaModel->field(' DISTINCT attr_value')
                    ->where(array('attr_id' =>array('eq',$v['attr_id'])))
                    ->select();
                //如果这个属性下没有这个商品, 那么就删除这个属性
                if(!$attrValues){
                    unset($attrData[$k]);
                }else{
                    $attrData[$k]['attr_value']   = $attrValues;
                }
            }

//            //缓存筛选属性(第三个参数为缓存时间)
//            Cache::set('attrData'.$catId,$attrData,1800);
//        }

        $historyData = session('historyData');
        if(isset($historyData) && is_array($historyData)){
            $historyData = array_reverse($historyData);
        }

        //获取类型数据
        $typeData = db("type")->select();

        //获取分类数据
        $cat = db("category")->select();
        $catData = model('category')->getTree($cat);

        //获取商品信息
        $goodslist = model('goods')->search_goods();

        $this->assign(array(
            'pathData' => $pathData,//导航条数据
            'price'		=> $_sprice,    //价格数据
            'attrData'  => $attrData,   //属性数据
            'brandInfo' => $brandInfo,  //品牌信息
            'goodslist'	=> $goodslist, //商品列表
            'typeData'	=> $typeData, //商品列表分页
            'catData' => $catData,    //分类数据
            'catId'		=> $catId,   //分类id
            'historyData' => $historyData,//浏览历史数据
        ));
        // 设置页面信息
//        $this->setPageInfo("搜索页","搜索页","搜索页","0",array('ShopShow'),array('ShopShow','shade'));
        $this->setPageInfo("搜索页","搜索页","搜索页","0",array('ShopShow'),array('shade'));
        return $this->fetch('index/catelist');
    }
}