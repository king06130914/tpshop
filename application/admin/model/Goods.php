<?php
namespace app\admin\model;

use think\Model;

class Goods extends Model
{
    //处理品牌信息数据
    public function getBrandInfo(){
        $brands = db('brand')->select();
        $data = array();
        foreach($brands as $v){
            $data[$v['brand_id']] = $v['brand_name'];
        }
        return $data;
    }
    //处理类型信息数据
    public function getTypeInfo(){
        $types = db('category')->select();
        $data = array();
        foreach($types as $v){
            $data[$v['cat_id']] = $v['cat_name'];
        }
        return $data;
    }
    //处理商品相册信息
    public function handleGalary($id, $imgData, $img_desc){
        $galary = db('galary');
        $photos = array();
        $len = count($imgData['img']);
        for($i = 0; $i < $len;$i++){
            $photos[] = array(
                'goods_id' => $id,
                'img_url' => $imgData['img'][$i],
                'thumb_url' => $imgData['thumb_img'][$i],
                'img_desc' => $img_desc[$i],
            );
        }
        $galary->insertAll($photos);
    }

    //处理商品属性信息
    public function handleAttr($ga, $attr_price, $goods_id){
        $goodsAttr = array();
        foreach ($ga as $key => $val){
            $attr = array();
            $attr['goods_id'] = $goods_id;
            $attr['attr_id'] = $key;
            $attr['attr_value'] = isset($val['0'])?$val['0']:'';
            $attr['attr_price'] = isset($attr_price[$key]['0'])?$attr_price[$key]['0']:'';
            array_push($goodsAttr, $attr);
        }
        db('goodsAttr')->insertAll($goodsAttr, true);
    }

}