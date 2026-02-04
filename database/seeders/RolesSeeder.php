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
        Role::create(['name' => 'Owner']);
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Member']);

        //Project Roles
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'User']);
        Role::create(['name' => 'Viewer']);

        //Permissions
        Permission::create(['name' => 'cancelTask']);
        Permission::create(['name' => 'updateStatus']);
        Permission::create(['name' => 'completeTask']);
        Permission::create(['name' => 'updateTask']);

        Permission::create(['name' => 'updateObjective']);
        Permission::create(['name' => 'updateProject']);

        //Assign Permissions to Roles
        $managerRole = Role::where('name', 'Manager')->first();

        $managerRole->givePermissionTo('cancelTask');
        $managerRole->givePermissionTo('updateStatus');
        $managerRole->givePermissionTo('updateProject');
        $managerRole->givePermissionTo('updateObjective');

        $userRole = Role::where('name', 'User')->first();
        $userRole->givePermissionTo('completeTask');
        $userRole->givePermissionTo('updateTask');
        $userRole->givePermissionTo('updateObjective');
    }
}
