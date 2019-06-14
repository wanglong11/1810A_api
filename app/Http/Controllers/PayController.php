<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use App\Model\OrderModel;
class PayController extends Controller
{
//    public $app_id;
//    public $gate_way;
//    public $notify_url;
//    public $return_url;
//    public $rsaPrivateKeyFilePath;
//    public $aliPubKey;
//
//    public function __construct()
//    {
//        $this->app_id = env('ALIPAY_APPID');//支付宝分配给开发者的应用ID
//        $this->gate_way = 'https://openapi.alipaydev.com/gateway.do';//支付网关
//        $this->notify_url = env('ALIPAY_NOTIFY_URL');//异步通知地址
//        $this->return_url = env('ALIPAY_RETURN_URL');//回调地址
//        $this->rsaPrivateKeyFilePath = storage_path('app/private.pem');    //应用私钥
//        $this->aliPubKey = storage_path('app/public.pem'); //支付宝公钥
//    }

    public function test()
    {
        echo $this->aliPubKey;
        echo '</br>';
        echo $this->rsaPrivateKeyFilePath;
        echo '</br>';
    }

    /**
     * 订单支付
     * @param $oid
     */
    public function pay1($order_id)
    {
        //验证订单状态 是否已支付 是否是有效订单
        $order_info = OrderModel::where(['order_id' => $order_id])->first()->toArray();
    }



    public function pay($order_id)
    {
//        echo "<pre>";print_r($_SERVER);echo "<pre>";die;
//        $str2 = json_encode($_SERVER['HTTP_USER_AGENT']);
//        $str = 'Windows';
//        if(strpos($str2,$str) != false){
//            //扫码支付
//            $method = 'alipay.trade.page.pay';
//            $prouct_code = 'FAST_INSTANT_TRADE_PAY';
//            $url = 'https://openapi.alipaydev.com/gateway.do';
//        }else{

//h5支付

//        }

        $order_info = OrderModel::where(['order_id' => $order_id])->first();
//业务参数
        $method = 'alipay.trade.wap.pay';
        $prouct_code = 'QUICK_WAP_WAY';
        $url = 'https://openapi.alipaydev.com/gateway.do';
        $bizcont = [
            'subject' => '月七',//交易标题/订单标题/订单关键
            'out_trade_no'=>$order_info->order_no , //订单号
            'total_amount'      => $order_info->order_amount / 100, //支付金额
            'product_code'      => $prouct_code //固定值
        ];
//公共参数
        $data = [
            'app_id'   => '2016092500595129',
            'method'   => $method,
            'format'   => 'JSON',
            'charset'   => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'   => '1.0',
           // 'notify_url'   => 'http://www.1810a.com',       //异步通知地址
            'return_url'   => 'http://www.baidu.com',      // 同步通知地址
            'biz_content'   => json_encode($bizcont),
        ];
//拼接参数
        ksort($data);//根据键以升序对关联数组进行排序
       // dd($data);
        $i = "";
        foreach ($data as $k=>$v)
        {
            $i.=$k.'='.$v.'&';
        }
        $trim  = rtrim($i,'&');
        //var_dump($trim);die;
//生成签名 最后拼接为url 格式
       // $rsaPrivateKeyFilePath = openssl_get_privatekey('file://'.storage_path('app/private.pem')); //密钥
        $rsaPrivateKeyFilePath=openssl_get_privatekey("file://".storage_path('app/private.pem'));
//          var_dump($rsaPrivateKeyFilePath);
            //$a = openssl_error_string();
            //var_dump($a);die;
//生成签名
        openssl_sign($trim,$sign,$rsaPrivateKeyFilePath,OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        $data['sign']=$sign;
        //var_dump($data);die;
//拼接url
        $a='?';
        foreach($data as $key=>$val){
            $a.=$key.'='.urlencode($val).'&'; //urlencode 将字符串以url形式编码
        }
        $trim2 = rtrim($a,'&');
      // var_dump($trim2);die;
        $url2 = $url.$trim2;
//        var_dump($url2);die;
        header('refresh:2;url='.$url2);}
}







