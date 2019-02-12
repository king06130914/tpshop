<?php
namespace app\admin\controller;

use think\Request;

class Goods extends Common
{
    public function index()
    {
        $goods = model('goods');
        //判断是否有查询
        $where = array();
        if (Request::instance()->isGet()) {
            if (!empty($_GET['cat_id'])) {
                $cat_id = input('get.cat_id');
                $where['cat_id'] = $cat_id;
            }
            if (!empty($_GET['brand_id'])) {
                $brand_id = input('get.brand_id');
                $where['brand_id'] = $brand_id;
            }
            if (!empty($_GET['is_onsale']) || (isset($_GET['is_onsale']) && $_GET['is_onsale'] == '0')) {
                $is_onsale = input('get.is_onsale');
                $where['is_onsale'] = $is_onsale;
            }

            $this->assign('search',$where);
        }

        //获得所有分类信息
        $this->assign('cat', model('category')->getTypeInfo());
        $this->assign('ge', "--/");
        //获取所有的品牌信息
        $this->assign('brands', db('brand')->select());
        //获取处理后的品牌信息
        $this->assign('brand', $goods->getBrandInfo());
        //获取处理后的类型信息
        $this->assign('handleCat', $goods->getTypeInfo());
        //是否上架
        $this->assign('onsale', array('0' => '下架', '1' => '在售'));

        //获取分页信息
        $data = fpage(db('goods'), $where, 'goods_id desc', 5);
        $this->assign('page_list', $data['page_list']);
        $this->assign('info', $data['info']);
        $this->setPageInfo('商品列表', '添加商品', url('add'));
        return $this->fetch();
    }

