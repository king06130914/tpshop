<?php
namespace app\admin\model;

use think\Model;

class Address extends Model
{
    //制作收货人地址信息
    public function userAddress(){
        $address = $this->select();
        $data = array();
        foreach($address as $v){
            $region = db('region');
            $province = $region->where('region_id', $v['province'])->find();
            $city = $region->where('region_id', $v['city'])->find();
            $district = $region->where('region_id', $v['district'])->find();
            $data[$v['address_id']] = $province['region_name'].$city['region_name'].$district['region_name'].$v['detail_address'];
        }
        return $data;
    }
}