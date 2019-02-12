<?php
// 应用公共文件
/*
 * 应用公共函数文件，函数不能定义为public类型，
 * 如果我们要使用我们定义的公共函数，直接在我们想用的地方直接调用函数即可。
 * */
/**
 * 发送邮件
 * @param  string $to        接收人邮件地址
 * @param  string $title     邮件标题
 * @param  string $contents  邮件内容 支持HTML格式
 * @param  string $type      判断是否要加附件
 * @param  string $accessory 附件的名字
 * @return                   成功返回true,失败返回错误信息
 */
function sendEmail($to,$title,$contents,$type = '',$accessory =''){
    $mail = new \mailer\PHPMailer();
    $mail->IsSMTP();
    $mail->CharSet ="UTF-8";//编码
    $mail->Debugoutput = 'html';// 支持HTML格式
    $mail->Host = 'smtp.163.com';//HOST 地址
//    $mail->SMTPSecure = "ssl";// 使用ssl协议方式</span><span style="color:#333333;">
//    $mail->Port = 994;// 163邮箱的ssl协议方式端口号是465/994
    $mail->Port = 25;//用非ssl协议方式 的端口
    $mail->SMTPAuth = true;
    $mail->Username = "dingke0613@163.com";//用户名
    $mail->Password = "ding06130914";//密码
    $mail->SetFrom('dingke0613@163.com','dingke');//发件人地址, 发件人名称
    $mail->AddAddress($to);//收信人地址
    //$mail->Subject = "=?utf-8?B?" . base64_encode() . "?=";
    if (!empty($type)) {
        $mail->AddAttachment($type,$accessory); // 添加附件,并指定名称
    }
    $mail->Subject = $title;//邮件标题
    $mail->MsgHTML($contents);
    if ($mail->Send()){
        return true;
    }else{
        return false;
    }
}

/*
 * $name为表单上传的name值
 * $filePath为为保存在入口文件夹public下面uploads/下面的文件夹名称，没有的话会自动创建
 * $width指定缩略宽度
 * $height指定缩略高度
 * 自动生成的缩略图保存在$filePath文件夹下面的thumb文件夹里，自动创建
 * @return array 一个是图片路径，一个是缩略图路径，如下：
 * array(2) {
      ["img"] => string(57) "uploads/img/20171211\3d4ca4098a8fb0f90e5f53fd7cd71535.jpg"
      ["thumb_img"] => string(63) "uploads/img/thumb/20171211/3d4ca4098a8fb0f90e5f53fd7cd71535.jpg"
    }
 */
function uploadFile($name,$filePath,$width,$height)
{
    $file = request()->file($name);
    if($file){
        $filePaths = ROOT_PATH . 'public' . DS . 'uploads' . DS .$filePath;
        if(!file_exists($filePaths)){
            mkdir($filePaths,0777,true);
        }
        $info = $file->move($filePaths);
        if($info){
            $imgpath = 'uploads/'.$filePath.'/'.$info->getSaveName();
            $image = \think\Image::open($imgpath);
            $date_path = 'uploads/'.$filePath.'/thumb';
            if(!file_exists($date_path)){
                mkdir($date_path,0777,true);
            }
            $thumb_path = $date_path.'/'.$info->getFilename();
            $image->thumb($width, $height)->save($thumb_path);
            $data['img'] = $imgpath;
            $data['thumb_img'] = $thumb_path;
            return $data;
        }else{
            // 上传失败获取错误信息
            return $file->getError();
        }
    }
}

