<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{
    //过滤机制
    public function __construct(){
        $this->middleware('auth',[
            'except'=>['create','show','store','index','confirmEmail']
        ]);

        $this->middleware('guest',[
            'only'=>['create']
        ]);
    }

    public function index(){
        $users=User::paginate(10);
        return view('users.index',compact('users'));
    }
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
        //注册成功后自动登录
        //Auth::login($user);
        $this->sendEmailConfirmationTo($user);
        session()->flash('success','验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
    }

    //发送用户激活邮件
    public function sendEmailConfirmationTo($user){
        $view='emails.confirm';
        $data=compact('user');
        // $from='aufree@yousails.com';
        // $name='Aufree';
        $to=$user->email;
        $subject="感谢注册sample1应用，请确认你的邮箱";
        Mail::send($view,$data,function($message) use($to,$subject){
            $message->to($to)->subject($subject);
        });
    }

    //激活邮件
    public function confirmEmail($token){
        $user=User::where('activation_token',$token)->firstOrFail();
        $user->activated=true;
        $user->activation_token=null;
        $user->save();

        Auth::login($user);
        session()->flash('success','激活成功');
        return redirect()->route('users.show',[$user]);
    }

    //编辑页面
    public function edit(User $user){
        $this->authorize('update',$user);
        return view('users.edit',compact('user'));
    }

    //更新数据
    public function update(User $user,Request $request){
        $this->validate($request,[
            'name'=>'required|max:50',
            'password'=>'nullable|min:6|confirmed',
        ]);
        $this->authorize('update',$user);
        $data=[];
        $data['name']=$request->name;
        if ($request->password) {
            $data['password']=bcrypt($request->password);

        }
        $user->update($data);
        session()->flash('success','个人资料更新成功');
        return redirect()->route('users.show',$user->id);

    }

    //删除用户
    public function destroy(User $user){
        $this->authorize('destroy',$user);
        $user->delete();
        session()->flash('success','成功删除用户');
        return back();
    }
}
