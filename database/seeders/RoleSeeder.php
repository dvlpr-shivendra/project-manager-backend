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
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);

        User::first()->assignRole('admin');

        $entities = ['project', 'task'];
        $commonActions = ['create', 'update', 'delete'];

        foreach ($entities as $entity) {
            foreach ($commonActions as $action) {
                Permission::create(['name' => $action . '-' . $entity]);
            }
        }
    }
}
