<?php
namespace app\common\validate;
use think\Validate;

class  Vdate extends Validate{
    //每个字段对应一个规则，这是第一层
    protected $rule=[
        ["mg_name","require|max:20|min:3","名称不能为空|名称不能超过10个字符|名称不能少于3个字符"],
        ["mg_pwd","require|min:6","密码不能为空|密码不能少于6个字符"],
        ["mg_pwd_confirm","require|min:6|confirm","密码不能为空|密码不能少于6个字符"],
        ["mg_role_id","require","请选择角色！"],
        ["role_name","require|min:2","角色不能为空|角色名称不能少于2个字符"],
        ["auth_name","require|min:4","权限不能为空|权限名称不能少于4个字符"],
        ["password","require|min:6","密码不能为空|密码不能少于6个字符"],
        ["title","require|min:2","网站名称不能为空|网站名称不能少于2个字符"],
        ["alink","require","网站链接不能为空"],
        ["pay_name","require|min:2","支付方式名称不能为空|支付方式名称不能少于2个字符"],
        ["pay_desc","require|min:3","支付方式描述不能为空|支付方式描述不能少于3个字符"],
        ["shipping_name","require|min:4","送货方式名称不能为空|送货方式名称不能少于4个字符"],
        ["shipping_desc","require|min:4","送货方式描述不能为空|送货方式描述不能少于4个字符"],
        ["article_title","require|min:2","文章名称不能为空|文章名称不能少于2个字符"],
        ["article_cat_id","require","文章分类不能为空"],
        ["article_content","require|min:6","文章内容不能为空|文章内容不能少于6个字符"],
        ["cat_name","require|min:2","分类名称不能为空|分类名称不能少于2个字符"],
        ["goods_name","require|min:3","商品名称不能为空|商品名称不能少于3个字符"],
        ["cat_id","require","商品分类不能为空"],
        ["shop_price","require|number","本店售价不能为空|售价必须是数字"],
//        ["cat_name","require","分类名称不能为空"],
        ["type_name","require","类型名称不能为空"],
        ["brand_name","require|min:2","商品品牌名称不能为空|商品品牌名称不能少于2个字符"],
        ["brand_url","require","商品品牌网址不能为空"],
        ["attr_name","require|min:2","属性名称不能为空|属性名称不能少于2个字符"],

        //前台
        ["username","require|min:3|max:20","用户名不能为空|用户名不能少于3个字符|名称不能超过10个字符"],
        ["password","require|min:6","密码不能为空|密码不能少于6个字符"],
        ["password_confirm","require|min:6|confirm","密码不能为空|密码不能少于6个字符"],
        ["user_email","require","邮箱不能为空"],
        ["user_tel","require","电话不能为空"],
        /*  ["id","number","必须是数字"],
          ["status","number|in:1,0,-1","必须是数字|必须是是0,-1,1"],*/
    ];

    //应用的场景，这是第二层
    protected $scene=[
        "manager"=>["mg_name","mg_pwd","mg_role_id"],
        "manager_edit"=>["mg_name","mg_role_id"],
        "manager_pwd"=>["mg_pwd","mg_pwd_confirm"],
        "role"=>["role_name"],
        "auth"=>["auth_name"],
        "user"=>["password"],
        "links"=>["title", "alink"],
        "payment"=>["pay_name", "pay_desc"],
        "shipping"=>["shipping_name", "shipping_desc"],
        "article"=>["article_title", "article_cat_id", "article_content"],
        "article_cat"=>["cat_name"],
        "goods"=>["goods_name", "cat_id", "shop_price"],
        "category"=>["cat_name"],
        "type"=>["type_name"],
        "brand"=>["brand_name", "brand_url"],
        "attribute"=>["attr_name"],

        //前台注册
        "regist"=>["username","password","password_confirm","user_email","user_tel"],
    ];
}