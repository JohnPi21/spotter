<?php

namespace Database\Seeders;

use App\Enums\Permissions\UserEnum;
use App\Enums\RolesEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        foreach (UserEnum::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission->value]);
        }

        // Create roles and assign permissions
        $rolesWithPermissions = [
            RolesEnum::ADMIN->value => UserEnum::cases(), // full access
            RolesEnum::USER->value => UserEnum::cases(),
            RolesEnum::GUEST->value => [],
        ];

        foreach ($rolesWithPermissions as $role => $permissions) {
            $roleModel = Role::firstOrCreate(['name' => $role]);
            $roleModel->syncPermissions(array_map(fn ($p) => $p->value, $permissions));
        }
    }
}
