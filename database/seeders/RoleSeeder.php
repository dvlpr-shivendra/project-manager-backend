<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'sanctum']);

        if (User::count() > 0) {
            User::first()->assignRole($adminRole);
        }

        $entities = ['project', 'task'];
        $commonActions = ['create', 'update', 'delete'];

        $allPermissionNames = [];
        foreach ($entities as $entity) {
            foreach ($commonActions as $action) {
                $permissionName = $action . '-' . $entity;
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'sanctum',
                ]);
                $allPermissionNames[] = $permissionName;
            }
        }

        $adminRole->syncPermissions($allPermissionNames);
        $userRole->syncPermissions(['create-project', 'update-project', 'create-task', 'update-task']);
    }
}
