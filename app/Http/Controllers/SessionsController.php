<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use APP\Models\Requests;
use App\Models\User;
use Auth;

class SessionsController extends Controller
{
    public function __construct(){
        $this->middleware('guest',[
            'only'=>['create']
        ]);
    }

    public function create(){
        return view('sessions.create');
    }

    public function store(Request $request){
        $credentials=$this->validate($request,[
            'email'=>'required|email|max:255',
            'password'=>'required',
        ]);
        if (Auth::attempt($credentials,$request->has('remember'))) {
            if (Auth::user()->activated) {
                session()->flash('success','欢迎回来');
                return redirect()->intended(route('users.show',[Auth::user()]));
            }else{
                Auth::logout();
                session()->flash('warning','你的账户未激活，请查收邮箱');
                return redirect('/');
            }

        }else{
            session()->flash('danger','邮箱和密码不一致');
            return redirect()->back();
        }
    }

    public function destroy(){
        Auth::logout();
        session()->flash('success','退出成功');
        return redirect('login');

    }
}
