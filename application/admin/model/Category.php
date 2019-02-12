<?php
namespace app\admin\model;

use think\Model;

class Category extends Model
{
    //自定义方法实现分类信息的添加
    public function saveData($typeinfo){
        //两个步骤
        //①根据已有的字段生成一条记录
        $newid = $this->insert($typeinfo,true,true);
        //②根据新纪录的主键id值进一步制作cat_path
        //cat_path处理：顶级/非顶级
        if($typeinfo['cat_pid']==0){
            $path = '0,';
        }else{
            $pinfo = $this->find($typeinfo['cat_pid']);
            $path = $pinfo['cat_path'].$typeinfo['cat_pid'].',';
        }
        $data['cat_path'] = $path;
        return $this->where("cat_id='{$newid}'")->update($data);
    }

    //自定义方法实现分类信息的修改
    public function updData($typeinfo){
        //两个步骤
        $typeId = $typeinfo['cat_id'];
        //②根据新纪录的主键id值进一步制作auth_path
        //auth_path处理：顶级/非顶级分类
        if($typeinfo['cat_pid']==0){
            $path = '0,';
        }else{
            $pinfo = $this->find($typeinfo['cat_pid']);
            $path = $pinfo['cat_path'].$typeinfo['cat_pid'].',';
        }
        $typeinfo['cat_path'] = $path;
        return $this->where("cat_id='$typeId'")->update($typeinfo);
    }

    //查询所有的父id，判断删除的分类下是否有子分类
    public function getAllPid(){
        $cat_pid = $this->field('cat_pid')->select();
        $pid_list = array();
        foreach($cat_pid as $v){
            $pid_list[] = $v['cat_pid'];
        }
        return $pid_list;
    }

    public function getTypeInfo(){
        return $this->field("*,concat(cat_path,cat_id) as bpath")->order('bpath')->select()->toArray();
    }
}