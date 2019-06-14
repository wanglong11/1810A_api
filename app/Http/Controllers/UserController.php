<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\UserModel;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Client;
class UserController extends Controller
{
    /**
     * 测试环境
     */
    public function login(){
        //echo "111";
      //$data=UserModel::get()->toArray();
        //dd($data);
       //phpinfo();
        echo "<pre>";
        print_r($_SERVER);
        echo "<pre>";


    }
    /**
     * 接受客户端传过来值
     */
    public function text(){
        echo '<pre>';print_r($_POST);echo '</pre>';
          // $data=file_get_contents("php://input");//raw
          //echo $data;
    }

    /**
     * Http客户端加密
     */
    public function encrypt(){
        $data=[
            'name'=>"杜甫",
           'age'=>"18"
        ];
        $iv="d7g8908000g11111";
        //$key="wwl";
        $via=json_encode($data,JSON_UNESCAPED_UNICODE);
        //$data=$this->data_encode($via,$iv);//对称加密
        $data=$this->data_encrypt($via);
       // dd($data);
        $url="http://www.1810lumen.com/curl6";
       $client= new Client();
        $responce=$client->request('POST',$url,[
            'body'=>$data
        ]);
        //echo $responce;
        //处理响应
       $res_str=$responce->getBody();
        echo $res_str;



    }
    /**
     * 对称加密
     */
   public function data_encode($strContent,$iv){
     //  $data = '1234567887654321';//加密明文
        $method = 'AES-128-CBC';//加密方法
        $passwd = '12344321';//加密密钥
       $options = 0;//数据格式选项（可选）
//        $iv = '';//加密初始化向量（可选）
       $encode = openssl_encrypt($strContent, $method, $passwd, $options,$iv);
        //$encode=openssl_encrypt($strContent,$method ,$key,$options,$vi);
        return base64_encode($encode);
       //return $encode;
    }
    /**
     * 非对称加密验证标签
     */
    public function encrypt1(){
        $data="qwertyuiop";
        //获取私钥
        $file_pri=openssl_get_privatekey("file://".storage_path('app/private.pem'));
        //将原数据私钥加密 赋给
        openssl_sign($data,$signature,$file_pri);
        $b=base64_encode($signature);//验签的数据
        $url="http://www.1810lumen.com/curl7?url=".urlencode($b);
        //使用Guzzle传值
        $clinet = new Client();
        $response = $clinet ->request("POST",$url,[
            'body'=>$data
        ]);
        echo $response->getBody();
    }
    /**
     * 非对称加密数据
     */
    public function encrypt2(){
       $data=[
           'name'=>"杜甫",
           'age'=>"19"
       ];
        $data=json_encode($data,JSON_UNESCAPED_UNICODE);
        //获取私钥
        $pi_key = openssl_pkey_get_private("file://".storage_path('app/private.pem'));
        $crypted='';
        openssl_private_encrypt($data,$crypted ,$pi_key);
       // dd($crypted);
        // 转码，这里的$encrypted就是私钥加密的字符串
         $encrypted = base64_encode($crypted);//乱码改一下(都可以)
        //dd($encrypted);
        $url="http://www.1810lumen.com/curl8";
        //使用Guzzle传值
        $clinet = new Client();
        $response = $clinet ->request("POST",$url,[
            'body'=>$encrypted
        ]);
        echo $response->getBody();

    }
    /**
     * 对称加密并用私钥生成签名，将加密数据和签名一起发送给服务端
     */
    public function encrypt3(){
        $data="passwd";
        $iv="d7g8908000g11111";
        //$key="wwl";
        //$via=json_encode($data,JSON_UNESCAPED_UNICODE);
        //$data=$this->data_encode($via,$iv);//对称加密
        $data=$this->data_encode($data,$iv);
        //生成签名
        //获取私钥
        $file_pri=openssl_get_privatekey("file://".storage_path('app/private.pem'));
        //将原数据私钥加密 赋给
        openssl_sign($data,$signature,$file_pri);
        $b=base64_encode($signature);//验签的数据
        $url="http://www.1810lumen.com/curl9?url=".urlencode($b);
        //使用Guzzle传值
        $clinet = new Client();
        $response = $clinet ->request("POST",$url,[
            'body'=>$data
        ]);
        echo $response->getBody();



    }
    /**
     *接受服务端的数据返回数据进行解密、验签
     */
    public function encrypt4(){
        //接受服务端发生过来的数据
        //验证签名
        $re=$_GET['url'];//接过来值(服务端传过来的验签加密的数据)
        //dd($re);
        $re=base64_decode($re);//乱码转化一下
        // dd($re);
        $data=file_get_contents('php://input');//接受一下数据原始数据
        //dd($data);
        $asymm=openssl_get_publickey("file://".storage_path('app/public.pem'));
        $result = openssl_verify($data,$re,$asymm);
        echo'验签结果:'. $result.'在客户端';
        //接受数据把他解密
        $decrypt1=base64_decode(file_get_contents("php://input"));//对称加密
        // dd($decrypt1);
        $method = 'AES-128-CBC';//加密方法
        $passwd = '12344321';//加密密钥
        $iv="d7g8908000g11111";//数据格式选项（可选）
        $options = 0;//加密初始化向量（可选）
        $a=openssl_decrypt($decrypt1, $method, $passwd, 0,$iv);//对称加密
        // $a1=json_decode($a,true);
        echo '服务端穿过来的密码'.$a;
    }
}
