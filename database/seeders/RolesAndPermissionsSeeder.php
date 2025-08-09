<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Enums\RolesEnum;
use App\Enums\PermissionsEnum;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        foreach (PermissionsEnum::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission->value]);
        }

        // Create roles and assign permissions
        $rolesWithPermissions = [
            RolesEnum::ADMIN->value => PermissionsEnum::cases(), // full access
            RolesEnum::USER->value  => PermissionsEnum::cases(),
            RolesEnum::GUEST->value => []
        ];

        foreach ($rolesWithPermissions as $role => $permissions) {
            $roleModel = Role::firstOrCreate(['name' => $role]);
            $roleModel->syncPermissions(array_map(fn($p) => $p->value, $permissions));
        }
    }
}
