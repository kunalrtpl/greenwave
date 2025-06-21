<?php

use Illuminate\Database\Seeder;
class ModuleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('modules')->delete();
        DB::select("INSERT INTO `modules` (`id`, `name`, `parent_id`, `view_route`, `edit_route`, `delete_route`, `icon`, `session_value`, `status`, `sortorder`, `shown_in_roles`, `table_name`, `created_at`, `updated_at`) VALUES
        (1, 'Employee Management', 'ROOT', '', '', '', 'fa fa-users', 'departments,designations,users', 1, 1, '0', '', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (2, 'Departments', '1', 'admin/departments', 'admin/add-edit-department/{id?}', '', '', 'departments', 1, 1, '1', 'departments', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (3, 'Designations', '1', 'admin/designations', 'admin/add-edit-designation/{id?}', '', '', 'designations', 1, 1, '1', 'designations', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (4, 'Employees', '1', 'admin/users', 'admin/add-edit-user/{id?}', '', '', 'users', 1, 1, '1', 'users', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (5, 'Dealers Management', 'ROOT', '', '', '', 'fa fa-users', 'dealers,dealerincentives', 1, 1, '0', '', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (6, 'Dealers', '5', 'admin/dealers', 'admin/add-edit-dealer/{id?}', '', '', 'dealers', 1, 1, '1', 'dealers', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (7, 'Dealer Incentives', '5', 'admin/dealer-incentives', 'admin/add-edit-dealer-incentive/{id?}', '', '', 'dealerincentives', 1, 1, '1', 'dealer_incentives', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (8, 'Customer Management', 'ROOT', '', '', '', 'fa fa-users', 'customers,customerdiscounts', 1, 1, '0', '', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (9, 'Customers', '8', 'admin/customers', 'admin/add-edit-customer/{id?}', '', '', 'customers', 1, 1, '1', 'customers', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (10, 'Customer Discounts', '8', 'admin/customer-discounts', 'admin/add-edit-customer-discount/{id?}', '', '', 'customerdiscounts', 1, 1, '1', 'customer_discounts', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (15, 'Products Management', 'ROOT', '', '', '', 'fa fa-book', 'products,rawmaterials,packingsizes', 1, 1, '0', '', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (16, 'Products', '15', 'admin/products', 'admin/add-edit-product/{id?}', '', '', 'products', 1, 1, '1', '', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (17, 'Raw Materials', '15', 'admin/raw-materials', 'admin/add-edit-raw-material/{id?}', '', '', 'rawmaterials', 1, 1, '1', 'raw_materials', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (18, 'Packing Sizes', '15', 'admin/packing-sizes', 'admin/add-edit-packing-size/{id?}', '', '', 'packingsizes', 1, 1, '1', 'packing_sizes', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (30, 'Masters', 'ROOT', '', '', '', 'fa fa-book', 'countries,states,cities,regions', 1, 1, '0', '', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (35, 'States', '30', 'admin/states', 'admin/add-edit-state/{id?}', '', '', 'states', 1, 1, '1', 'states', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (38, 'Cities', '30', 'admin/cities', 'admin/add-edit-city/{id?}', '', '', 'cities', 1, 1, '1', 'cities', '2020-07-16 05:14:51', '2020-07-16 05:14:51'),
        (40, 'Regions', '30', 'admin/regions', 'admin/add-edit-region/{id?}', '', '', 'regions', 1, 1, '1', 'regions', '2020-07-16 05:14:51', '2020-07-16 05:14:51')");
    }
}
