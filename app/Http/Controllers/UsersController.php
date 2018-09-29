<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;

class UsersController extends Controller
{
    //用户注册界面
    public function create(){
        return view('users.create');
    }

    //显示个人信息
    public function show(User $user){
        return view('users.show',compact('user'));
    }

    //对提交信息进行验证并保存
    public function store(Request $request){
        $this->validate($request,[
            'name'=>'required|max:50',
            'email'=>'required|unique:users|email|max:255',
            'password'=>'required|min:6|confirmed'
        ]);
        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>bcrypt($request->password),
        ]);
        session()->flash('success','注册成功');
        return redirect()->route('users.show',[$user]);
    }
}