function uploadFileMore($name,$filePath,$width,$height)
{
    $files = request()->file($name);
    if($files){
        $data = array();
        foreach($files as $file){
            $filePaths = ROOT_PATH . 'public' . DS . 'uploads' . DS .$filePath;
            if(!file_exists($filePaths)){
                mkdir($filePaths,0777,true);
            }
            $info = $file->move($filePaths);
            if($info){
                $imgpath = 'uploads/'.$filePath.'/'.$info->getSaveName();
                $image = \think\Image::open($imgpath);
                $date_path = 'uploads/'.$filePath.'/thumb';
                if(!file_exists($date_path)){
                    mkdir($date_path,0777,true);
                }
                $thumb_path = $date_path.'/'.$info->getFilename();
                $image->thumb($width, $height)->save($thumb_path);
                $data['img'][] = $imgpath;
                $data['thumb_img'][] = $thumb_path;
            }else{
                // 上传失败获取错误信息
                return $file->getError();
            }
        }
        return $data;
    }
}

//删除图片的方法
function deleteImage($pic){
    @unlink('./'.$pic['img_url']);
    @unlink('./'.$pic['thumb_url']);
}

/**
 * 分页显示
 * @param  object $model   数据表的model对象
 * @param  array $where    查询条件
 * @param  string $order   排序方式
 * @param  string $per     每页显示的条数
 * @param  string $field   查询的字段
 * @return array  $data
 */
function fpage($model, $where=array(), $order='id desc', $per = 10, $field='*'){
    //①获得记录综条数
    $total = $model->count();
    //②实例化分页类对象
    $page = new \app\tool\Page($total,$per);
    //执行查询
    $info = $model->field($field)->where($where)->order($order)->limit($page->limit)->paginate($per,false,['query'=>request()->param()]);
    //④获得页码列表
    $page_list = $page->fpage();
    $data = array();
    $data['info'] = $info;
    $data['page_list'] = $page_list;
    return $data;
}
/**
 * 获取中文字符拼音首字母
 * @param $str 中文字符
 * @return null|string
 */
function getFirstCharter($str)
{
    if (empty($str)) {
        return '';
    }
    $fchar = ord($str{0});
    if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
    $s1 = iconv('UTF-8', 'gb2312', $str);
    $s2 = iconv('gb2312', 'UTF-8', $s1);
    $s = $s2 == $str ? $s1 : $str;
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if ($asc >= -20319 && $asc <= -20284) return 'A';
    if ($asc >= -20283 && $asc <= -19776) return 'B';
    if ($asc >= -19775 && $asc <= -19219) return 'C';
    if ($asc >= -19218 && $asc <= -18711) return 'D';
    if ($asc >= -18710 && $asc <= -18527) return 'E';
    if ($asc >= -18526 && $asc <= -18240) return 'F';
    if ($asc >= -18239 && $asc <= -17923) return 'G';
    if ($asc >= -17922 && $asc <= -17418) return 'H';
    if ($asc >= -17417 && $asc <= -16475) return 'J';
    if ($asc >= -16474 && $asc <= -16213) return 'K';
    if ($asc >= -16212 && $asc <= -15641) return 'L';
    if ($asc >= -15640 && $asc <= -15166) return 'M';
    if ($asc >= -15165 && $asc <= -14923) return 'N';
    if ($asc >= -14922 && $asc <= -14915) return 'O';
    if ($asc >= -14914 && $asc <= -14631) return 'P';
    if ($asc >= -14630 && $asc <= -14150) return 'Q';
    if ($asc >= -14149 && $asc <= -14091) return 'R';
    if ($asc >= -14090 && $asc <= -13319) return 'S';
    if ($asc >= -13318 && $asc <= -12839) return 'T';
    if ($asc >= -12838 && $asc <= -12557) return 'W';
    if ($asc >= -12556 && $asc <= -11848) return 'X';
    if ($asc >= -11847 && $asc <= -11056) return 'Y';
    if ($asc >= -11055 && $asc <= -10247) return 'Z';
    return null;
}

function ip() {
    //strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
    return $res;
}

//判断是否URL网址（PHP代码/函数）
function is_url($v){
    $pattern="#(http|https)://(.*\.)?.*\..*#i";
    if(preg_match($pattern,$v)){
        return true;
    }else{
        return false;
    }
}
