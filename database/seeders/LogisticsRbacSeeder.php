<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LogisticsRbacSeeder extends Seeder
{
    public function run(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $adminPerms = [
            'manage_logistics_staff',
        ];

        foreach ($adminPerms as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'admin',
            ]);
        }

        $logisticsPerms = [
            'logistics.orders.view_all',
            'logistics.orders.create',
            'logistics.orders.edit',
            'logistics.orders.assign',
            'logistics.orders.update_status',
            'logistics.shipments.schedule',
            'logistics.shipments.assign_routes',
            'logistics.shipments.monitor',
            'logistics.agents.onboard',
            'logistics.agents.manage_assignments',
            'logistics.agents.view_metrics',
            'logistics.agents.view',
            'logistics.centers.manage',
            'logistics.inventory.view',
            'logistics.inventory.manage',
            'logistics.analytics.view',
        ];

        foreach ($logisticsPerms as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'logistics_staff',
            ]);
        }

        $manager = Role::firstOrCreate([
            'name' => 'logistics_manager',
            'guard_name' => 'logistics_staff',
        ]);
        $manager->syncPermissions($logisticsPerms);

        $officer = Role::firstOrCreate([
            'name' => 'logistics_officer',
            'guard_name' => 'logistics_staff',
        ]);
        $officer->syncPermissions([
            'logistics.orders.update_status',
            'logistics.shipments.monitor',
            'logistics.agents.view',
            'logistics.centers.manage',
            'logistics.inventory.view',
        ]);
    }
}
