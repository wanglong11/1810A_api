<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\UserModel;

class UserController extends Controller
{
    //
    public function login(){
        //echo "111";
      $data=UserModel::get()->toArray();
        //dd($data);
    }
}
