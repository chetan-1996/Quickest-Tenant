<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
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
            $panel8T_1_path_one = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-1.jpg";
            $panel8T_1_one = date('YmdHis') . "1" . ".jpg";

            // Copy the original image to S3
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel8T_1_one, file_get_contents($panel8T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel8T_1_one);
            $image = Image::make($panel8T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel8T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel8T_1_one);

            $panel8T_1_path_two = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-2.jpg";
            $panel8T_1_two = date('YmdHis') . "2" . ".jpg";
               
            // Copy the original image to S3
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel8T_1_two, file_get_contents($panel8T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel8T_1_two);
            $image = Image::make($panel8T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel8T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel8T_1_two);

            $panel8T_1_path_three = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-3.jpg";
            $panel8T_1_three = date('YmdHis') . "3" . ".jpg";
                
            // Copy the original image to S3
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel8T_1_three, file_get_contents($panel8T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel8T_1_three);
            $image = Image::make($panel8T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel8T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel8T_1_three);

            $panel8T_2_path_one = env('APP_URL') . "sample/product/8-PANEL/T-2/10002.jpg";
            $panel8T_2_one = date('YmdHis') . "4" . ".jpg";
            
            // Copy the original image to S3
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel8T_2_one, file_get_contents($panel8T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel8T_2_one);
            $image = Image::make($panel8T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel8T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel8T_2_one);

            $panel8T_2_path_two = env('APP_URL') . "sample/product/8-PANEL/T-2/20001.jpg";
            $panel8T_2_two = date('YmdHis') . "5" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel8T_2_two, file_get_contents($panel8T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel8T_2_two);
            $image = Image::make($panel8T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel8T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel8T_2_two);

            $panel8T_2_path_three = env('APP_URL') . "sample/product/8-PANEL/T-2/20003.jpg";
            $panel8T_2_three = date('YmdHis') . "6" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel8T_2_three, file_get_contents($panel8T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel8T_2_three);
            $image = Image::make($panel8T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel8T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel8T_2_three);

            $panel8T_3_path_one = env('APP_URL') . "sample/product/8-PANEL/T-3/10002.jpg";
            $panel8T_3_one = date('YmdHis') . "7" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel8T_3_one, file_get_contents($panel8T_3_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel8T_3_one);
            $image = Image::make($panel8T_3_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel8T_3_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel8T_3_one);

            $panel8T_3_path_two = env('APP_URL') . "sample/product/8-PANEL/T-3/20001.jpg";
            $panel8T_3_two = date('YmdHis') . "8" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel8T_3_two, file_get_contents($panel8T_3_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel8T_3_two);
            $image = Image::make($panel8T_3_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel8T_3_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel8T_3_two);

            $panel8T_3_path_three = env('APP_URL') . "sample/product/8-PANEL/T-3/20003.jpg";
            $panel8T_3_three = date('YmdHis') . "9" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel8T_3_three, file_get_contents($panel8T_3_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel8T_3_three);
            $image = Image::make($panel8T_3_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel8T_3_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel8T_3_three);

            // 9-PANEL
            $panel9T_1_path_one = env('APP_URL') . "sample/product/9-PANEL/T-1/10002.jpg";
            $panel9T_1_one = date('YmdHis') . "10" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel9T_1_one, file_get_contents($panel9T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel9T_1_one);
            $image = Image::make($panel9T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel9T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel9T_1_one);

            $panel9T_1_path_two = env('APP_URL') . "sample/product/9-PANEL/T-1/20001.jpg";
            $panel9T_1_two = date('YmdHis') . "11" . ".jpg";
                
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel9T_1_two, file_get_contents($panel9T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel9T_1_two);
            $image = Image::make($panel9T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel9T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel9T_1_two);

            $panel9T_1_path_three = env('APP_URL') . "sample/product/9-PANEL/T-1/20003.jpg";
            $panel9T_1_three = date('YmdHis') . "12" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel9T_1_three, file_get_contents($panel9T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel9T_1_three);
            $image = Image::make($panel9T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel9T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel9T_1_three);

            $panel9T_2_path_one = env('APP_URL') . "sample/product/9-PANEL/T-2/10002.jpg";
            $panel9T_2_one = date('YmdHis') . "13" . ".jpg";
                
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel9T_2_one, file_get_contents($panel9T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel9T_2_one);
            $image = Image::make($panel9T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel9T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel9T_2_one);

            $panel9T_2_path_two = env('APP_URL') . "sample/product/9-PANEL/T-2/20001.jpg";
            $panel9T_2_two = date('YmdHis') . "14" . ".jpg";
                
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel9T_2_two, file_get_contents($panel9T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel9T_2_two);
            $image = Image::make($panel9T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel9T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel9T_2_two);

            $panel9T_2_path_three = env('APP_URL') . "sample/product/9-PANEL/T-2/20003.jpg";
            $panel9T_2_three = date('YmdHis') . "15" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel9T_2_three, file_get_contents($panel9T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel9T_2_three);
            $image = Image::make($panel9T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel9T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel9T_2_three);

            $panel9T_3_path_one = env('APP_URL') . "sample/product/9-PANEL/T-3/10002.jpg";
            $panel9T_3_one = date('YmdHis') . "16" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel9T_3_one, file_get_contents($panel9T_3_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel9T_3_one);
            $image = Image::make($panel9T_3_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel9T_3_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel9T_3_one);

            $panel9T_3_path_two = env('APP_URL') . "sample/product/9-PANEL/T-3/20001.jpg";
            $panel9T_3_two = date('YmdHis') . "17" . ".jpg";
                
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel9T_3_two, file_get_contents($panel9T_3_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel9T_3_two);
            $image = Image::make($panel9T_3_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel9T_3_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel9T_3_two);

            $panel9T_3_path_three = env('APP_URL') . "sample/product/9-PANEL/T-3/20003.jpg";
            $panel9T_3_three = date('YmdHis') . "18" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel9T_3_three, file_get_contents($panel9T_3_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel9T_3_three);
            $image = Image::make($panel9T_3_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel9T_3_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel9T_3_three);

            // 10-PANEL
            $panel10T_1_path_one = env('APP_URL') . "sample/product/10-PANEL/T-1/10002.jpg";
            $panel10T_1_one = date('YmdHis') . "19" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel10T_1_one, file_get_contents($panel10T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel10T_1_one);
            $image = Image::make($panel10T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel10T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel10T_1_one);

            $panel10T_1_path_two = env('APP_URL') . "sample/product/10-PANEL/T-1/20001.jpg";
            $panel10T_1_two = date('YmdHis') . "20" . ".jpg";
                
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel10T_1_two, file_get_contents($panel10T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel10T_1_two);
            $image = Image::make($panel10T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel10T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel10T_1_two);

            $panel10T_1_path_three = env('APP_URL') . "sample/product/10-PANEL/T-1/20003.jpg";
            $panel10T_1_three = date('YmdHis') . "21" . ".jpg";
                
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel10T_1_three, file_get_contents($panel10T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel10T_1_three);
            $image = Image::make($panel10T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel10T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel10T_1_three);

            $panel10T_2_path_one = env('APP_URL') . "sample/product/10-PANEL/T-2/10002.jpg";
            $panel10T_2_one = date('YmdHis') . "22" . ".jpg";
                
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel10T_2_one, file_get_contents($panel10T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel10T_2_one);
            $image = Image::make($panel10T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel10T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel10T_2_one);

            $panel10T_2_path_two = env('APP_URL') . "sample/product/10-PANEL/T-2/20001.jpg";
            $panel10T_2_two = date('YmdHis') . "23" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel10T_2_two, file_get_contents($panel10T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel10T_2_two);
            $image = Image::make($panel10T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel10T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel10T_2_two);

            $panel10T_2_path_three = env('APP_URL') . "sample/product/10-PANEL/T-2/20003.jpg";
            $panel10T_2_three = date('YmdHis') . "24" . ".jpg";
                
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel10T_2_three, file_get_contents($panel10T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel10T_2_three);
            $image = Image::make($panel10T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel10T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel10T_2_three);

            $panel10T_3_path_one = env('APP_URL') . "sample/product/10-PANEL/T-3/10002.jpg";
            $panel10T_3_one = date('YmdHis') . "25" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel10T_3_one, file_get_contents($panel10T_3_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel10T_3_one);
            $image = Image::make($panel10T_3_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel10T_3_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel10T_3_one);

            $panel10T_3_path_two = env('APP_URL') . "sample/product/10-PANEL/T-3/20001.jpg";
            $panel10T_3_two = date('YmdHis') . "26" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel10T_3_two, file_get_contents($panel10T_3_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel10T_3_two);
            $image = Image::make($panel10T_3_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel10T_3_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel10T_3_two);

            $panel10T_3_path_three = env('APP_URL') . "sample/product/10-PANEL/T-3/20003.jpg";
            $panel10T_3_three = date('YmdHis') . "27" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel10T_3_three, file_get_contents($panel10T_3_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel10T_3_three);
            $image = Image::make($panel10T_3_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel10T_3_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel10T_3_three);

            // 11-PANEL
            $panel11T_1_path_one = env('APP_URL') . "sample/product/11-PANEL/T-1/10002.jpg";
            $panel11T_1_one = date('YmdHis') . "28" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel11T_1_one, file_get_contents($panel11T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel11T_1_one);
            $image = Image::make($panel11T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel11T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel11T_1_one);

            $panel11T_1_path_two = env('APP_URL') . "sample/product/11-PANEL/T-1/20001.jpg";
            $panel11T_1_two = date('YmdHis') . "29" . ".jpg";
                
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel11T_1_two, file_get_contents($panel11T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel11T_1_two);
            $image = Image::make($panel11T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel11T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel11T_1_two);

            $panel11T_1_path_three = env('APP_URL') . "sample/product/11-PANEL/T-1/20003.jpg";
            $panel11T_1_three = date('YmdHis') . "30" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel11T_1_three, file_get_contents($panel11T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel11T_1_three);
            $image = Image::make($panel11T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel11T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel11T_1_three);

            $panel11T_2_path_one = env('APP_URL') . "sample/product/11-PANEL/T-2/10002.jpg";
            $panel11T_2_one = date('YmdHis') . "31" . ".jpg";
                
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel11T_2_one, file_get_contents($panel11T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel11T_2_one);
            $image = Image::make($panel11T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel11T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel11T_2_one);

            $panel11T_2_path_two = env('APP_URL') . "sample/product/11-PANEL/T-2/20001.jpg";
            $panel11T_2_two = date('YmdHis') . "32" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel11T_2_two, file_get_contents($panel11T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel11T_2_two);
            $image = Image::make($panel11T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel11T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel11T_2_two);

            $panel11T_2_path_three = env('APP_URL') . "sample/product/11-PANEL/T-2/20003.jpg";
            $panel11T_2_three = date('YmdHis') . "33" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel11T_2_three, file_get_contents($panel11T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel11T_2_three);
            $image = Image::make($panel11T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel11T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel11T_2_three);

            // 12-PANEL
            $panel12T_1_path_one = env('APP_URL') . "sample/product/12-PANEL/T-1/10002.jpg";
            $panel12T_1_one = date('YmdHis') . "34" . ".jpg";
        
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel12T_1_one, file_get_contents($panel12T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel12T_1_one);
            $image = Image::make($panel12T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel12T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel12T_1_one);

            $panel12T_1_path_two = env('APP_URL') . "sample/product/12-PANEL/T-1/20001.jpg";
            $panel12T_1_two = date('YmdHis') . "35" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel12T_1_two, file_get_contents($panel12T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel12T_1_two);
            $image = Image::make($panel12T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel12T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel12T_1_two);

            $panel12T_1_path_three = env('APP_URL') . "sample/product/12-PANEL/T-1/20003.jpg";
            $panel12T_1_three = date('YmdHis') . "36" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel12T_1_three, file_get_contents($panel12T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel12T_1_three);
            $image = Image::make($panel12T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel12T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel12T_1_three);

            $panel12T_2_path_one = env('APP_URL') . "sample/product/12-PANEL/T-2/10002.jpg";
            $panel12T_2_one = date('YmdHis') . "37" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel12T_2_one, file_get_contents($panel12T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel12T_2_one);
            $image = Image::make($panel12T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel12T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel12T_2_one);

            $panel12T_2_path_two = env('APP_URL') . "sample/product/12-PANEL/T-2/20001.jpg";
            $panel12T_2_two = date('YmdHis') . "38" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel12T_2_two, file_get_contents($panel12T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel12T_2_two);
            $image = Image::make($panel12T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel12T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel12T_2_two);

            $panel12T_2_path_three = env('APP_URL') . "sample/product/12-PANEL/T-2/20003.jpg";
            $panel12T_2_three = date('YmdHis') . "39" . ".jpg";
           
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel12T_2_three, file_get_contents($panel12T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel12T_2_three);
            $image = Image::make($panel12T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel12T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel12T_2_three);

            $panel12T_3_path_one = env('APP_URL') . "sample/product/12-PANEL/T-3/10002.jpg";
            $panel12T_3_one = date('YmdHis') . "40" . ".jpg";
                
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel12T_3_one, file_get_contents($panel12T_3_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel12T_3_one);
            $image = Image::make($panel12T_3_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel12T_3_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel12T_3_one);

            $panel12T_3_path_two = env('APP_URL') . "sample/product/12-PANEL/T-3/20001.jpg";
            $panel12T_3_two = date('YmdHis') . "41" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel12T_3_two, file_get_contents($panel12T_3_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel12T_3_two);
            $image = Image::make($panel12T_3_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel12T_3_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel12T_3_two);

            $panel12T_3_path_three = env('APP_URL') . "sample/product/12-PANEL/T-3/20003.jpg";
            $panel12T_3_three = date('YmdHis') . "42" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel12T_3_three, file_get_contents($panel12T_3_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel12T_3_three);
            $image = Image::make($panel12T_3_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel12T_3_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel12T_3_three);

            // 13-PANEL
            $panel13T_1_path_one = env('APP_URL') . "sample/product/13-PANEL/T-1/10002.jpg";
            $panel13T_1_one = date('YmdHis') . "43" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel13T_1_one, file_get_contents($panel13T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel13T_1_one);
            $image = Image::make($panel13T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel13T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel13T_1_one);

            $panel13T_1_path_two = env('APP_URL') . "sample/product/13-PANEL/T-1/20001.jpg";
            $panel13T_1_two = date('YmdHis') . "44" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel13T_1_two, file_get_contents($panel13T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel13T_1_two);
            $image = Image::make($panel13T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel13T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel13T_1_two);

            $panel13T_1_path_three = env('APP_URL') . "sample/product/13-PANEL/T-1/20003.jpg";
            $panel13T_1_three = date('YmdHis') . "45" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel13T_1_three, file_get_contents($panel13T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel13T_1_three);
            $image = Image::make($panel13T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel13T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel13T_1_three);

            $panel13T_2_path_one = env('APP_URL') . "sample/product/13-PANEL/T-2/10002.jpg";
            $panel13T_2_one = date('YmdHis') . "46" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel13T_2_one, file_get_contents($panel13T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel13T_2_one);
            $image = Image::make($panel13T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel13T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel13T_2_one);

            $panel13T_2_path_two = env('APP_URL') . "sample/product/13-PANEL/T-2/20001.jpg";
            $panel13T_2_two = date('YmdHis') . "47" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel13T_2_two, file_get_contents($panel13T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel13T_2_two);
            $image = Image::make($panel13T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel13T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel13T_2_two);

            $panel13T_2_path_three = env('APP_URL') . "sample/product/13-PANEL/T-2/20003.jpg";
            $panel13T_2_three = date('YmdHis') . "48" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel13T_2_three, file_get_contents($panel13T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel13T_2_three);
            $image = Image::make($panel13T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel13T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel13T_2_three);

            // 14-PANEL
            $panel14T_1_path_one = env('APP_URL') . "sample/product/14-PANEL/T-1/10002.jpg";
            $panel14T_1_one = date('YmdHis') . "49" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel14T_1_one, file_get_contents($panel14T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel14T_1_one);
            $image = Image::make($panel14T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel14T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel14T_1_one);

            $panel14T_1_path_two = env('APP_URL') . "sample/product/14-PANEL/T-1/20001.jpg";
            $panel14T_1_two = date('YmdHis') . "50" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel14T_1_two, file_get_contents($panel14T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel14T_1_two);
            $image = Image::make($panel14T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel14T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel14T_1_two);

            $panel14T_1_path_three = env('APP_URL') . "sample/product/14-PANEL/T-1/20003.jpg";
            $panel14T_1_three = date('YmdHis') . "51" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel14T_1_three, file_get_contents($panel14T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel14T_1_three);
            $image = Image::make($panel14T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel14T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel14T_1_three);

            $panel14T_2_path_one = env('APP_URL') . "sample/product/14-PANEL/T-2/10002.jpg";
            $panel14T_2_one = date('YmdHis') . "52" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel14T_2_one, file_get_contents($panel14T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel14T_2_one);
            $image = Image::make($panel14T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel14T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel14T_2_one);

            $panel14T_2_path_two = env('APP_URL') . "sample/product/14-PANEL/T-2/20001.jpg";
            $panel14T_2_two = date('YmdHis') . "53" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel14T_2_two, file_get_contents($panel14T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel14T_2_two);
            $image = Image::make($panel14T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel14T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel14T_2_two);

            $panel14T_2_path_three = env('APP_URL') . "sample/product/14-PANEL/T-2/20003.jpg";
            $panel14T_2_three = date('YmdHis') . "54" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel14T_2_three, file_get_contents($panel14T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel14T_2_three);
            $image = Image::make($panel14T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel14T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel14T_2_three);

            // 15-PANEL
            $panel15T_1_path_one = env('APP_URL') . "sample/product/15-PANEL/T-1/10002.jpg";
            $panel15T_1_one = date('YmdHis') . "55" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel15T_1_one, file_get_contents($panel15T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel15T_1_one);
            $image = Image::make($panel15T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel15T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel15T_1_one);

            $panel15T_1_path_two = env('APP_URL') . "sample/product/15-PANEL/T-1/20001.jpg";
            $panel15T_1_two = date('YmdHis') . "56" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel15T_1_two, file_get_contents($panel15T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel15T_1_two);
            $image = Image::make($panel15T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel15T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel15T_1_two);

            $panel15T_1_path_three = env('APP_URL') . "sample/product/15-PANEL/T-1/20003.jpg";
            $panel15T_1_three = date('YmdHis') . "57" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel15T_1_three, file_get_contents($panel15T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel15T_1_three);
            $image = Image::make($panel15T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel15T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel15T_1_three);

            $panel15T_2_path_one = env('APP_URL') . "sample/product/15-PANEL/T-2/10002.jpg";
            $panel15T_2_one = date('YmdHis') . "58" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel15T_2_one, file_get_contents($panel15T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel15T_2_one);
            $image = Image::make($panel15T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel15T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel15T_2_one);

            $panel15T_2_path_two = env('APP_URL') . "sample/product/15-PANEL/T-2/20001.jpg";
            $panel15T_2_two = date('YmdHis') . "59" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel15T_2_two, file_get_contents($panel15T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel15T_2_two);
            $image = Image::make($panel15T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel15T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel15T_2_two);

            $panel15T_2_path_three = env('APP_URL') . "sample/product/15-PANEL/T-2/20003.jpg";
            $panel15T_2_three = date('YmdHis') . "60" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel15T_2_three, file_get_contents($panel15T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel15T_2_three);
            $image = Image::make($panel15T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel15T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel15T_2_three);

            // 16-PANEL
            $panel16T_1_path_one = env('APP_URL') . "sample/product/16-PANEL/T-1/R010001.jpg";
            $panel16T_1_one = date('YmdHis') . "61" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel16T_1_one, file_get_contents($panel16T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel16T_1_one);
            $image = Image::make($panel16T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel16T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel16T_1_one);

            $panel16T_1_path_two = env('APP_URL') . "sample/product/16-PANEL/T-1/R010002.jpg";
            $panel16T_1_two = date('YmdHis') . "62" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel16T_1_two, file_get_contents($panel16T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel16T_1_two);
            $image = Image::make($panel16T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel16T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel16T_1_two);

            $panel16T_1_path_three = env('APP_URL') . "sample/product/16-PANEL/T-1/R010003.jpg";
            $panel16T_1_three = date('YmdHis') . "63" . ".jpg";
                
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel16T_1_three, file_get_contents($panel16T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel16T_1_three);
            $image = Image::make($panel16T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel16T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel16T_1_three);

            $panel16T_2_path_one = env('APP_URL') . "sample/product/16-PANEL/T-2/3P6_160002.jpg";
            $panel16T_2_one = date('YmdHis') . "64" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel16T_2_one, file_get_contents($panel16T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel16T_2_one);
            $image = Image::make($panel16T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel16T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel16T_2_one);

            $panel16T_2_path_two = env('APP_URL') . "sample/product/16-PANEL/T-2/10001.jpg";
            $panel16T_2_two = date('YmdHis') . "65" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel16T_2_two, file_get_contents($panel16T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel16T_2_two);
            $image = Image::make($panel16T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel16T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel16T_2_two);

            $panel16T_2_path_three = env('APP_URL') . "sample/product/16-PANEL/T-2/10003.jpg";
            $panel16T_2_three = date('YmdHis') . "66" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel16T_2_three, file_get_contents($panel16T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel16T_2_three);
            $image = Image::make($panel16T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel16T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel16T_2_three);

            // 17-PANEL
            $panel17T_1_path_one = env('APP_URL') . "sample/product/17-PANEL/T-1/R010002.jpg";
            $panel17T_1_one = date('YmdHis') . "67" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel17T_1_one, file_get_contents($panel17T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel17T_1_one);
            $image = Image::make($panel17T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel17T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel17T_1_one);

            $panel17T_1_path_two = env('APP_URL') . "sample/product/17-PANEL/T-1/R010001.jpg";
            $panel17T_1_two = date('YmdHis') . "68" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel17T_1_two, file_get_contents($panel17T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel17T_1_two);
            $image = Image::make($panel17T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel17T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel17T_1_two);

            $panel17T_1_path_three = env('APP_URL') . "sample/product/17-PANEL/T-1/R010003.jpg";
            $panel17T_1_three = date('YmdHis') . "69" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel17T_1_three, file_get_contents($panel17T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel17T_1_three);
            $image = Image::make($panel17T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel17T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel17T_1_three);

            // 18-PANEL
            $panel18T_1_path_one = env('APP_URL') . "sample/product/18-PANEL/T-1/R010002.jpg";
            $panel18T_1_one = date('YmdHis') . "70" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel18T_1_one, file_get_contents($panel18T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel18T_1_one);
            $image = Image::make($panel18T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel18T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel18T_1_one);

            $panel18T_1_path_two = env('APP_URL') . "sample/product/18-PANEL/T-1/R010001.jpg";
            $panel18T_1_two = date('YmdHis') . "71" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel18T_1_two, file_get_contents($panel18T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel18T_1_two);
            $image = Image::make($panel18T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel18T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel18T_1_two);

            $panel18T_1_path_three = env('APP_URL') . "sample/product/18-PANEL/T-1/R010003.jpg";
            $panel18T_1_three = date('YmdHis') . "72" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel18T_1_three, file_get_contents($panel18T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel18T_1_three);
            $image = Image::make($panel18T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel18T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel18T_1_three);

            $panel18T_2_path_one = env('APP_URL') . "sample/product/18-PANEL/T-2/3P60002.jpg";
            $panel18T_2_one = date('YmdHis') . "73" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel18T_2_one, file_get_contents($panel18T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel18T_2_one);
            $image = Image::make($panel18T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel18T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel18T_2_one);

            $panel18T_2_path_two = env('APP_URL') . "sample/product/18-PANEL/T-2/Mr0001.jpg";
            $panel18T_2_two = date('YmdHis') . "74" . ".jpg";
        
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel18T_2_two, file_get_contents($panel18T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel18T_2_two);
            $image = Image::make($panel18T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel18T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel18T_2_two);

            $panel18T_2_path_three = env('APP_URL') . "sample/product/18-PANEL/T-2/Mr0003.jpg";
            $panel18T_2_three = date('YmdHis') . "75" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel18T_2_three, file_get_contents($panel18T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel18T_2_three);
            $image = Image::make($panel18T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel18T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel18T_2_three);

            // 21-PANEL
            $panel21T_1_path_one = env('APP_URL') . "sample/product/21-PANEL/T-1/R010002.jpg";
            $panel21T_1_one = date('YmdHis') . "76" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel21T_1_one, file_get_contents($panel21T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel21T_1_one);
            $image = Image::make($panel21T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel21T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel21T_1_one);

            $panel21T_1_path_two = env('APP_URL') . "sample/product/21-PANEL/T-1/R010001.jpg";
            $panel21T_1_two = date('YmdHis') . "77" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel21T_1_two, file_get_contents($panel21T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel21T_1_two);
            $image = Image::make($panel21T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel21T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel21T_1_two);

            $panel21T_1_path_three = env('APP_URL') . "sample/product/21-PANEL/T-1/R010003.jpg";
            $panel21T_1_three = date('YmdHis') . "78" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel21T_1_three, file_get_contents($panel21T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel21T_1_three);
            $image = Image::make($panel21T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel21T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel21T_1_three);

            $panel21T_2_path_one = env('APP_URL') . "sample/product/21-PANEL/T-2/3P70002.jpg";
            $panel21T_2_one = date('YmdHis') . "79" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel21T_2_one, file_get_contents($panel21T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel21T_2_one);
            $image = Image::make($panel21T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel21T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel21T_2_one);

            $panel21T_2_path_two = env('APP_URL') . "sample/product/21-PANEL/T-2/Mr0001.jpg";
            $panel21T_2_two = date('YmdHis') . "80" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel21T_2_two, file_get_contents($panel21T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel21T_2_two);
            $image = Image::make($panel21T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel21T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel21T_2_two);

            $panel21T_2_path_three = env('APP_URL') . "sample/product/21-PANEL/T-2/Mr0003.jpg";
            $panel21T_2_three = date('YmdHis') . "81" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel21T_2_three, file_get_contents($panel21T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel21T_2_three);
            $image = Image::make($panel21T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel21T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel21T_2_three);

            // 24-PANEL
            $panel24T_1_path_one = env('APP_URL') . "sample/product/24-PANEL/T-1/R010002.jpg";
            $panel24T_1_one = date('YmdHis') . "82" . ".jpg";
           
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel24T_1_one, file_get_contents($panel24T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel24T_1_one);
            $image = Image::make($panel24T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel24T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel24T_1_one);

            $panel24T_1_path_two = env('APP_URL') . "sample/product/24-PANEL/T-1/R010001.jpg";
            $panel24T_1_two = date('YmdHis') . "83" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel24T_1_two, file_get_contents($panel24T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel24T_1_two);
            $image = Image::make($panel24T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel24T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel24T_1_two);

            $panel24T_1_path_three = env('APP_URL') . "sample/product/24-PANEL/T-1/R010003.jpg";
            $panel24T_1_three = date('YmdHis') . "84" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel24T_1_three, file_get_contents($panel24T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel24T_1_three);
            $image = Image::make($panel24T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel24T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel24T_1_three);

            $panel24T_2_path_one = env('APP_URL') . "sample/product/24-PANEL/T-2/10002.jpg";
            $panel24T_2_one = date('YmdHis') . "85" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel24T_2_one, file_get_contents($panel24T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel24T_2_one);
            $image = Image::make($panel24T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel24T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel24T_2_one);

            $panel24T_2_path_two = env('APP_URL') . "sample/product/24-PANEL/T-2/SSEMH0817-Mr0001.jpg";
            $panel24T_2_two = date('YmdHis') . "86" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel24T_2_two, file_get_contents($panel24T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel24T_2_two);
            $image = Image::make($panel24T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel24T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel24T_2_two);

            $panel24T_2_path_three = env('APP_URL') . "sample/product/24-PANEL/T-2/SSEMH0817-Mr0003.jpg";
            $panel24T_2_three = date('YmdHis') . "87" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel24T_2_three, file_get_contents($panel24T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel24T_2_three);
            $image = Image::make($panel24T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel24T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel24T_2_three);

            // 27-PANEL
            $panel27T_1_path_one = env('APP_URL') . "sample/product/27-PANEL/T-1/R010002.jpg";
            $panel27T_1_one = date('YmdHis') . "88" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel27T_1_one, file_get_contents($panel27T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel27T_1_one);
            $image = Image::make($panel27T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel27T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel27T_1_one);

            $panel27T_1_path_two = env('APP_URL') . "sample/product/27-PANEL/T-1/R010001.jpg";
            $panel27T_1_two = date('YmdHis') . "89" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel27T_1_two, file_get_contents($panel27T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel27T_1_two);
            $image = Image::make($panel27T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel27T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel27T_1_two);

            $panel27T_1_path_three = env('APP_URL') . "sample/product/27-PANEL/T-1/R010003.jpg";
            $panel27T_1_three = date('YmdHis') . "90" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel27T_1_three, file_get_contents($panel27T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel27T_1_three);
            $image = Image::make($panel27T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel27T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel27T_1_three);

            $panel27T_2_path_one = env('APP_URL') . "sample/product/27-PANEL/T-2/10002.jpg";
            $panel27T_2_one = date('YmdHis') . "91" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel27T_2_one, file_get_contents($panel27T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel27T_2_one);
            $image = Image::make($panel27T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel27T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel27T_2_one);

            $panel27T_2_path_two = env('APP_URL') . "sample/product/27-PANEL/T-2/R0_SSEMH0952-Mr0001.jpg";
            $panel27T_2_two = date('YmdHis') . "92" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel27T_2_two, file_get_contents($panel27T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel27T_2_two);
            $image = Image::make($panel27T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel27T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel27T_2_two);

            $panel27T_2_path_three = env('APP_URL') . "sample/product/27-PANEL/T-2/R0_SSEMH0952-Mr0003.jpg";
            $panel27T_2_three = date('YmdHis') . "93" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel27T_2_three, file_get_contents($panel27T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel27T_2_three);
            $image = Image::make($panel27T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel27T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel27T_2_three);

            // 29-PANEL
            $panel29T_1_path_one = env('APP_URL') . "sample/product/29-PANEL/T-1/R010001.jpg";
            $panel29T_1_one = date('YmdHis') . "94" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel29T_1_one, file_get_contents($panel29T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel29T_1_one);
            $image = Image::make($panel29T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel29T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel29T_1_one);

            $panel29T_1_path_two = env('APP_URL') . "sample/product/29-PANEL/T-1/R010002.jpg";
            $panel29T_1_two = date('YmdHis') . "95" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel29T_1_two, file_get_contents($panel29T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel29T_1_two);
            $image = Image::make($panel29T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel29T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel29T_1_two);

            $panel29T_1_path_three = env('APP_URL') . "sample/product/29-PANEL/T-1/R010003.jpg";
            $panel29T_1_three = date('YmdHis') . "96" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel29T_1_three, file_get_contents($panel29T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel29T_1_three);
            $image = Image::make($panel29T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel29T_1_three, $image->stream()->__toString());
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel29T_1_three);

            $panel29T_2_path_one = env('APP_URL') . "sample/product/29-PANEL/T-2/3P100002.jpg";
            $panel29T_2_one = date('YmdHis') . "97" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel29T_2_one, file_get_contents($panel29T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel29T_2_one);
            $image = Image::make($panel29T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel29T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel29T_2_one);

            $panel29T_2_path_two = env('APP_URL') . "sample/product/29-PANEL/T-2/R010001.jpg";
            $panel29T_2_two = date('YmdHis') . "98" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel29T_2_two, file_get_contents($panel29T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel29T_2_two);
            $image = Image::make($panel29T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel29T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel29T_2_two);

            $panel29T_2_path_three = env('APP_URL') . "sample/product/29-PANEL/T-2/R010003.jpg";
            $panel29T_2_three = date('YmdHis') . "99" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel29T_2_three, file_get_contents($panel29T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel29T_2_three);
            $image = Image::make($panel29T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel29T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel29T_2_three);


            // 30-PANEL
            $panel30T_1_path_one = env('APP_URL') . "sample/product/30-PANEL/T-1/R010001.jpg";
            $panel30T_1_one = date('YmdHis') . "100" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel30T_1_one, file_get_contents($panel30T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel30T_1_one);
            $image = Image::make($panel30T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel30T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel30T_1_one);

            $panel30T_1_path_two = env('APP_URL') . "sample/product/30-PANEL/T-1/R010002.jpg";
            $panel30T_1_two = date('YmdHis') . "101" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel30T_1_two, file_get_contents($panel30T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel30T_1_two);
            $image = Image::make($panel30T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel30T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel30T_1_two);

            $panel30T_1_path_three = env('APP_URL') . "sample/product/30-PANEL/T-1/R010003.jpg";
            $panel30T_1_three = date('YmdHis') . "102" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel30T_1_three, file_get_contents($panel30T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel30T_1_three);
            $image = Image::make($panel30T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel30T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel30T_1_three);

            $panel30T_2_path_one = env('APP_URL') . "sample/product/30-PANEL/T-2/3P100002.jpg";
            $panel30T_2_one = date('YmdHis') . "103" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel30T_2_one, file_get_contents($panel30T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel30T_2_one);
            $image = Image::make($panel30T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel30T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel30T_2_one);

            $panel30T_2_path_two = env('APP_URL') . "sample/product/30-PANEL/T-2/3P1010001.jpg";
            $panel30T_2_two = date('YmdHis') . "104" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel30T_2_two, file_get_contents($panel30T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel30T_2_two);
            $image = Image::make($panel30T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel30T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel30T_2_two);

            $panel30T_2_path_three = env('APP_URL') . "sample/product/30-PANEL/T-2/3P1010003.jpg";
            $panel30T_2_three = date('YmdHis') . "105" . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $panel30T_2_three, file_get_contents($panel30T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $panel30T_2_three);
            $image = Image::make($panel30T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $panel30T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $panel30T_2_three);

            $commercial_path_one = env('APP_URL') . "sample/product/commercial/10002.jpg";
            $commercial_one = rand(1000, 9999) . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $commercial_one, file_get_contents($commercial_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $commercial_one);
            $image = Image::make($commercial_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $commercial_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $commercial_one);

            $commercial_path_two = env('APP_URL') . "sample/product/commercial/20001.jpg";
            $commercial_two = rand(1000, 9999) . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $commercial_two, file_get_contents($commercial_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $commercial_two);
            $image = Image::make($commercial_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $commercial_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $commercial_two);

            $commercial_path_three = env('APP_URL') . "sample/product/commercial/20003.jpg";
            $commercial_three = rand(1000, 9999) . ".jpg";
            
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $commercial_three, file_get_contents($commercial_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $commercial_three);
            $image = Image::make($commercial_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $commercial_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $commercial_three);

            //4-Mono-T-1
            $m4T_1_path_one = env('APP_URL') . "sample/product/4-Mono-T-1/1.jpeg";
            $m4T_1_one = date('YmdHis') . "900" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m4T_1_one, file_get_contents($m4T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m4T_1_one);
            $image = Image::make($m4T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m4T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m4T_1_one);

            $m4T_1_path_two = env('APP_URL') . "sample/product/4-Mono-T-1/2.jpeg";
            $m4T_1_two = date('YmdHis') . "902" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m4T_1_two, file_get_contents($m4T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m4T_1_two);
            $image = Image::make($m4T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m4T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m4T_1_two);

            $m4T_1_path_three = env('APP_URL') . "sample/product/4-Mono-T-1/3.jpeg";
            $m4T_1_three = date('YmdHis') . "903" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m4T_1_three, file_get_contents($m4T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m4T_1_three);
            $image = Image::make($m4T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m4T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m4T_1_three);

            //4-Mono-T-2
            $m4T_2_path_one = env('APP_URL') . "sample/product/4-Mono-T-2/1.jpeg";
            $m4T_2_one = date('YmdHis') . "904" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m4T_2_one, file_get_contents($m4T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m4T_2_one);
            $image = Image::make($m4T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m4T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m4T_2_one);

            $m4T_2_path_two = env('APP_URL') . "sample/product/4-Mono-T-2/2.jpeg";
            $m4T_2_two = date('YmdHis') . "905" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m4T_2_two, file_get_contents($m4T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m4T_2_two);
            $image = Image::make($m4T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m4T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m4T_2_two);

            $m4T_2_path_three = env('APP_URL') . "sample/product/4-Mono-T-2/3.jpeg";
            $m4T_2_three = date('YmdHis') . "906" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m4T_2_three, file_get_contents($m4T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m4T_2_three);
            $image = Image::make($m4T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m4T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m4T_2_three);

            //6-Mono-T-1
            $m6T_1_path_one = env('APP_URL') . "sample/product/6-Mono-T-1/1.jpeg";
            $m6T_1_one = date('YmdHis') . "907" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m6T_1_one, file_get_contents($m6T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m6T_1_one);
            $image = Image::make($m6T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m6T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m6T_1_one);

            $m6T_1_path_two = env('APP_URL') . "sample/product/6-Mono-T-1/2.jpeg";
            $m6T_1_two = date('YmdHis') . "908" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m6T_1_two, file_get_contents($m6T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m6T_1_two);
            $image = Image::make($m6T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m6T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m6T_1_two);

            $m6T_1_path_three = env('APP_URL') . "sample/product/6-Mono-T-1/3.jpeg";
            $m6T_1_three = date('YmdHis') . "909" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m6T_1_three, file_get_contents($m6T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m6T_1_three);
            $image = Image::make($m6T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m6T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m6T_1_three);


            //6-Mono-T-2
            $m6T_2_path_one = env('APP_URL') . "sample/product/6-Mono-T-2/1.jpeg";
            $m6T_2_one = date('YmdHis') . "910" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m6T_2_one, file_get_contents($m6T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m6T_2_one);
            $image = Image::make($m6T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m6T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m6T_2_one);

            $m6T_2_path_two = env('APP_URL') . "sample/product/6-Mono-T-2/2.jpeg";
            $m6T_2_two = date('YmdHis') . "911" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m6T_2_two, file_get_contents($m6T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m6T_2_two);
            $image = Image::make($m6T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m6T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m6T_2_two);

            $m6T_2_path_three = env('APP_URL') . "sample/product/6-Mono-T-2/3.jpeg";
            $m6T_2_three = date('YmdHis') . "912" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m6T_2_three, file_get_contents($m6T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m6T_2_three);
            $image = Image::make($m6T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m6T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m6T_2_three);

            //6-Mono-T-3
            $m6T_3_path_one = env('APP_URL') . "sample/product/6-Mono-T-3/1.jpeg";
            $m6T_3_one = date('YmdHis') . "913" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m6T_3_one, file_get_contents($m6T_3_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m6T_3_one);
            $image = Image::make($m6T_3_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m6T_3_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m6T_3_one);

            $m6T_3_path_two = env('APP_URL') . "sample/product/6-Mono-T-3/2.jpeg";
            $m6T_3_two = date('YmdHis') . "914" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m6T_3_two, file_get_contents($m6T_3_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m6T_3_two);
            $image = Image::make($m6T_3_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m6T_3_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m6T_3_two);

            $m6T_3_path_three = env('APP_URL') . "sample/product/6-Mono-T-3/3.jpeg";
            $m6T_3_three = date('YmdHis') . "915" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m6T_3_three, file_get_contents($m6T_3_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m6T_3_three);
            $image = Image::make($m6T_3_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m6T_3_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m6T_3_three);

            //7-Mono-T-1
            $m7T_1_path_one = env('APP_URL') . "sample/product/7-Mono-T-1/1.jpeg";
            $m7T_1_one = date('YmdHis') . "916" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m7T_1_one, file_get_contents($m7T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m7T_1_one);
            $image = Image::make($m7T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m7T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m7T_1_one);

            $m7T_1_path_two = env('APP_URL') . "sample/product/7-Mono-T-1/2.jpeg";
            $m7T_1_two = date('YmdHis') . "917" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m7T_1_two, file_get_contents($m7T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m7T_1_two);
            $image = Image::make($m7T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m7T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m7T_1_two);

            $m7T_1_path_three = env('APP_URL') . "sample/product/7-Mono-T-1/3.jpeg";
            $m7T_1_three = date('YmdHis') . "918" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m7T_1_three, file_get_contents($m7T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m7T_1_three);
            $image = Image::make($m7T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m7T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m7T_1_three);


            //7-Mono-T-2
            $m7T_2_path_one = env('APP_URL') . "sample/product/7-Mono-T-2/1.jpeg";
            $m7T_2_one = date('YmdHis') . "919" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m7T_2_one, file_get_contents($m7T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m7T_2_one);
            $image = Image::make($m7T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m7T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m7T_2_one);

            $m7T_2_path_two = env('APP_URL') . "sample/product/7-Mono-T-2/2.jpeg";
            $m7T_2_two = date('YmdHis') . "920" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m7T_2_two, file_get_contents($m7T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m7T_2_two);
            $image = Image::make($m7T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m7T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m7T_2_two);

            $m7T_2_path_three = env('APP_URL') . "sample/product/7-Mono-T-2/3.jpeg";
            $m7T_2_three = date('YmdHis') . "921" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m7T_2_three, file_get_contents($m7T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m7T_2_three);
            $image = Image::make($m7T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m7T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m7T_2_three);

            //8-Mono-T-1
            $m8T_1_path_one = env('APP_URL') . "sample/product/8-Mono-T-1/1.jpeg";
            $m8T_1_one = date('YmdHis') . "922" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m8T_1_one, file_get_contents($m8T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m8T_1_one);
            $image = Image::make($m8T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m8T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m8T_1_one);

            $m8T_1_path_two = env('APP_URL') . "sample/product/8-Mono-T-1/2.jpeg";
            $m8T_1_two = date('YmdHis') . "923" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m8T_1_two, file_get_contents($m8T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m8T_1_two);
            $image = Image::make($m8T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m8T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m8T_1_two);

            $m8T_1_path_three = env('APP_URL') . "sample/product/8-Mono-T-1/3.jpeg";
            $m8T_1_three = date('YmdHis') . "924" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m8T_1_three, file_get_contents($m8T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m8T_1_three);
            $image = Image::make($m8T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m8T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m8T_1_three);


            //8-Mono-T-2
            $m8T_2_path_one = env('APP_URL') . "sample/product/8-Mono-T-2/1.jpeg";
            $m8T_2_one = date('YmdHis') . "925" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m8T_2_one, file_get_contents($m8T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m8T_2_one);
            $image = Image::make($m8T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m8T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m8T_2_one);

            $m8T_2_path_two = env('APP_URL') . "sample/product/8-Mono-T-2/2.jpeg";
            $m8T_2_two = date('YmdHis') . "926" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m8T_2_two, file_get_contents($m8T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m8T_2_two);
            $image = Image::make($m8T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m8T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m8T_2_two);

            $m8T_2_path_three = env('APP_URL') . "sample/product/8-Mono-T-2/3.jpeg";
            $m8T_2_three = date('YmdHis') . "927" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m8T_2_three, file_get_contents($m8T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m8T_2_three);
            $image = Image::make($m8T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m8T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m8T_2_three);

            //9-Mono-T-1
            $m9T_1_path_one = env('APP_URL') . "sample/product/9-Mono-T-1/1.jpeg";
            $m9T_1_one = date('YmdHis') . "928" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m9T_1_one, file_get_contents($m9T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m9T_1_one);
            $image = Image::make($m9T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m9T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m9T_1_one);

            $m9T_1_path_two = env('APP_URL') . "sample/product/9-Mono-T-1/2.jpeg";
            $m9T_1_two = date('YmdHis') . "929" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m9T_1_two, file_get_contents($m9T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m9T_1_two);
            $image = Image::make($m9T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m9T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m9T_1_two);

            $m9T_1_path_three = env('APP_URL') . "sample/product/9-Mono-T-1/3.jpeg";
            $m9T_1_three = date('YmdHis') . "930" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m9T_1_three, file_get_contents($m9T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m9T_1_three);
            $image = Image::make($m9T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m9T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m9T_1_three);


            //9-Mono-T-2
            $m9T_2_path_one = env('APP_URL') . "sample/product/9-Mono-T-2/1.jpeg";
            $m9T_2_one = date('YmdHis') . "931" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m9T_2_one, file_get_contents($m9T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m9T_2_one);
            $image = Image::make($m9T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m9T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m9T_2_one);

            $m9T_2_path_two = env('APP_URL') . "sample/product/9-Mono-T-2/2.jpeg";
            $m9T_2_two = date('YmdHis') . "932" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m9T_2_two, file_get_contents($m9T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m9T_2_two);
            $image = Image::make($m9T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m9T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m9T_2_two);

            $m9T_2_path_three = env('APP_URL') . "sample/product/9-Mono-T-2/3.jpeg";
            $m9T_2_three = date('YmdHis') . "933" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m9T_2_three, file_get_contents($m9T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m9T_2_three);
            $image = Image::make($m9T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m9T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m9T_2_three);

            //10-Mono-T-3
            $m9T_3_path_one = env('APP_URL') . "sample/product/9-Mono-T-3/1.jpeg";
            $m9T_3_one = date('YmdHis') . "934" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m9T_3_one, file_get_contents($m9T_3_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m9T_3_one);
            $image = Image::make($m9T_3_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m9T_3_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m9T_3_one);

            $m9T_3_path_two = env('APP_URL') . "sample/product/9-Mono-T-3/2.jpeg";
            $m9T_3_two = date('YmdHis') . "935" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m9T_3_two, file_get_contents($m9T_3_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m9T_3_two);
            $image = Image::make($m9T_3_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m9T_3_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m9T_3_two);

            $m9T_3_path_three = env('APP_URL') . "sample/product/9-Mono-T-3/3.jpeg";
            $m9T_3_three = date('YmdHis') . "936" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m9T_3_three, file_get_contents($m9T_3_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m9T_3_three);
            $image = Image::make($m9T_3_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m9T_3_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m9T_3_three);

            //10-Mono-T-1
            $m10T_1_path_one = env('APP_URL') . "sample/product/10-Mono-T-1/1.jpeg";
            $m10T_1_one = date('YmdHis') . "937" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m10T_1_one, file_get_contents($m10T_1_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m10T_1_one);
            $image = Image::make($m10T_1_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m10T_1_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m10T_1_one);

            $m10T_1_path_two = env('APP_URL') . "sample/product/10-Mono-T-1/2.jpeg";
            $m10T_1_two = date('YmdHis') . "938" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m10T_1_two, file_get_contents($m10T_1_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m10T_1_two);
            $image = Image::make($m10T_1_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m10T_1_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m10T_1_two);

            $m10T_1_path_three = env('APP_URL') . "sample/product/10-Mono-T-1/3.jpeg";
            $m10T_1_three = date('YmdHis') . "939" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m10T_1_three, file_get_contents($m10T_1_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m10T_1_three);
            $image = Image::make($m10T_1_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m10T_1_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m10T_1_three);


            //10-Mono-T-2
            $m10T_2_path_one = env('APP_URL') . "sample/product/10-Mono-T-2/1.jpeg";
            $m10T_2_one = date('YmdHis') . "940" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m10T_2_one, file_get_contents($m10T_2_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m10T_2_one);
            $image = Image::make($m10T_2_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m10T_2_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m10T_2_one);

            $m10T_2_path_two = env('APP_URL') . "sample/product/10-Mono-T-2/2.jpeg";
            $m10T_2_two = date('YmdHis') . "941" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m10T_2_two, file_get_contents($m10T_2_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m10T_2_two);
            $image = Image::make($m10T_2_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m10T_2_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m10T_2_two);

            $m10T_2_path_three = env('APP_URL') . "sample/product/10-Mono-T-2/3.jpeg";
            $m10T_2_three = date('YmdHis') . "942" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m10T_2_three, file_get_contents($m10T_2_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m10T_2_three);
            $image = Image::make($m10T_2_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m10T_2_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m10T_2_three);

            //10-Mono-T-3
            $m10T_3_path_one = env('APP_URL') . "sample/product/10-Mono-T-3/1.jpeg";
            $m10T_3_one = date('YmdHis') . "943" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m10T_3_one, file_get_contents($m10T_3_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m10T_3_one);
            $image = Image::make($m10T_3_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m10T_3_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m10T_3_one);

            $m10T_3_path_two = env('APP_URL') . "sample/product/10-Mono-T-3/2.jpeg";
            $m10T_3_two = date('YmdHis') . "944" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m10T_3_two, file_get_contents($m10T_3_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m10T_3_two);
            $image = Image::make($m10T_3_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m10T_3_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m10T_3_two);

            $m10T_3_path_three = env('APP_URL') . "sample/product/10-Mono-T-3/3.jpeg";
            $m10T_3_three = date('YmdHis') . "945" . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $m10T_3_three, file_get_contents($m10T_3_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $m10T_3_three);
            $image = Image::make($m10T_3_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $m10T_3_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $m10T_3_three);

            DB::table('products')->insert([
                [
                    'name' => '8 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel8T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel8T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel8T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel8T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel8T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel8T_1_three,
                    'status' => 1,
                    'user_id' => 1,
                    'company_id' => 1
                ],
                [
                    'name' => '8 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel8T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel8T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel8T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel8T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel8T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel8T_2_three,
                    'status' => 1,
                    'user_id' => 1,
                    'company_id' => 1
                ],
                [
                    'name' => '8 Panel_T-3',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel8T_3_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel8T_3_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel8T_3_three,
                    'thumb_thumb_image_one' => 'public/1/products/resize_image/' . $panel8T_3_one,
                    'thumb_thumb_image_two' => 'public/1/products/resize_image/' . $panel8T_3_two,
                    'thumb_thumb_image_three' => 'public/1/products/resize_image/' . $panel8T_3_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '9 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel9T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel9T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel9T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel9T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel9T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel9T_1_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1],
                [
                    'name' => '9 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel9T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel9T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel9T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel9T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel9T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel9T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1],
                [
                    'name' => '9 Panel_T-3',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel9T_3_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel9T_3_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel9T_3_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel9T_3_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel9T_3_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel9T_3_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '10 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel10T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel10T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel10T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel10T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel10T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel10T_1_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '10 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel10T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel10T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel10T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel10T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel10T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel10T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '10 Panel_T-3',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel10T_3_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel10T_3_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel10T_3_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel10T_3_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel10T_3_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel10T_3_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '11 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel11T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel11T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel11T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel11T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel11T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel11T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '11 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel11T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel11T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel11T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel11T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel11T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel11T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '12 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel12T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel12T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel12T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel12T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel12T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel12T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '12 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel12T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel12T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel12T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel12T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel12T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel12T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '12 Panel_T-3',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel12T_3_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel12T_3_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel12T_3_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel12T_3_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel12T_3_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel12T_3_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '13 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel13T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel13T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel13T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel13T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel13T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel13T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '13 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel13T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel13T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel13T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel13T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel13T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel13T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '14 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel14T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel14T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel14T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel14T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel14T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel14T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '14 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel14T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel14T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel14T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel14T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel14T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel14T_2_three,
                    'status' => 1,
                    'user_id' => 1,
                    'company_id' => 1
                ],
                [
                    'name' => '15 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel15T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel15T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel15T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel15T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel15T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel15T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '15 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel15T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel15T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel15T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel15T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel15T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel15T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '16 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel16T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel16T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel16T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel16T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel16T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel16T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '16 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel16T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel16T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel16T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel16T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel16T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel16T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '17 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel17T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel17T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel17T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel17T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel17T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel17T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '18 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel18T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel18T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel18T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel18T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel18T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel18T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '18 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel18T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel18T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel18T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel18T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel18T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel18T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '21 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel21T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel21T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel21T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel21T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel21T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel21T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '21 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel21T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel21T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel21T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel21T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel21T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel21T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '24 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel24T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel24T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel24T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel24T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel24T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel24T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '24 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel24T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel24T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel24T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel24T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel24T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel24T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '27 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel27T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel27T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel27T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel27T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel27T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel27T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '27 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel27T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel27T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel27T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel27T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel27T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel27T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '29 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel29T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel29T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel29T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel29T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel29T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel29T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '29 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel29T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel29T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel29T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel29T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel29T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel29T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '30 Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel30T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel30T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel30T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel30T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel30T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel30T_1_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '30 Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $panel30T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $panel30T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $panel30T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $panel30T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $panel30T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $panel30T_2_three,
                    'status' => 1, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => 'Commercial Installations',
                    'description' => 'Our Previous Project Installation Photos',
                    'image_one' => 'public/1/products/thumbnail/' . $commercial_one,
                    'image_two' => 'public/1/products/thumbnail/' . $commercial_two,
                    'image_three' => 'public/1/products/thumbnail/' . $commercial_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $commercial_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $commercial_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $commercial_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '4 Mono Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m4T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m4T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m4T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m4T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m4T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m4T_1_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '4 Mono Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m4T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m4T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m4T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m4T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m4T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m4T_2_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '6 Mono Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m6T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m6T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m6T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m6T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m6T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m6T_1_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '6 Mono Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m6T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m6T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m6T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m6T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m6T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m6T_2_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '6 Mono Panel_T-3',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m6T_3_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m6T_3_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m6T_3_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m6T_3_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m6T_3_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m6T_3_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '7 Mono Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m7T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m7T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m7T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m7T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m7T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m7T_1_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '7 Mono Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m7T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m7T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m7T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m7T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m7T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m7T_2_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '8 Mono Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m8T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m8T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m8T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m8T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m8T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m8T_1_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '8 Mono Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m8T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m8T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m8T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m8T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m8T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m8T_2_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '9 Mono Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m9T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m9T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m9T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m9T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m9T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m9T_1_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '9 Mono Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m9T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m9T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m9T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m9T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m9T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m9T_2_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '9 Mono Panel_T-3',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m9T_3_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m9T_3_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m9T_3_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m9T_3_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m9T_3_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m9T_3_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '10 Mono Panel_T-1',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m10T_1_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m10T_1_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m10T_1_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m10T_1_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m10T_1_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m10T_1_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '10 Mono Panel_T-2',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m10T_2_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m10T_2_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m10T_2_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m10T_2_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m10T_2_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m10T_2_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
                [
                    'name' => '10 Mono Panel_T-3',
                    'description' => '',
                    'image_one' => 'public/1/products/thumbnail/' . $m10T_3_one,
                    'image_two' => 'public/1/products/thumbnail/' . $m10T_3_two,
                    'image_three' => 'public/1/products/thumbnail/' . $m10T_3_three,
                    'thumb_image_one' => 'public/1/products/resize_image/' . $m10T_3_one,
                    'thumb_image_two' => 'public/1/products/resize_image/' . $m10T_3_two,
                    'thumb_image_three' => 'public/1/products/resize_image/' . $m10T_3_three,
                    'status' => 0, 'user_id' => 1, 'company_id' => 1
                ],
            ]);
        }

        if($userDatas->company_category != 1) {
            $commercial_path_one = env('APP_URL') . "sample/product/sample/1.jpg";
            $commercial_one = rand(1000, 9999) . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $commercial_one, file_get_contents($commercial_path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $commercial_one);
            $image = Image::make($commercial_path_one)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $commercial_one, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $commercial_one);

            $commercial_path_two = env('APP_URL') . "sample/product/sample/2.jpg";
            $commercial_two = rand(1000, 9999) . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $commercial_two, file_get_contents($commercial_path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $commercial_two);
            $image = Image::make($commercial_path_two)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $commercial_two, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $commercial_two);

            $commercial_path_three = env('APP_URL') . "sample/product/sample/3.jpg";
            $commercial_three = rand(1000, 9999) . ".jpg";
            Storage::disk('s3')->put("public/1/products/thumbnail/" . $commercial_three, file_get_contents($commercial_path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/products/thumbnail/" . $commercial_three);
            $image = Image::make($commercial_path_three)->resize(64, 64);
            Storage::disk('s3')->put("public/1/products/resize_image/" . $commercial_three, $image->stream()->__toString(),'public');
            $publicUrlThumbnail = Storage::disk('s3')->url("public/1/products/resize_image/" . $commercial_three);

            DB::table('products')->insert([
                ['name' => 'Sample Product', 'image_one' => 'public/1/products/thumbnail/' . $commercial_one, 'image_two' => 'public/1/products/thumbnail/' . $commercial_two, 'image_three' => 'public/1/products/thumbnail/' . $commercial_three,'thumb_image_one' => 'public/1/products/resize_image/' . $commercial_one, 'thumb_image_two' => 'public/1/products/resize_image/' . $commercial_two, 'thumb_image_three' => 'public/1/products/resize_image/' . $commercial_three, 'status' => 0, 'user_id' => 1, 'company_id' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
            ]);
        }
    }
}
