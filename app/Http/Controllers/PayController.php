<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use App\Model\OrderModel;
class PayController extends Controller
{
    public $app_id;
    public $gate_way;
    public $notify_url;
    public $return_url;
    public $rsaPrivateKeyFilePath;
    public $aliPubKey;
    public function __construct()
    {
        $this->app_id = env('ALIPAY_APPID');//支付宝分配给开发者的应用ID
        $this->gate_way = 'https://openapi.alipaydev.com/gateway.do';//支付网关
        $this->notify_url = env('ALIPAY_NOTIFY_URL');//异步通知地址
        $this->return_url = env('ALIPAY_RETURN_URL');//回调地址
        $this->rsaPrivateKeyFilePath = storage_path('app/private.pem');    //应用私钥
        $this->aliPubKey = storage_path('app/public.pem'); //支付宝公钥
    }
    public function test()
    {
        echo $this->aliPubKey;echo '</br>';
        echo $this->rsaPrivateKeyFilePath;echo '</br>';
    }
    /**
     * 订单支付
     * @param $oid
     */
    public function pay($order_id)
    {
        //验证订单状态 是否已支付 是否是有效订单
        $order_info = OrderModel::where(['order_id'=>$order_id])->first()->toArray();
        //dd($order_info);
//        echo '<pre>';print_r($order_info);echo '</pre>';echo '<hr>';
//        //判断订单是否已被支付
//        if($order_info['pay_time']>0){
//            die("订单已支付，请勿重复支付");
//        }
//        //判断订单是否已被删除
//        if($order_info['is_delete']==1){
//            die("订单已被删除，无法支付");
//        }
        //业务参数
        $bizcont = [
            'subject'           => 'Lening-Order: ' .$order_id,//商品的标题/交易标题/订单标题/订单关键字等。
            'out_trade_no'      => $order_info['order_no'],//商户网站唯一订单号
            'total_amount'      => $order_info['order_amount'] / 100,// 	订单总金额
            'product_code'      => 'QUICK_WAP_WAY',//销售产品码
        ];
        //公共参数
        $data = [
            'app_id'   => $this->app_id,//支付宝分配给开发者的应用ID
            'method'   => 'alipay.trade.wap.pay',//接口名称
            'format'   => 'JSON',//仅支持JSON
            'charset'   => 'utf-8',//请求使用的编码格式，如utf-8,gbk,gb2312等
            'sign_type'   => 'RSA2',//签名算法类型
            'timestamp'   => date('Y-m-d H:i:s'),//发送请求的时间
            'version'   => '1.0',//调用的接口版本
            'notify_url'   => $this->notify_url,         // 同步通知地址
            'return_url'   => $this->return_url,       //异步通知地址
            'biz_content'   => json_encode($bizcont),//业务请求参数的集合
        ];
        //签名
        $sign = $this->rsaSign($data);//类似于客户端签名
        $data['sign'] = $sign;//得到签名验证签名
        $param_str = '?';
        foreach($data as $k=>$v){
            $param_str .= $k.'='.urlencode($v) . '&';
        }
        $url = rtrim($param_str,'&');
        $url = $this->gate_way . $url;
        header("Location:".$url);       // 重定向到支付宝支付页面
    }
    public function rsaSign($params) {
        return $this->sign($this->getSignContent($params));
    }
    protected function sign($data) {//签名函数利用商户私钥对待签名字符串进行签名，并进行Base64编码
        $priKey = file_get_contents($this->rsaPrivateKeyFilePath);
        dd($priKey);
        $res = openssl_get_privatekey($priKey);
       // dd($res);
        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
        openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);//
        if(!$this->checkEmpty($this->rsaPrivateKeyFilePath)){
            openssl_free_key($res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }
    public function getSignContent($params) {//.筛选并排序拼接
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->characet($v, 'UTF-8');
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }
    protected function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
    }
    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset) {
        if (!empty($data)) {
            $fileType = 'UTF-8';
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }
        return $data;
    }
    /**
     * 支付宝异步通知
     */
    public function notify()
    {
        $p = json_encode($_POST);
        $log_str = "\n>>>>>> " .date('Y-m-d H:i:s') . ' '.$p . " \n";
        file_put_contents('logs/alipay_notify',$log_str,FILE_APPEND);
        echo 'success';
        //TODO 验签 更新订单状态
    }
    /**
     * 支付宝同步通知
     */
    public function aliReturn()
    {
        echo '<pre>';print_r($_GET);echo '</pre>';
    }

}