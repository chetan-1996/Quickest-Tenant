<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class TestimonialsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userDatas = DB::table('users')->where('id', '=', 1)->select(['company_category', 'name', 'email'])->first();

        if ($userDatas->company_category == 1) {
            $path_one = env('APP_URL') . "sample/testimonial/t-1.png";
            $filename_one = 'public/1/testimonials/'.date('YmdHis') . "106" . ".png";
                Storage::disk('s3')->put($filename_one, file_get_contents($path_one),'public');
            $publicUrl = Storage::disk('s3')->url($filename_one);
            
            $path_two = env('APP_URL') . "sample/testimonial/t-2.png";
            $filename_two = 'public/1/testimonials/'.date('YmdHis') . "107" . ".png";
                Storage::disk('s3')->put($filename_two, file_get_contents($path_two),'public');
            $publicUrl = Storage::disk('s3')->url($filename_two);

            $path_three = env('APP_URL') . "sample/testimonial/t-3.png";
            $filename_three = 'public/1/testimonials/'.date('YmdHis') . "108" . ".png";
                Storage::disk('s3')->put($filename_three, file_get_contents($path_three),'public');
            $publicUrl = Storage::disk('s3')->url($filename_three);

            $path_four = env('APP_URL') . "sample/testimonial/t-7.jpeg";
            $filename_four = 'public/1/testimonials/'.date('YmdHis') . "507" . ".png";
                Storage::disk('s3')->put($filename_four, file_get_contents($path_four),'public');
            $publicUrl = Storage::disk('s3')->url($filename_four);

            $path_five = env('APP_URL') . "sample/testimonial/t-8.jpeg";
            $filename_five = 'public/1/testimonials/'.date('YmdHis') . "508" . ".png";
                Storage::disk('s3')->put($filename_five, file_get_contents($path_five),'public');
            $publicUrl = Storage::disk('s3')->url($filename_five);

            $path_six = env('APP_URL') . "sample/testimonial/t-9.jpeg";
            $filename_six = 'public/1/testimonials/'.date('YmdHis') . "509" . ".png";
                Storage::disk('s3')->put($filename_six, file_get_contents($path_six),'public');
            $publicUrl = Storage::disk('s3')->url($filename_six);

            DB::table('testimonials')->insert([
                [
                    'name' => 'Residential Testimonial',
                    'client_name_one' => 'Rahulbhai patel',
                    'client_name_two' => 'Payalben hirpara',
                    'client_name_three' => 'Kiranbhai prajapati',
                    'description_one' => 'Absolutely, here\'s a concise review for your solar installer:

                    "Extremely satisfied with rooftop solar power plant! The installation was smooth, professional, and on time. Great customer service and excellent quality.',
                    'description_two' => 'When you have an empty roof then why pay for electricity bill? Thank you for end-to-end guidance. Your staff is very professional and friendly. Thanks for making my roof solarized! Superb work by the team.',
                    'description_three' => 'One of the best decisions of my life to go solar! I really appreciate your product quality and workmanship. In the last 6 months, my plant has generated more than 2000 units and counting. I strongly recommend everyone to go solar as soon as possible. Thank you.',
                        'rating_one' => 5,
                        'rating_two' => 5,
                        'rating_three' => 5,
                        'image_one' => $filename_one,
                        'image_two' => $filename_two,
                        'image_three' => $filename_three,
                        'status' => 0,
                        'is_default' => 1,
                        'user_id' => 1,
                        'company_id' => 1,
                ],
                [
                    'name' => 'Commercial Clients',
                    'client_name_one' => 'Mr. Mitesh Dhakesh',
                    'client_name_two' => 'Mr. Chinmay Modi',
                    'client_name_three' => 'Mr. Divyesh Patel',
                    'description_one' => 'Absolutely, heres a concise review for your solar installer:
                    "Extremely satisfied with rooftop solar power plant! The installation was smooth, professional, and on time. Great customer service and excellent quality.',
                    'description_two' => 'When you have empty roof then why to pay for electricity bill? Thank You for end to end guidance. Your staff is very professional and friendly. Thanks for making my roof solarize! Superb Work by Team.',
                    'description_three' => 'One of the best decision of my life to go solar! I really appreciate your product quality and workmanship. In last 6 month, my plant has generated more than 2000 Units and counting. I strongly recommend everyone to go solar as soon as possible. Thank You',
                    'rating_one' => 5,
                    'rating_two' => 5,
                    'rating_three' => 5,
                    'image_one' => $filename_four,
                    'image_two' => $filename_five,
                    'image_three' => $filename_six,
                    'status' => 0,
                    'is_default' => 0,
                    'user_id' => 1,
                    'company_id' => 1,
                ]
            ]);
        }
        if ($userDatas->company_category != 1) {
            $path_one = env('APP_URL') . "sample/testimonial/t-4.jpg";
            $filename_one = date('YmdHis') . "106" . ".jpg";
            Storage::disk('s3')->put("public/1/testimonials/" . $filename_one, file_get_contents($path_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/testimonials/" . $filename_one);

            $path_two = env('APP_URL') . "sample/testimonial/t-5.jpg";
            $filename_two = date('YmdHis') . "107" . ".jpg";
            Storage::disk('s3')->put("public/1/testimonials/" . $filename_two, file_get_contents($path_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/testimonials/" . $filename_two);

            $path_three = env('APP_URL') . "sample/testimonial/t-6.jpg";
            $filename_three = date('YmdHis') . "108" . ".jpg";
            Storage::disk('s3')->put("public/1/testimonials/" . $filename_three, file_get_contents($path_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/testimonials/" . $filename_three);

            DB::table('testimonials')->insert([
                [
                    'name' => 'Sample Testimonials',
                    'client_name_one' => 'Sofie Matos',
                    'client_name_two' => 'Ben Miller',
                    'client_name_three' => 'Joseph Dickens',
                    'description_one' => 'This is a placeholder for sample testimonials that you can customize and add to your Quickest account from the web portal.',
                    'description_two' => 'With Quickest, you can create as many testimonials as you want and easily select them from the dropdown menu while creating an estimate.',
                    'description_three' => 'Adding testimonials to your estimates can help build trust with your customers and increase your chances of closing a deal.',
                    'rating_one' => 5,
                    'rating_two' => 5,
                    'rating_three' => 5,
                    'image_one' => 'public/1/testimonials/' . $filename_one,
                    'image_two' => 'public/1/testimonials/' . $filename_two,
                    'image_three' => 'public/1/testimonials/' . $filename_three,
                    'status' => 0,
                    'is_default' => 1,
                    'user_id' => 1,
                    'company_id' => 1,
                ],
            ]);
        }
    }
}
