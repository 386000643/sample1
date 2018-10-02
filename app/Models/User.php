<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;
use Auth;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
    *生成用户激活令牌
    *creating 用于监听模型被创建之前的事件
    *boot 方法会在用户模型类完成初始化之后进行加载
    */
    public static function boot(){
        parent::boot();
        static::creating(function($user){
            $user->activation_token=str_random(30);
        });
    }

     /**
     * 生成头像
     *trim除掉前后空格
     * strtolower转为小写
     *md5转码
     */
    public function gravatar($size='100'){
        $hash=md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size
        ";
    }

    public function sendPasswordResetNotification($token){
        $this->notify(new ResetPassword($token));
    }

    //将 Eloquent 关联定义为函数,一个用户多条微博
    public function statuses(){
        return $this->hasMany(Status::class);
    }

    //获取当前用户发布的所有微博
    // public function feed(){
    //     return $this->statuses()
    //                 ->orderBy('created_at','desc');
    // }

    //获取自己以及自己关注用户的所有微博
    public function feed(){
        //获取所有关注用户id
        $user_ids=Auth::user()->followings->pluck('id')->toArray();
        //将自己的id加入该数组
        array_push($user_ids, Auth::user()->id);
        //取出所有微博
        return Status::whereIn('user_id',$user_ids)->with('user')->orderBy('created_at','desc');
    }

    //获取粉丝列表
    public function followers(){
        return $this->belongsToMany(User::class,'followers','user_id','follower_id');
    }

    //获得关注人列表
    public function followings(){
        return $this->belongsToMany(User::class,'followers','follower_id','user_id');
    }

    //关注用户
    public function follow($user_ids){
        if (!is_array($user_ids)) {
            $user_ids=compact('user_ids');
        }
        $this->followings()->sync($user_ids,false);
    }


    //取消关注
    public function unfollow($user_ids){
        if (!is_array($user_ids)) {
            $usr_ids=compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    //判断用户a是否有关注b,即查看b是否在a的关注人列表上
    public function isFollowing($user_id){
        return $this->followings->contains($user_id);
    }
}
