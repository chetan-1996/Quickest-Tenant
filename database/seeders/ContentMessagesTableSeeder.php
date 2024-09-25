<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class ContentMessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userDatas = DB::table('users')->where('id', '=', 1)->select(['company_category', 'name', 'email'])->first();
        if ($userDatas->company_category == 1) {
            DB::table('content_messages')->insert([
                ['name' => 'Welcome', 'description' => trim('Hello @leadName ji,
                    *Welcome to ABC Solar Energy Private Limited!*
                    Thank you for considering us for your solar rooftop power plant needs. At Heaven Solar Energy, we are committed to providing sustainable and efficient solar energy solutions tailored to your unique requirements.
                    
                    We are proud to highlight:
                    - *5000+ Rooftop Installations* completed
                    - *30+ Dedicated Staff* members
                    - *24-Hour Service Support*
                    
                    Our expert team ensures seamless installation and exceptional service, helping you harness the power of the sun to reduce energy costs and contribute to a greener future.

                    For more information, please visit our website: http://Xyz.com

                    We look forward to partnering with you on your journey to sustainable energy.

                    Warm regards,
                    @senderName'), 'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_id' => 1, 'company_id' => 1
                ],
                ['name' => 'Follow up 1', 'description' => trim("Hi @leadName
                    
                    It was great visiting your rooftop and discussing your solar needs. I've sent the quotation to you. Please review it and let me know if you have any questions.

                    Thank you!
                    @senderName"), 'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_id' => 1, 'company_id' => 1
                ],
                ['name' => 'Follow up 2', 'description' => trim("Hello @leadName ji,
                
                    Just following up on the quotation I sent a few days ago. Have you had a chance to review it? I'm here to answer any questions you might have.
                    
                    Looking forward to your feedback.

                    @senderName"), 'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_id' => 1, 'company_id' => 1
                ],
                ['name' => 'Follow up 3', 'description' => trim("Hello @leadName ji,
                
                    Hope you’re doing well. I wanted to check in regarding the solar quotation. We’re excited to help you go solar and would love to finalize the details.
                    
                    Please let me know if there's anything you need.
                    
                    Best,
                    @senderName"), 'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_id' => 1, 'company_id' => 1
                ],
                ['name' => 'Follow up 4', 'description' => trim("Hello @leadName ji,

                    I hope all is well. Just a final follow-up on the solar quotation. If you need any adjustments or have any concerns, please let me know. We’re eager to assist you in making the switch to solar energy.

                    Thank you for considering Heaven Solar Energy.

                    Best regards,
                    @senderName"), 'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_id' => 1, 'company_id' => 1
                ]
            ]);
        }
        if($userDatas->company_category != 1) {
            DB::table('content_messages')->insert([
                ['name' => 'Quickest Message', 'description' => trim('HI @leadName,
Hope you are well. I have just installed the Quickest app, it helps to send professional proposals within a few seconds. It helps to increase the sales and manage all my leads from my phone.
Sign up for free here: Quickestimate.co

Thank you
@senderName'), 'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_id' => 1, 'company_id' => 1]
            ]);
        }
    }
}
