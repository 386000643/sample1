<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //制造数据
        $users=factory(User::class)->times(50)->make();
        //插入user表中
        User::insert($users->makeVisible(['password','remember_token'])->toArray());
        //对第一个用户进行更新
        $user=User::find(1);
        $user->is_admin=true;
        $user->activated=true;
        $user->name='Aufree';
        $user->email='aufree@yousails.com';
        $user->password=bcrypt('password');
        $user->save();
    }
}
