<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/index','UserController@login');
//测试分布式
Route::post('text','UserController@text');
//http客户端请求加密
Route::get ('curl6','UserController@encrypt');
//非对称加密验证签名
Route::get ('curl7','UserController@encrypt1');
//非对称加密数据
Route::get ('curl8','UserController@encrypt2');
//测试对称加密和签名发到后端
Route::get('curl9','UserController@encrypt3');

Route::post('curl10','UserController@encrypt4');

//手机支付
Route::get('pay1/{id}','PayController@pay');
//Route::get('/pay/alipay/pay/{id}', 'Pay\AlipayController@pay');       //去支付
//Route::post('/pay/alipay/notify', 'Pay\AlipayController@notify');       //支付宝异步通知