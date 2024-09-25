<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = DB::table('units')->where([["company_id", "=", 1], ["name", "=", "Kw"]])->orderBy('id', 'ASC')->select("id")->first();
        
        $units1 = DB::table('units')->where([["company_id", "=", 1], ["name", "=", "Nos"]])->orderBy('id', 'ASC')->select("id")->first();

        $userDatas = DB::table('users')->where('id', '=', 1)->select(['company_category', 'name', 'email'])->first();
		if ($userDatas->company_category == 1) {
            DB::table('items')->insert([
                ['name' => 'Commercial Solar Power Plant', 'description' => trim('On Grid Solar Power Plant ___ KW
                        Solar Panel Brand
                        Solar Inverter Brand

                    Please find Detailed BOM'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 13.8, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Taxable', 'sale_price' => 49000.00, 'sales_flag' => 1, 'status' => 0, 'purchase_flag' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                ['name' => 'Residential Solar Rooftop solar Plant', 'description' => trim('1.Solar Power Plant Capacity : 3.24  KW
                        2.Solar Panels Brand: Adani Mono 540 W (6 Qty)
                        3.Solar Inverter Brand: KSolare 3.2 KW

                    Please find detailed BOM & Scope on next page.
                    78,000 Subsidy will be credited to your bank account direct.'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 13.8, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Taxable', 'sale_price' => 49000.00, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                ['name' => 'Solar Meter Charges', 'description' => trim('This charge we need to pay to Government (Discom)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units1->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 3250.00, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                ['name' => 'Solar Structure Charge', 'description' => '', 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 18, 'cost_price' => 0, 'tax_preference' => 'Taxable', 'sale_price' => 8500.00, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                ['name' => 'Subsidy Amount', 'description' => '', 'item_type' => 'Goods', 'unit_id' => $units1->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 58000.00, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => 1, 'company_id' => 1
                ]
            ]);
        }

        if ($userDatas->company_category != 1) {
            $path_ones = env('APP_URL') . "sample/image.png";
            $filename_ones = date('YmdHis') . "10654" . ".png";
            Storage::disk('s3')->put("public/1/items/" . $filename_ones, file_get_contents($path_ones),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/items/" . $filename_ones);

            DB::table('items')->insert([
                ['name' => 'Sample item 1', 'description' => trim('- Item details
                    - You can write detailed description the item
                    - You can add or import all your item from the web portal
                    - Pricing will be fetched automatically
                    - You can edit the price and descriptions while creating the item
                    - You can add notes below'), 'item_type' => 'Goods', 'inter_state' => 18, 'intra_state' => 18, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 999, 'sales_flag' => 1, 'status' => 0, 'purchase_flag' => 0, 'user_id' => 1, 'company_id' => 1, 'image_icon' => "public/1/items/" . $filename_ones]
            ]);
        }
    }
}
