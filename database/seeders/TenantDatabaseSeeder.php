<?php

namespace Database\Seeders;

use App\Models\Unit;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Stancl\Tenancy\Facades\Tenancy;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // User::factory(10)->create();

        /*User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/
        $this->call([
            CountryTableSeeder::class,
            StateTableSeeder::class,
            CityTableSeeder::class,
            PermissionsTableSeeder::class,
            EstimateAutoNumberTableSeeder::class,
            UnitsTableSeeder::class,
            LostReasonsTableSeeder::class,
            DashboardSettingsTableSeeder::class,
            LeadStageTableSeeder::class,
            CustomersTableSeeder::class,
            TaxsTableSeeder::class,
            CustomerCategoriesTableSeeder::class,
            CustomerLeadsTableSeeder::class,
            // ProductsTableSeeder::class,
            // ProposalTemplatesTableSeeder::class,
            LeadGroupsTableSeeder::class,
            ContentMessagesTableSeeder::class,
            TermConditionsTableSeeder::class,
            // TestimonialsTableSeeder::class,
            // ItemsTableSeeder::class,
            ContentFilesTableSeeder::class,
            UsersPermissionsTableSeeder::class,
            PlansTableSeeder::class,
            PlansHistoryTableSeeder::class,
        ]);
    }
}
