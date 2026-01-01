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
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'sanctum']);

        User::first()->assignRole(['name' => 'admin']);

        $entities = ['project', 'task'];
        $commonActions = ['create', 'update', 'delete'];

        foreach ($entities as $entity) {
            foreach ($commonActions as $action) {
                Permission::firstOrCreate([
                    'name' => $action . '-' . $entity,
                    'guard_name' => 'sanctum',
                ]);
            }
        }
    }
}
