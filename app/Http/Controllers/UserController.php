<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\UserModel;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    //
    public function login(){
        //echo "111";
      //$data=UserModel::get()->toArray();
        //dd($data);
       //phpinfo();
        echo "<pre>";
        print_r($_SERVER);
        echo "<pre>";


    }
}
