<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\{Role, Permission};

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Team Roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Member']);

        //Project Roles
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'User']);
        Role::create(['name' => 'Viewer']);

        //Permissions
        Permission::create(['name' => 'cancel_task']);
        Permission::create(['name' => 'updateStatus']);
        Permission::create(['name' => 'completeTask']);

        //Assign Permissions to Roles
        $managerRole = Role::where('name', 'Manager')->first();

        $managerRole->givePermissionTo('cancel_task');
        $managerRole->givePermissionTo('updateStatus');

        $userRole = Role::where('name', 'User')->first();
        $userRole->givePermissionTo('completeTask');
    }
}