    //添加商品
    public function add(){
        $goods = db('goods');
        //两个逻辑：展示表单，收集表单
        if(Request::instance()->isPost()){
            //设置这个脚本执行的时间， 单位：秒 0:代表一致执行到结束 默认30秒
            set_time_limit(0);
            //获取当前时间
            $time = time();

            //获取提交的数据
            $data = input("post.");
            $validate=validate("Vdate"); //使用验证
            if(!$validate->scene("goods")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            $imgData = uploadFile('goods_img', date('Y-m-d'), 80, 80);
            $imgData2 = uploadFileMore('goods_pic', date('Y-m-d'), 80, 80);

            if(!empty($data['ext_cat_id']['0'])){
                $data['cat_id'] = end($data['ext_cat_id']);
            }
            unset($data['ext_cat_id']);

            $img_desc = $data['img_desc'];
            unset($data['img_desc']);
            $data['goods_img'] = isset($imgData['img'])?$imgData['img']:'';
            $data['goods_thumb'] = isset($imgData['thumb_img'])?$imgData['thumb_img']:'';
            $data['promote_start_time'] = isset($data['promote_start_time'])?strtotime($data['promote_start_time']):'';
            $data['promote_end_time'] = isset($data['promote_end_time'])?strtotime($data['promote_end_time']):'';

            $ga = isset($data['ga'])?$data['ga']:array();
            $attr_price = isset($data['attr_price'])?$data['attr_price']:array();

            unset($data['ga']);
            unset($data['attr_price']);
            //剩下操作
            $data['add_time'] = $time;
            $res = $goods->insert($data, true, true);
            if($res){
                //保存商品相册数据
                if(isset($imgData2)){
                    model('goods')->handleGalary($res, $imgData2, $img_desc);
                }
                //保存商品属性数据
                if(!empty($ga) && is_array($ga)){
                    model('goods')->handleAttr($ga, $attr_price, $res);
                }
                $this->success('新增成功', url('index'));
            }else{
                $this->error("新增失败！");
            }
        }else{
            //获得所有分类信息
            $this->assign('cat', model('category')->getTypeInfo());
            $this->assign('ge',"--/");
            //获得所有品牌信息
            $this->assign('brand',db('brand')->select());

            //获得所有类型信息
            $this->assign('type', db('type')->select());
            $this->setPageInfo('添加商品','商品列表',url('index'));
            return $this->fetch();
        }
    }

    //修改商品
    public function edit(){
        $goods = db('goods');
        //两个逻辑：展示表单，收集表单
        if(Request::instance()->isPost()){
            //设置这个脚本执行的时间， 单位：秒 0:代表一致执行到结束 默认30秒
            set_time_limit(0);

            //获取提交的数据
            $data = input("post.");
            $validate = validate("Vdate"); //使用验证
            if(!$validate->scene("goods")->check($data)){
                $this->error($validate->getError());//内置错误返回
            }

            $imgData = uploadFile('goods_img', date('Y-m-d'), 80, 80);
            $imgData2 = uploadFileMore('goods_pic', date('Y-m-d'), 80, 80);

            if(!empty($data['ext_cat_id']['0'])){
                $data['cat_id'] = end($data['ext_cat_id']);
            }
            unset($data['ext_cat_id']);

            $img_desc = $data['img_desc'];
            unset($data['img_desc']);
            if(isset($imgData['img'])){
                $data['goods_img'] = $imgData['img'];
            }
            if(isset($imgData['thumb_img'])){
                $data['goods_thumb'] = $imgData['thumb_img'];
            }
            if(isset($data['promote_start_time'])){
                $data['promote_start_time'] = strtotime($data['promote_start_time']);
            }
            if(isset($data['promote_end_time'])){
                $data['promote_end_time'] = strtotime($data['promote_end_time']);
            }

            if(!isset($data['is_promote'])){
                $data['is_promote'] = '0';
            }
            if(!isset($data['is_onsale'])){
                $data['is_onsale'] = '0';
            }

            $ga = isset($data['ga'])?$data['ga']:array();
            $attr_price = isset($data['attr_price'])?$data['attr_price']:array();

            unset($data['ga']);
            unset($data['attr_price']);

            //剩下操作
            $res = $goods
                ->where('goods_id', $data['goods_id'])
                ->update($data);
            if($res){
                if(isset($imgData2)){
                    model('goods')->handleGalary($data['goods_id'], $imgData2, $img_desc);
                }
                //保存商品属性数据
                $goodsAttr = db('goodsAttr');
                $goodsAttr->where('goods_id', $data['goods_id'])->delete();
                if(!empty($ga) && is_array($ga)){
                    model('goods')->handleAttr($ga, $attr_price, $data['goods_id']);
                }

                $this->success('修改商品成功！', url('index'));
            }else{
                $this->error("修改商品失败！");
            }
        } else {
            $id = input('param.id');

            //获得所有分类信息
            $this->assign('cat', model('category')->getTypeInfo());
            $this->assign('ge',"--/");

            //获得所有品牌信息
            $this->assign('brand',db('brand')->select());
            $this->assign('info', $goods->find($id));

            //获得所有类型信息
            $this->assign('type', db('type')->select());

            //获得当前商品的属性信息
            $attr = db('goodsAttr')->where(array('goods_id'=>$id))->select();
            $attrData = array();
            if(isset($attr) && is_array($attr)){
                foreach ($attr as $key => $val){
                    $attrData[$val['attr_id']]['attr_value'] = $val['attr_value'];
                    $attrData[$val['attr_id']]['attr_price'] = $val['attr_price'];
                }
            }
            $this->assign('attrData', json_encode($attrData));

            //取出当前商品的图片
            $galary = db('galary');
            $gpData = $galary->where(array('goods_id'=>$id))->select();
            $this->assign('gpData', $gpData);

            $this->setPageInfo('编辑商品信息','商品列表',url('index'));
            return $this->fetch();
        }
    }

    //删除商品
    public function del(){
        $id = input('param.id');

        $goods = db('goods');
        $galary = db('galary');
        //获取当前要删除的数据信息
        $goodsImg = $goods->find($id);
        $z = $goods->delete($id);

        //相册中的图片
        $map = array('goods_id' => $id);
        $galaryImg = $galary->where($map)->select();

        if($z){
            //删除原来的图片
            @unlink('./'.$goodsImg['goods_img']);
            @unlink('./'.$goodsImg['goods_thumb']);

            if($galaryImg){
                $z2 = $galary->where($map)->delete();
                if($z2){
                    //删除相册的图片
                    deleteImage($galaryImg);
                }
            }
            $this->redirect('index',array(),2,'删除商品成功！');
        }else{
            $this -> error('删除商品失败！',url('index'));
        }
    }

    //ajax删除图片的方法
    public function ajaxDelImage(){
        $picId = input("post.id");
        $galary = db('galary');
        //先取出图片的路径
        $pic= $galary->field('img_url,thumb_url')->find($picId);
        //把图片从硬盘上删除
        deleteImage($pic);
        //再把数据库中的图片数据删除掉
        $galary->delete($picId);
    }

    //AJAX获取属性根据类型的ID
    public function ajaxGetAttr(){
        $typeId = input('param.type_id');
        //根据类型id取出属性
        $attrData = db('attribute')->where("type_id = '$typeId'")->select();
        $res = array('state' => '200', 'data' => $attrData);
        return json($res);
    }
}