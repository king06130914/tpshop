<?php
namespace app\index\controller;

use think\Controller;

//公共控制器
class Common extends Controller
{
    //设置页面信息
    public function setPageInfo($title,$keywords,$description,$showNav='0',$scc=array(),$js=array())
    {
        //设置页面
        // 1. 页面标题
        // 2. 关键字
        // 3. 页面描述
        // 4. 导航条是否展开, 1为展开, 其他不展开
        // 5. 页面需要包含的css文件
        // 6. 页面需要包含的js 文件
        $this->assign("page_title",$title);
        $this->assign("page_keywords",$keywords);
        $this->assign("page_description",$description);
        $this->assign("show_nav",$showNav);
        $this->assign("page_css",$scc);
        $this->assign("page_js",$js);

        //获取购物车列表
        $cartData = model('cart')->cartList();
        $this->assign('cartData', $cartData);
        $this->assign('web_user', session('web_user'));

        //获取所有的省份
        $region = db('region');
        $whereprovince['region_type'] = 1;
        $listprovince =  $region->where($whereprovince)->select();
        if(isset($listprovince) && is_array($listprovince)){
            $listpro = array();
            foreach ($listprovince as $key => $val){
                $first = getFirstCharter($val['region_name']);
                $listpro[$first][] = $val['region_name'];
            }
        }
        //按照键从低到高排序
        ksort($listpro);
        $this->assign("listpro",$listpro);
        $this->assign("province_list",$listprovince);

        //获取当前用户添加的地址信息中的省份
        $web_id = session('web_id');
        if(isset($web_id)){
            $address = db('address')->where('user_id', $web_id)->order('address_id desc')->select();
            if(isset($address) && is_array($address)){
                $pro_id = isset($address['0']['province'])?$address['0']['province']:'';
                $pro_name = $region->where('region_id', $pro_id)->find();
                $this->assign("pro_name",$pro_name);
            }
        }
    }

    //获取省和直辖市
    public function get_province(){
        $whereprovince['region_type'] = 1;
        $listprovince =  db('region')->where($whereprovince)->select();
        $this->assign("province_list",$listprovince);
    }
    //获取地级市
    public function get_citys(){
        $listObj = db('region');
        $where['parent_id'] = input('param.province_id');
        $where['region_type'] = 2;
        $list = $listObj->where($where)->select();
        $data=array('status'=>0,'city'=>$list);
        header("Content-type: application/json");
        exit(json_encode($data));
    }
    //获取地级县
    public function get_district(){
        $listObj = db('region');
        $where['parent_id'] = input('param.city_id');
        $where['region_type'] = 3;
        $list = $listObj->where($where)->select();
        $data=array('status'=>0,'district'=>$list);
        header("Content-type: application/json");
        exit(json_encode($data));
    }

    /**
     * 检测是不是一个合法的邮箱
     * @param string $email 邮箱号码
     * @return bool
     */
    public static function isEmail($email)
    {
        $regex = '/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i';
        return !!preg_match($regex, $email);
    }

    /**
     * 检查是不是一个合法的手机号码
     * @param $phone
     * @return bool
     */
    public static function isPhone($phone)
    {
        return !!preg_match("/^1[34578]{1}\d{9}$/", $phone);
    }
}