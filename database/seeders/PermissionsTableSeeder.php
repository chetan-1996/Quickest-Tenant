<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('permissions')->insert([
            ['id' => '9','name' => 'Unit list','slug' => 'unit-list','company_id' => '0'],
            ['id' => '10','name' => 'Add Unit','slug' => 'add-unit','company_id' => '0'],
            ['id' => '11','name' => 'Edit Unit','slug' => 'edit-unit','company_id' => '0'],
            ['id' => '12','name' => 'Remove Unit','slug' => 'remove-unit','company_id' => '0'],
            ['id' => '13','name' => 'Item list','slug' => 'item-list','company_id' => '0'],
            ['id' => '14','name' => 'Add Item','slug' => 'add-item','company_id' => '0'],
            ['id' => '15','name' => 'Edit Item','slug' => 'edit-item','company_id' => '0'],
            ['id' => '16','name' => 'Remove Item','slug' => 'remove-item','company_id' => '0'],
            ['id' => '17','name' => 'Product list','slug' => 'product-list','company_id' => '0'],
            ['id' => '18','name' => 'Add Product','slug' => 'add-product','company_id' => '0'],
            ['id' => '19','name' => 'Edit Product','slug' => 'edit-product','company_id' => '0'],
            ['id' => '20','name' => 'Remove Product','slug' => 'remove-product','company_id' => '0'],
            ['id' => '21','name' => 'Testimonial list','slug' => 'testimonial-list','company_id' => '0'],
            ['id' => '22','name' => 'Add Testimonial','slug' => 'add-testimonial','company_id' => '0'],
            ['id' => '23','name' => 'Edit Testimonial','slug' => 'edit-testimonial','company_id' => '0'],
            ['id' => '24','name' => 'Remove Testimonial','slug' => 'remove-testimonial','company_id' => '0'],
            ['id' => '25','name' => 'Country list','slug' => 'country-list','company_id' => '0'],
            ['id' => '26','name' => 'Add Country','slug' => 'add-country','company_id' => '0'],
            ['id' => '27','name' => 'Edit Country','slug' => 'edit-country','company_id' => '0'],
            ['id' => '28','name' => 'Remove Country','slug' => 'remove-country','company_id' => '0'],
            ['id' => '29','name' => 'State list','slug' => 'state-list','company_id' => '0'],
            ['id' => '30','name' => 'Add State','slug' => 'add-state','company_id' => '0'],
            ['id' => '31','name' => 'Edit State','slug' => 'edit-state','company_id' => '0'],
            ['id' => '32','name' => 'Remove State','slug' => 'remove-state','company_id' => '0'],
            ['id' => '33','name' => 'City list','slug' => 'city-list','company_id' => '0'],
            ['id' => '34','name' => 'Add City','slug' => 'add-city','company_id' => '0'],
            ['id' => '35','name' => 'Edit City','slug' => 'edit-city','company_id' => '0'],
            ['id' => '36','name' => 'Remove City','slug' => 'remove-city','company_id' => '0'],
            ['id' => '37','name' => 'Customer list','slug' => 'customer-list','company_id' => '0'],
            ['id' => '38','name' => 'Add Customer','slug' => 'add-customer','company_id' => '0'],
            ['id' => '39','name' => 'Edit Customer','slug' => 'edit-customer','company_id' => '0'],
            ['id' => '40','name' => 'Remove Customer','slug' => 'remove-customer','company_id' => '0'],
            ['id' => '41','name' => 'Estimate list','slug' => 'estimate-list','company_id' => '0'],
            ['id' => '42','name' => 'Add Estimate','slug' => 'add-estimate','company_id' => '0'],
            ['id' => '43','name' => 'Edit Estimate','slug' => 'edit-estimate','company_id' => '0'],
            ['id' => '44','name' => 'Remove Estimate','slug' => 'remove-estimate','company_id' => '0'],
            ['id' => '45','name' => 'User list','slug' => 'user-list','company_id' => '0'],
            ['id' => '46','name' => 'Add User','slug' => 'add-user','company_id' => '0'],
            ['id' => '47','name' => 'Edit User','slug' => 'edit-user','company_id' => '0'],
            ['id' => '48','name' => 'Remove User','slug' => 'remove-user','company_id' => '0'],
            ['id' => '49','name' => 'Role list','slug' => 'role-list','company_id' => '0'],
            ['id' => '50','name' => 'Add Role','slug' => 'add-role','company_id' => '0'],
            ['id' => '51','name' => 'Edit Role','slug' => 'edit-role','company_id' => '0'],
            ['id' => '52','name' => 'Remove Role','slug' => 'remove-role','company_id' => '0'],
            ['id' => '53','name' => 'Follow-up list','slug' => 'follow-up-list','company_id' => '0'],
            ['id' => '54','name' => 'Tax List','slug' => 'tax-list','company_id' => '0'],
            ['id' => '55','name' => 'Add Tax','slug' => 'add-tax','company_id' => '0'],
            ['id' => '56','name' => 'Edit Tax','slug' => 'edit-tax','company_id' => '0'],
            ['id' => '57','name' => 'Remove Tax','slug' => 'remove-tax','company_id' => '0'],
            ['id' => '58','name' => 'Category list','slug' => 'category-list','company_id' => '0'],
            ['id' => '59','name' => 'Add Category','slug' => 'add-category','company_id' => '0'],
            ['id' => '60','name' => 'Edit Category','slug' => 'edit-category','company_id' => '0'],
            ['id' => '61','name' => 'Remove Category','slug' => 'remove-category','company_id' => '0'],
            ['id' => '62','name' => 'Lead Origin list','slug' => 'lead-origin-list','company_id' => '0'],
            ['id' => '63','name' => 'Add Lead Origin','slug' => 'add-lead-origin','company_id' => '0'],
            ['id' => '64','name' => 'Edit Lead Origin','slug' => 'edit-lead-origin','company_id' => '0'],
            ['id' => '65','name' => 'Remove Lead Origin','slug' => 'remove-lead-origin','company_id' => '0'],
            ['id' => '70','name' => 'Access all lead and assign to anyone in team','slug' => 'access-all-lead-and-assign-to-anyone-in-team','company_id' => '0'],
            ['id' => '71','name' => 'Access self leads only and assign my leads to anyone in team','slug' => 'access-self-leads-only-and-assign-my-leads-to-anyone-in-team','company_id' => '0'],
            ['id' => '72','name' => 'Access self leads only and cant assign my leads to anyone in team','slug' => 'access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team','company_id' => '0'],
            ['id' => '73','name' => 'Give access to attend unassigned leads','slug' => 'give-access-to-attend-unassigned-leads','company_id' => '0'],
            ['id' => '74','name' => 'Give access to delete other users','slug' => 'give-access-to-delete-other-users','company_id' => '0'],
            ['id' => '75','name' => 'Template setting','slug' => 'template-setting','company_id' => '0'],
            ['id' => '76','name' => 'Access to add and edit files and message template','slug' => 'access-to-add-and-edit-files-and-message-template','company_id' => '0'],
            ['id' => '77','name' => 'Access to Add and edit item and product photos','slug' => 'access-to-add-and-edit-item-and-product-photos','company_id' => '0'],
            ['id' => '78','name' => 'Give Access to delete leads','slug' => 'give-access-to-delete-leads','company_id' => '0'],
        ]);
    }
}
