<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            // Dashboards
            'Dashboard: Stock Overview',
            'Dashboard: Utilization By Client',
            'Dashboard: RMA Monitoring',
            'Dashboard: Inbound vs Return Trend',
            'Dashboard: Stock Monitoring',

            // Inbound
            'Inbound: Receiving',
            'Inbound: Staging (Testing)',
            'Inbound: Put Away',

            // Inventory
            'Inventory: List',
            'Inventory: Product Summary',
            'Inventory: Stock Statement',
            'Inventory: Product Movement',
            'Inventory: Write-off',
            'Inventory: Cycle Count',

            // Outbound
            'Outbound',

            // Invoices
            'Invoices',

            // Audit & Reporting
            'Report: Stock on Hand',
            'Report: Movement History',
            'Report: Utilization',

            // Storage
            'Storage: Zone',
            'Storage: Rak',
            'Storage: Bin',
            'Storage: Level',

            // Master Data
            'Brand',
            'Product Group',
            'Client',
            'User Management',
        ];

        foreach ($menus as $menu) {
            DB::table('menu')->updateOrInsert(['name' => $menu], ['name' => $menu]);
        }
    }
}
