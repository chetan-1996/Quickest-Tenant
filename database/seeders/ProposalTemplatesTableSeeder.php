<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class ProposalTemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = DB::table('units')->where([["company_id", "=", 1], ["name", "=", "Kw"]])->orderBy('id', 'ASC')->select("id")->first();
        
        $units1 = DB::table('units')->where([["company_id", "=", 1], ["name", "=", "Nos"]])->orderBy('id', 'ASC')->select("id")->first();

        $userDatas = DB::table('users')->where('id', '=', 1)->select(['company_category', 'name', 'email'])->first();

        $proposalTemplates = DB::table('proposal_templates')->first();
        $newProposalTemplates = $proposalTemplates->replicate();
        $newProposalTemplates->company_id = 1;
        if ($userDatas->company_category > 1) {
            $newProposalTemplates->aboutas_content = trim('<h1><strong><span style="font-size:24px">About&nbsp;${companies.company_name}</span></strong></h1>

<p><span style="font-size:11pt"><span style="font-family:Arial"><span style="color:#000000">Welcome to our company profile! This is a sample description that you can change from the template settings. To do so, simply log in to the web portal and navigate to the template settings section. If you&#39;re new here, we recommend checking out the welcome email that we sent you, which includes a video tutorial to help you get started.</span></span></span></p>

<p><span style="font-size:11pt"><span style="font-family:Arial"><span style="color:#000000">You can customize your profile by setting up the theme color and adding photos of your products, items, and customer testimonials, as well as terms and conditions - all of which you can do with just a one-time entry from the web portal. Once you&#39;re all set up, you&#39;ll be able to generate quick estimates within seconds using your mobile phone.</span></span></span></p>

<p><span style="font-size:11pt"><span style="font-family:Arial"><span style="color:#000000">Thank you for choosing Quickest. if you need any kind of support you can always reach out to us on<strong> contact@quickestimate.co</strong></span></span></span></p>');
                $newProposalTemplates->est_customer_notes_details = trim('Payment 100% advance
You can edit the notes');
            }
            if ($userDatas->company_category == 1) {
                $newProposalTemplates->est_customer_notes_details = trim('Please Refer Detailed Terms & Condition for payment & Warranty');
                $newProposalTemplates->aboutas_content = trim('<h1><strong><span style="font-size:24px">About&nbsp;${companies.company_name}</span></strong></h1>

<p><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:14.0pt">Vision</span></strong><strong> </strong><br />
<span style="font-size:12.0pt">&quot;Empowering a Sustainable Future with Clean Solar Energy&quot;</span></span></span></p>

<p><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:14.0pt">Mission </span></strong><br />
<span style="font-size:12.0pt">&nbsp;&quot; our mission is to lead the transition to a sustainable and renewable energy future through the widespread adoption of solar power. We are committed to delivering innovative, reliable, and affordable solar solutions that not only reduce our carbon footprint but also provide economic and environmental benefits to our customers and communities. With unwavering dedication to quality, innovation, and customer satisfaction, we aim to make solar energy accessible to all, contributing to a greener planet and a brighter tomorrow.&quot;</span></span></span></p>
');
        }//proposal_template_cover_photos
        $newProposalTemplates->save();

        $lastId = $newProposalTemplates->id;
        $path = 'public/document/' . 1;
        if (!Storage::exists($path)) {
            Storage::makeDirectory($path);
        }

        if ($userDatas->company_category == 1) {
            $cover_one = env('APP_URL') . "sample/cover/1.png";
            $filename_cover_one = date('YmdHis') . "106cvr" . ".png";
            Storage::disk('s3')->put("public/1/templates/cover/" . $filename_cover_one, file_get_contents($cover_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/cover/" . $filename_cover_one);

            $cover_two = env('APP_URL') . "sample/cover/2.png";
            $filename_cover_two = date('YmdHis') . "107cvr" . ".png";
            Storage::disk('s3')->put("public/1/templates/cover/" . $filename_cover_two, file_get_contents($cover_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/cover/" . $filename_cover_two);

            $cover_three = env('APP_URL') . "sample/cover/3.png";
            $filename_cover_three = date('YmdHis') . "108cvr" . ".png";
            Storage::disk('s3')->put("public/1/templates/cover/" . $filename_cover_three, file_get_contents($cover_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/cover/" . $filename_cover_three);

            $cover_four = env('APP_URL') . "sample/cover/4.png";
            $filename_cover_four = date('YmdHis') . "109cvr" . ".png";
            Storage::disk('s3')->put("public/1/templates/cover/" . $filename_cover_four, file_get_contents($cover_four),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/cover/" . $filename_cover_four);

            $cover_five = env('APP_URL') . "sample/cover/5.png";
            $filename_cover_five = date('YmdHis') . "110cvr" . ".png";
            Storage::disk('s3')->put("public/1/templates/cover/" . $filename_cover_five, file_get_contents($cover_five),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/cover/" . $filename_cover_five);

            DB::table('proposal_template_cover_photos')->insert([
                ['proposal_template_id' => $lastId, 'image_icon' => 'public/1/templates/cover/' . $filename_cover_one, 'cover_flg' => 1, 'user_id' => 1, 'company_id' => 1],
                ['proposal_template_id' => $lastId, 'image_icon' => 'public/1/templates/cover/' . $filename_cover_two, 'cover_flg' => 0, 'user_id' => 1, 'company_id' => 1],
                ['proposal_template_id' => $lastId, 'image_icon' => 'public/1/templates/cover/' . $filename_cover_three, 'cover_flg' => 0, 'user_id' => 1, 'company_id' => 1],
                ['proposal_template_id' => $lastId, 'image_icon' => 'public/1/templates/cover/' . $filename_cover_four, 'cover_flg' => 0, 'user_id' => 1, 'company_id' => 1],
                ['proposal_template_id' => $lastId, 'image_icon' => 'public/1/templates/cover/' . $filename_cover_five, 'cover_flg' => 0, 'user_id' => 1, 'company_id' => 1]
            ]);

            $aboutus_one = env('APP_URL') . "sample/about-us/1.jpg";
            $filename_aboutus_one = date('YmdHis') . "106aboutus" . ".jpg";
            Storage::disk('s3')->put("public/1/templates/aboutus/" . $filename_aboutus_one, file_get_contents($aboutus_one));
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/aboutus/" . $filename_aboutus_one);

            $aboutus_two = env('APP_URL') . "sample/about-us/2.jpg";
            $filename_aboutus_two = date('YmdHis') . "107aboutus" . ".jpg";
            Storage::disk('s3')->put("public/1/templates/aboutus/" . $filename_aboutus_two, file_get_contents($aboutus_two),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/aboutus/" . $filename_aboutus_two);


            $aboutus_three = env('APP_URL') . "sample/about-us/3.jpg";
            $filename_aboutus_three = date('YmdHis') . "108aboutus" . ".jpg";
            Storage::disk('s3')->put("public/1/templates/aboutus/" . $filename_aboutus_three, file_get_contents($aboutus_three),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/aboutus/" . $filename_aboutus_three);

            $aboutus_four = env('APP_URL') . "sample/about-us/4.jpg";
            $filename_aboutus_four = date('YmdHis') . "109aboutus" . ".jpg";
            Storage::disk('s3')->put("public/1/templates/aboutus/" . $filename_aboutus_four, file_get_contents($aboutus_four),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/aboutus/" . $filename_aboutus_four);

            $aboutus_five = env('APP_URL') . "sample/about-us/5.jpg";
            $filename_aboutus_five = date('YmdHis') . "110aboutus" . ".jpg";
            Storage::disk('s3')->put("public/1/templates/aboutus/" . $filename_aboutus_five, file_get_contents($aboutus_five),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/aboutus/" . $filename_aboutus_five);

            DB::table('proposal_template_aboutus_photos')->insert([
                ['proposal_template_id' => $lastId, 'image_icon' => 'public/1/templates/aboutus/' . $filename_aboutus_one, 'about_flg' => 1, 'user_id' => 1, 'company_id' => 1],
                ['proposal_template_id' => $lastId, 'image_icon' => 'public/1/templates/aboutus/' . $filename_aboutus_two, 'about_flg' => 0, 'user_id' => 1, 'company_id' => 1],
                ['proposal_template_id' => $lastId, 'image_icon' => 'public/1/templates/aboutus/' . $filename_aboutus_three, 'about_flg' => 0, 'user_id' => 1, 'company_id' => 1],
                ['proposal_template_id' => $lastId, 'image_icon' => 'public/1/templates/aboutus/' . $filename_aboutus_four, 'about_flg' => 0, 'user_id' => 1, 'company_id' => 1],
                ['proposal_template_id' => $lastId, 'image_icon' => 'public/1/templates/aboutus/' . $filename_aboutus_five, 'about_flg' => 0, 'user_id' => 1, 'company_id' => 1]
            ]);

        }

        if ($userDatas->company_category != 1) {
            $cover_one = env('APP_URL') . "sample/cover/6.jpg";
            $filename_cover_one = date('YmdHis') . "150defcvr" . ".jpg";
            Storage::disk('s3')->put("public/1/templates/cover/" . $filename_cover_one, file_get_contents($cover_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/cover/" . $filename_cover_one);

            DB::table('proposal_template_cover_photos')->insert([
                ['proposal_template_id' => $lastId, 'image_icon' => "public/1/templates/cover/" . $filename_cover_one, 'cover_flg' => 1, 'user_id' => 1, 'company_id' => 1]
            ]);

            $aboutus_one = env('APP_URL') . "sample/about-us/6.jpg";
            $filename_aboutus_one = date('YmdHis') . "151defaboutus" . ".jpg";
            Storage::disk('s3')->put("public/1/templates/aboutus/" . $filename_aboutus_one, file_get_contents($aboutus_one),'public');
            $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/aboutus/" . $filename_aboutus_one);

            DB::table('proposal_template_aboutus_photos')->insert([
                ['proposal_template_id' => $lastId, 'image_icon' => 'public/1/templates/aboutus/' . $filename_aboutus_one, 'about_flg' => 1, 'user_id' => 1, 'company_id' => 1]
            ]);
        }

        $path_ones = env('APP_URL') . "sample/signature.png";
        $filename_onesa = date('YmdHis') . "1065474" . ".png";
        Storage::disk('s3')->put("public/1/templates/signature/" . $filename_onesa, file_get_contents($path_ones),'public');
        $publicUrlOriginal = Storage::disk('s3')->url("public/1/templates/signature/" . $filename_onesa);
        ProposalTemplates::where('company_id', 1)->update(array('term_condition_id' => $term_condition_id, 'cover_img' => "public/1/templates/cover/" . $filename_cover_one, 'aboutas_img' => "public/1/templates/aboutus/" . $filename_aboutus_one, 'est_signature_img' => "public/1/templates/signature/" . $filename_onesa));
    }
}
