<?php
use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class CreateAdminUserSeeder extends Seeder
{
/**
* Run the database seeds.
*
* @return void
*/
public function run()
{
    

        DB::table('users')->delete();
         $user = User::create([
        'name' => 'hatem', 
        'email' => 'hatemfaysil16@gmail.com',
        'password' => bcrypt('123456789'),
        'roles_name' => ["owner"],
        'Status' => 'مفعل',
        ]);
  
        $role = Role::create(['name' => 'owner']);
   
        $permissions = Permission::pluck('id','id')->all();
  
        $role->syncPermissions($permissions);
   
        $user->assignRole([$role->id]);


}
}