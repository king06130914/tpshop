<?php
namespace app\index\model;

use think\Model;

//公共控制器
class Category extends Model
{
    public function getTree($items, $pid ="cat_pid"){
        $map  = [];
        $tree = [];
        foreach ($items as &$it){ $map[$it['cat_id']] = &$it; }  //数据的ID名生成新的引用索引树
        foreach ($items as &$at){
            $parent = &$map[$at[$pid]];
            if($parent) {
                $parent['children'][] = &$at;
            }else{
                $tree[] = &$at;
            }
        }
        return $tree;
    }

    public function gatPathData(){
        $typeId = input('param.type_id');
        $catId = input('param.cat_id');
        if($typeId){
            $data = db('type')->where('type_id', $typeId)->find();
            $res[] = $data['type_name'];
            return $res;
        }
        if($catId){
            //获取此类的全路径
            $path = $this->field("cat_path")->find($catId);
            $path['cat_path'] .= $catId;
            //查询此类所有父类的分类名称
            $row = $this->field('cat_name')->where(array(
                'cat_id'=>array('in',$path['cat_path']),
            ))->order('cat_path')->select()->toArray();
            $result = array();
            foreach($row as $k=>$v){
                $result[] = $v['cat_name'];
            }
            return $result;
        }
    }

    //获取某个分类的所有子分类
    function getSubs($categorys,$catId=0,$level=1){
        $subs=array();
        foreach($categorys as $item){
            if($item['cat_pid']==$catId){
                $item['level']=$level;
                $subs[]=$item;
                $subs=array_merge($subs,$this->getSubs($categorys,$item['cat_id'],$level+1));
            }
        }
        return $subs;
    }
}