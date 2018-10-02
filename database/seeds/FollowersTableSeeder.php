<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users=User::all();
        $user=$users->first();
        $user_id=$user->id;

        //去除第一个用户
        $followers=$users->slice(1);
        //获取其id
        $follower_ids=$followers->pluck('id')->toArray();

        //关注除1以外的所有用户
        $user->follow($follower_ids);

        //除1以外的其他人关注1
        foreach ($followers as  $follower) {
            $follower->follow($user_id);
        }
    }
}
