<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\MpdfService;
use App\Models\admin\ViewUserData;
use App\Models\ProposalTemplateAboutusPhoto;
use App\Models\ProposalTemplateCoverPhoto;
use App\Models\TermCondition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\ProposalTemplates;
use Auth;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Spatie\PdfToImage\Pdf;
use Image;
//use Imagick;


class ProposalController extends Controller
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $segment = null;
    public function __construct()
    {
        if (request()->segment(2) != "generate-link") { //request()->segment(2) &&
            $this->middleware(function ($request, $next) {
                $this->logged_user = \Illuminate\Support\Facades\Auth::user();
                $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
                $this->segment = $request->segment(1);
                return $next($request);
            });
        }
    }

    public function index(Request $request){
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $data = ProposalTemplates::select(['id','template_name','company_id'])->where('company_id',$company_id)->paginate(10);
        $segment = $this->segment;
        return view('app.template.proposal.index',compact('data', 'segment'));
    }

    public function create(){

        $user = Auth::user();
        $company_id = ($user->company_id)? $user->company_id : $user->id;
        $proposal_template = ProposalTemplates::where('company_id',$company_id)->first();

        return view('template.proposal.create',compact('proposal_template'));
    }

    public function newCreate($id=0){

        $user = Auth::user();
        $company_id = ($user->company_id)? $user->company_id : $user->id;
        $proposal_template = ProposalTemplates::where(['company_id' => $company_id, "id" => $id])->first();
        $termConditionDatas = TermCondition::where([["status","=",0],["company_id","=",$company_id]])->select('*')->get();
        $proposal_template_cover_photos = ProposalTemplateCoverPhoto::where(["proposal_template_id" => $id])->get();
        $proposal_template_aboutus_photos = ProposalTemplateAboutusPhoto::where(["proposal_template_id" => $id])->get();
        return view('template.proposal.new-create',compact('proposal_template','termConditionDatas','proposal_template_cover_photos','proposal_template_aboutus_photos'));
    }

    public function pdfPreview(){
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $proposal_template = ProposalTemplates::where('company_id',$company_id)->first();
        $company_data = ViewUserData::where("id", $company_id)->orderBy('id', 'ASC')->get()->first();
        $term_condition_data = TermCondition::where("id", $proposal_template->term_condition_id)->orderBy('id', 'ASC')->get()->first();


        if($proposal_template->new_pdf_flag==1) {
            $header = '<!--mpdf
            <htmlpageheader name="letterheader">
                <table width="100%" style=" font-family: sans-serif;">
                    <tr>
                        <td width="50%" style="color:#0000BB; ">
                            <span style="font-weight: bold; font-size: 14pt;">Acme Trading Co.</span><br />
                            123 Anystreet<br />Your City<br />GD12 4LP<br />
                            <span style="font-size: 15pt;">Phone : </span> +91 1777 123 567
                         </td>
                        <td width="50%" style="text-align: right; vertical-align: top;">
                            Invoice No.<br />
                            <span style="font-weight: bold; font-size: 12pt;">0012345</span>
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 1cm; text-align: right; font-family: sans-serif;">
                {DATE j-m-Y}
                </div>
            </htmlpageheader>

            <htmlpagefooter name="letterfooter2">
                <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; font-family: sans-serif; ">
                    Page {PAGENO} of {nbpg}
                </div>
            </htmlpagefooter>
        mpdf-->

        <style>
            @page {
                margin-top: 2.5cm;
                margin-bottom: 2.5cm;
                margin-left: 2cm;
                margin-right: 2cm;
                footer: html_letterfooter2;
                background-color: pink;
            }

            @page :first {
                margin-top: 8cm;
                margin-bottom: 4cm;
                header: html_letterheader;
                footer: _blank;
                resetpagenum: 1;
                background-color: lightblue;
            }

            @page letterhead {
                margin-top: 2.5cm;
                margin-bottom: 2.5cm;
                margin-left: 2cm;
                margin-right: 2cm;
                footer: html_letterfooter2;
                background-color: pink;
            }

            @page letterhead :first {
                margin-top: 8cm;
                margin-bottom: 4cm;
                header: html_letterheader;
                footer: _blank;
                resetpagenum: 1;
                background-color: lightblue;
            }
        </style>';

           /* $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'tempDir' => storage_path('tempdir'),
            ]);*/


            $mpdf = MpdfService::createMpdfInstance();



            // Define the font directory
    //        $fontDir = __DIR__ . '/mpdf/ttfonts/';


    //        $mpdf->SetCompression(false);
            $mpdf->showImageErrors = true;
            $mpdf->debug = true;
            $mpdf->curlAllowUnsafeSslRequests = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            $mpdf->allow_charset_conversion = true;
    //        $mpdf->shrink_tables_to_fit = 1;

    //        $mpdf->SetWatermarkText('Heaven Design Pvt. Ltd.');
    //        $mpdf->showWatermarkText = true;
    //        $mpdf->watermarkTextAlpha = 0.1;
    //        $mpdf->watermarkImageAlpha = 0.5;

            $mpdf->baseScript = 1;
            $mpdf->autoVietnamese = true;
            $mpdf->autoArabic = true;

//            $image_file =public_path(Storage::url($proposal_template->header_logo));
            $image_file = Storage::disk('s3')->url($proposal_template->header_logo);
            $header = '<div style="text-align: right; font-weight: bold;border-bottom: 1px solid #fff;margin-right:' . $proposal_template->header_logo_left . 'px;padding-top:' . $proposal_template->header_logo_top . 'px;"><img src="' . $image_file  . '" width="' . $proposal_template->header_logo_size . '"/></div>';
            // Define the Headers before writing anything so they appear on the first page


            $facebook_url = '';
            $instagram_url = '';
            $linkedin_url = '';
            $twitter_url = '';
            $width = 0;
            if ($company_data->facebook_url) {
                $facebook_url = '<td><a href="' . $company_data->facebook_url . '"><img src="' . public_path(Storage::url('facebook.png')) . '" width="30"></a></td>';
            }
            if ($company_data->instagram_url) {
                $instagram_url = '<td><a href="' . $company_data->instagram_url . '"><img src="' . public_path(Storage::url('instagram.png')) . '" width="30"></a></td>';
            }
            if ($company_data->linkedin_url) {
                $linkedin_url = '<td><a href="' . $company_data->linkedin_url . '"><img src="' . public_path(Storage::url('linkedin.png')) . '" width="30"></a></td>';
            }
            if ($company_data->twitter_url) {
                $twitter_url = '<td><a href="' . $company_data->twitter_url . '"><img src="' . public_path(Storage::url('twitter.png')) . '" width="30"></a></td>';
            }

            $footer = '
        <table width="100%" style="vertical-align: middle; font-family: Arial, Helvetica, serif;
            font-size: 12pt; color: #fff; font-style: normal;background: ' . $proposal_template->theme_footer_color . ';">
            <tr>
                <td width="47%"><a href="' . $company_data->website_link . '" target="_blank" style="text-decoration: none !important;color:#fff;">' . $company_data->company_name . '</a></td>
                <td width="6%" align="center">{PAGENO}/{nbpg}</td>
                <td width="47%" style="text-align: right;">
                    <table>
                        <tr>
                            ' . $facebook_url . $instagram_url . $linkedin_url . $twitter_url . '
                        </tr>
                    </table>
                </td>
            </tr>
        </table>';

//        $mpdf->SetHTMLHeader();
//        $mpdf->SetHTMLHeader();
//        $mpdf->SetHTMLFooter();
            $mpdf->AddPage('P', '', '', '', '', 0, 0, 0, -1, 0, 0);
            $coverHtml1 = view('pdf-setting.cover-page-new', compact('proposal_template'))->render();
            $mpdf->WriteHTML($coverHtml1);

            $mpdf->SetHTMLHeader($header);
//        $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);
            $aboutHtml = view('pdf-setting.about-page-new', compact('proposal_template'))->render();
            $mpdf->WriteHTML($aboutHtml);

            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            $productHtml = view('pdf-setting.product-page-new', compact('proposal_template'))->render();
            $mpdf->WriteHTML($productHtml);


            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            $estHtml = view('pdf-setting.estimate-page-new', compact('proposal_template'))->render();
            $mpdf->writeHTML($estHtml);

            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);
            $termsHtml = view('pdf-setting.term-and-condition-page-new', compact('proposal_template', 'term_condition_data'))->render();
            $mpdf->writeHTML($termsHtml);
//        $mpdf->shrink_tables_to_fit = 1;

            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            $testiHtml = view('pdf-setting.testimonial-page-new', compact('proposal_template'))->render();
            $mpdf->writeHTML($testiHtml);

            //Seven page
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            $thanksHtml = view('pdf-setting.thank-you-page-new', compact('company_data', 'proposal_template'))->render();
            $mpdf->writeHTML($thanksHtml);

            $mpdf->Output('quickest-sample-pdf.pdf', 'I');

            $mpdf->Output(public_path('storage/document/' . $company_id . '/proposal-sample.pdf'), 'F');
            $pdfUrl = storage_path('app/public/document/' . $company_id . '/proposal-sample.pdf')."[0]";
            $outputImage = storage_path('app/public/document/' . $company_id . '/proposal-sample.jpeg');

            $command = "convert -density 300 \"$pdfUrl\" \"$outputImage\"";
            exec($command, $output, $returnStatus);

            $old_path = url(Storage::url('public/document/' . $company_id . '/proposal-sample.jpeg'));
            $path = 'public/' . $company_id . '/documents/proposal-sample.jpeg';
            Storage::disk('s3')->put($path, file_get_contents($old_path),'public');
            Storage::disk('s3')->url($path);

            unlink(storage_path('app/public/document/' . $company_id . '/proposal-sample.jpeg'));
        }

        if($proposal_template->new_pdf_flag==0) {

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf::setHeaderCallback(function ($pdf) use ($proposal_template) {
//            if ($pdf->PageNo() > 1) {
                $image_file = Storage::disk('s3')->url($proposal_template->header_logo);
//                $image_file = public_path(Storage::url($proposal_template->header_logo));
                $pdf->Image($image_file, $proposal_template->header_logo_left, $proposal_template->header_logo_top, $proposal_template->header_logo_size, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                $pdf->SetY(7);
                // Set font
                $pdf->SetFont('helvetica', 'B', 20);
                $pdf->setPageMark();
                /* $pdf->SetAlpha(0.1);
                 $img_file = public_path('storage/logo.png');
                 $pdf->Image($img_file, 50, 135, 100, '', 0, 0, '', false, 300, '', false, false, false);*/

                // Title
//                $pdf->Cell(0, 15, 'Heaven Designs Pvt Ltd.', 0, false, 'C', 0, '', 1, false, 'M', 'M');
//            $pdf->line(1, 20, 209, 20, array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'solid' => 1, 'color' => "#dee2e6"));
//            }
            });

            // Custom Footer
            $pdf::setFooterCallback(function ($pdf) use ($proposal_template, $company_data) {
                $facebook_url = '';
                $instagram_url = '';
                $linkedin_url = '';
                $twitter_url = '';
                $width = 0;
                if ($company_data->facebook_url) {
                    $width += 31.25;
                    $facebook_url = '<td width="30"><a href="' . $company_data->facebook_url . '"><img src="' . Storage::url('facebook.png') . '" height="40"></a></td>';
                }
                if ($company_data->instagram_url) {
                    $width += 31.25;
                    $instagram_url = '<td width="30"><a href="' . $company_data->instagram_url . '"><img src="' . Storage::url('instagram.png') . '" height="40"></a></td>';
                }
                if ($company_data->linkedin_url) {
                    $width += 31.25;
                    $linkedin_url = '<td width="30"><a href="' . $company_data->linkedin_url . '"><img src="' . Storage::url('linkedin.png') . '" height="40"></a></td>';
                }
                if ($company_data->twitter_url) {
                    $width += 31.25;
                    $twitter_url = '<td width="30"><a href="' . $company_data->twitter_url . '"><img src="' . Storage::url('twitter.png') . '" height="40"></a></td>';
                }
                if ($facebook_url == '' && $instagram_url == '' && $linkedin_url == '' && $twitter_url == '') {
                    $facebook_url = '<td width="30"></td>';
                }

                $first_td_width = 470 + (125 - $width);

//            if ($pdf->PageNo() > 1) {
                $footer = '<table><tr style="background-color:' . $proposal_template->theme_footer_color . ';"><td width="' . $first_td_width . '"><table cellpadding="6"><tr><td><a href="' . $company_data->website_link . '" target="_blank" style="text-decoration: none;color:#fff;">' . $company_data->company_name . '</a></td></tr></table></td><td style="text-align: right;color:#fff;"  width="125" align="right"><table border="0" style="text-align: right;" align="right"><tr>' . $facebook_url . $instagram_url . $linkedin_url . $twitter_url . '</tr></table></td></tr></table>';
                $pdf->SetY(-9.6);

                /* }else{

                     $footer = '<table cellpadding="6"><tr style="background-color:' . $proposal_template->theme_color_two . ';"><td><a href="'.$company_data->website_link.'" target="_blank" style="text-decoration: none;color:#fff;">'.$company_data->company_name.'</a></td><td style="text-align: right;color:#fff;">Social Media Link</td></tr></table>';
                     $pdf->SetY(-9.6);
                 }*/
                $pdf->writeHTML($footer, true, false, true, false, '');
            });

            $pdf::SetAuthor('Chetan Moradiya');
            $pdf::SetTitle('Quickest | PDf');
            $pdf::SetSubject('Quick Estimate');

            //First page

            $pdf::SetMargins(0, 0, 0, true);
            $pdf::SetFontSubsetting(false);
            $pdf::SetFontSize('12px');
            $pdf::SetFont('helvetica', 'R', 11);
            $pdf::SetAutoPageBreak(false);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf-setting.cover-page', compact('proposal_template'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');

            //Second page
            $pdf::startPageGroup();
            $pdf::SetMargins(7, $proposal_template->page_top_margin, 7, false);
            $pdf::SetFontSubsetting(true);
            $pdf::SetFont('helvetica', 'R', 11);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf-setting.about-page', compact('proposal_template'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');

            //Third page
            $pdf::startPageGroup();
            $pdf::SetMargins(7, $proposal_template->page_top_margin, 7, false);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::SetFont('helvetica', 'R', 11);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf-setting.product-page', compact('proposal_template', 'company_data'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');

            //Fourth page
            $pdf::startPageGroup();
            $pdf::SetMargins(7, $proposal_template->page_top_margin, 7, false);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::SetFont('helvetica', 'R', 11);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf-setting.estimate-page', compact('proposal_template',));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');

            //Fifth page
            $pdf::startPageGroup();
            $pdf::SetMargins(7, $proposal_template->page_top_margin, 7, false);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::SetFont('helvetica', 'R', 10);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf-setting.term-and-condition-page', compact('proposal_template', 'term_condition_data'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');

            //Sixth page
            $pdf::startPageGroup();
            $pdf::SetMargins(7, $proposal_template->page_top_margin, 7, false);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::SetFont('helvetica', 'R', 11);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf-setting.testimonial-page', compact('proposal_template'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');

            //Seven page
            $pdf::startPageGroup();
            $pdf::SetMargins(7, 15.5, 7, false);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::SetFont('helvetica', 'R', 11);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf-setting.thank-you-page', compact('proposal_template', 'company_data'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');

            $pdf::Output('quickest-sample-pdf.pdf', 'I');
            $pdf::Output(public_path('storage/document/' . $company_id . '/proposal-sample.pdf'), 'F');
            $pdfUrl = storage_path('app/public/document/' . $company_id . '/proposal-sample.pdf')."[0]";
            $outputImage = storage_path('app/public/document/' . $company_id . '/proposal-sample.jpeg');

            $command = "convert -density 300 \"$pdfUrl\" \"$outputImage\"";
            exec($command, $output, $returnStatus);

            $old_path = url(Storage::url('public/document/' . $company_id . '/proposal-sample.jpeg'));
            $path = 'public/' . $company_id . '/documents/proposal-sample.jpeg';
            Storage::disk('s3')->put($path, file_get_contents($old_path),'public');
            Storage::disk('s3')->url($path);

            unlink(storage_path('app/public/document/' . $company_id . '/proposal-sample.jpeg'));
        }
    }

    public function show(){

    }

    public function edit(){

    }

    public function store(Request $request){
        $input = $request->all();
        $input['cover_page_flg'] = 0;
        if ($request->has('cover_page_flg')) {
            $input['cover_page_flg'] = 1;
        }
        $input['about_us_flg'] = 0;
        if ($request->has('about_us_flg')) {
            $input['about_us_flg'] = 1;
        }

        $input['thank_you_flg'] = 0;
        if ($request->has('thank_you_flg')) {
            $input['thank_you_flg'] = 1;
        }
        $input['photo_position_flg'] = 0;
        if ($request->has('photo_position_flg')) {
            $input['photo_position_flg'] = 1;
        }
        $user = Auth::user();
        $company_id = ($user->company_id)? $user->company_id : $user->id;

//        $id = Crypt::decrypt($input['id']);
        $validator = Validator::make($input, [
//            'template_name' => 'required',
//            'color_picker_one' => 'required',
//            'color_picker_two' => 'required',
//            'logo_dimension_one' => 'required',
//            'logo_dimension_img' => 'required',
//            'cover_title' => 'required',
//            'cover_content' => 'required',
//            'cover_footer_one' => 'required',
//            'cover_footer_two' => 'required',
//            'aboutas_title' => 'required',
//            'aboutas_content' => 'required',
//            'testimonials_title' => 'required',
//            'testimonials_content' => 'required',
////            'item_table_no' => 'required',
//            'item_table_item' => 'required',
////            'item_table_hsn' => 'required',
//            'item_table_qty' => 'required',
//            'item_table_rate' => 'required',
////            'item_table_discount' => 'required',
////            'item_table_cgst' => 'required',
////            'item_table_sgst' => 'required',
////            'item_table_igst' => 'required',
//            'item_table_total' => 'required',
//            'est_bank_label' => 'required',
//            'est_bank_details' => 'required',
//            'est_term_condition_lable' => 'required',
//            'est_term_condition_details' => 'required',
//            'est_signature_lable' => 'required',
//            'header_logo' => 'required|image|mimes:jpeg,png,jpg|max:1024',
//            'cover_img' => 'required|image|mimes:jpeg,png,jpg|max:1024',
//            'aboutas_img' => 'required|image|mimes:jpeg,png,jpg|max:1024',
//            'est_signature_img' => 'required|image|mimes:jpeg,png,jpg|max:1024',

        ]);
        if ($request->file('header_logo')) {
            /*$path = $request->file('header_logo')->store('public/template');
            $input['header_logo'] = $path;*/

            $path = Storage::disk('s3')->put('public/'.$company_id.'/templates/logo/', $request->file('header_logo'),'public');
            $input['header_logo'] = 'public/'.$company_id.'/templates/logo/'.basename(Storage::disk('s3')->url($path));
        }

        if ($request->file('cover_img')) {
            $path = $request->file('cover_img')->store('public/template');
            $input['cover_img'] = $path;
            ProposalTemplateCoverPhoto::where('proposal_template_id', $input['id'])->update(["cover_flg" => 0]);
            ProposalTemplateCoverPhoto::create(["proposal_template_id" =>$input['id'],"image_icon" => $input['cover_img'], "cover_flg" => 1, "user_id" => $company_id,"company_id" => $company_id]);
        }else{
            $input['cover_img'] = $input['imgbackground'];
            ProposalTemplateCoverPhoto::where('proposal_template_id', $input['id'])->update(["cover_flg" => 0]);
            ProposalTemplateCoverPhoto::where('image_icon', $input['imgbackground'])->update(["cover_flg" => 1]);
        }

        if ($request->file('aboutas_img')) {
            $path = $request->file('aboutas_img')->store('public/template');
            $input['aboutas_img'] = $path;

            ProposalTemplateAboutusPhoto::where('proposal_template_id', $input['id'])->update(["about_flg" => 0]);
            ProposalTemplateAboutusPhoto::create(["proposal_template_id" =>$input['id'],"image_icon" => $input['aboutas_img'], "about_flg" => 1, "user_id" => $company_id,"company_id" => $company_id]);
        }else{
            $input['aboutas_img'] = $input['image_aboutus'];
            ProposalTemplateAboutusPhoto::where('proposal_template_id', $input['id'])->update(["about_flg" => 0]);
            ProposalTemplateAboutusPhoto::where('image_icon', $input['aboutas_img'])->update(["about_flg" => 1]);
        }

        /*if ($request->file('est_signature_img')) {
            $path = $request->file('est_signature_img')->store('public/template');
            $input['est_signature_img'] = $path;
        }
        if ($request->file('est_signature_img')) {
            $path = $request->file('est_signature_img')->store('public/template');
            $input['est_signature_img'] = $path;
        }*/

        /*if ($request->h_image_three) {
            $folderPath = public_path('storage/template/');
            $image_parts = explode(";base64,", $request->h_image_three);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $ext = explode(";", explode("/", $request->h_image_three)[1])[0];
//            $imageName = uniqid() . '-2' . '.' . $ext;
//            $imageFullPath = $folderPath . $imageName;

            $decodedImage = $image_base64;
            $imageNames = Str::uuid() . '-2.' . $ext;
            $imagePath = public_path('storage/template/') . $imageNames;

            // Use intervention/image package to create an image instance and save it to the desired path
            $image = Image::make($decodedImage);

            // Save the resized image to the specified path
            $image->save($imagePath);

            $input['est_signature_img'] = 'public/template/' . $imageNames;
        }

        if ($request->h_image_two) {
            $folderPath = public_path('storage/template/');
            $image_parts = explode(";base64,", $request->h_image_two);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $ext = explode(";", explode("/", $request->h_image_two)[1])[0];
//            $imageName = uniqid() . '-2' . '.' . $ext;
//            $imageFullPath = $folderPath . $imageName;

            $decodedImage = $image_base64;
            $imageNames = Str::uuid() . '-2.' . $ext;
            $imagePath = public_path('storage/template/') . $imageNames;

            // Use intervention/image package to create an image instance and save it to the desired path
            $image = Image::make($decodedImage);

            // Save the resized image to the specified path
            $image->save($imagePath);

            $input['thank_you_img'] = 'public/template/' . $imageNames;
        }*/

        if ($request->h_image_three) {
            $ext = explode(";", explode("/", $request->h_image_three)[1])[0];
            $imageName = Str::uuid() . '-2' . '.' . $ext;


            //S3 bucket
            $imageData = $request->input('h_image_three');
            // Decode the base64 image data
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

            // Upload the image to S3
            Storage::disk('s3')->put('public/'.$company_id.'/templates/signature/'.$imageName, $imageData,'public');

            // Get the S3 URL of the uploaded image
            $s3Url = Storage::disk('s3')->url('public/'.$company_id.'/templates/signature/'.$imageName);
            $input['est_signature_img'] = 'public/'.$company_id.'/templates/signature/'.$imageName;
//            $input['est_signature_img'] = 'public/template/' . $imageNames;
        }

        if ($request->h_image_two) {
            $ext = explode(";", explode("/", $request->h_image_two)[1])[0];
            $imageName = Str::uuid() . '-2' . '.' . $ext;


            //S3 bucket
            $imageData = $request->input('h_image_two');
            // Decode the base64 image data
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

            // Upload the image to S3
            Storage::disk('s3')->put('public/'.$company_id.'/templates/thankyou/'.$imageName, $imageData,'public');

            // Get the S3 URL of the uploaded image
            $s3Url = Storage::disk('s3')->url('public/'.$company_id.'/templates/thankyou/'.$imageName);
            $input['thank_you_img'] = 'public/'.$company_id.'/templates/thankyou/'.$imageName;
//            $input['thank_you_img'] = 'public/template/' . $imageNames;
        }

       /* if ($request->file('thank_you_img')) {
            $path = $request->file('thank_you_img')->store('public/template');
            $input['thank_you_img'] = $path;
        }*/

        /*if (empty($input['item_number_flag'])) {
            $input['item_number_flag'] = 0;
        }else{
            $input['item_number_flag'] = 1;
        }

        if (empty($input['item_hsn_flag'])) {
            $input['item_hsn_flag'] = 0;
        }else{
            $input['item_hsn_flag'] = 1;
        }

        if (empty($input['item_discount_flag'])) {
            $input['item_discount_flag'] = 0;
        }else{
            $input['item_discount_flag'] = 1;
        }

        if (empty($input['item_cgst_flag'])) {
            $input['item_cgst_flag'] = 0;
        }else{
            $input['item_cgst_flag'] = 1;
        }

        if (empty($input['item_sgst_flag'])) {
            $input['item_sgst_flag'] = 0;
        }else{
            $input['item_sgst_flag'] = 1;
        }
        if (empty($input['item_igst_flag'])) {
            $input['item_igst_flag'] = 0;
        }else{
            $input['item_igst_flag'] = 1;
        }*/

        ProposalTemplates::updateOrCreate(['company_id' => $company_id],$input);
        return response()->json(['success' => 'Successfully saved...'], 201);
//        $unit = ProposalTemplates::create($input);
//        echo "<pre>";
//        print_r($request->file('est_signature_img'));
//        print_r($input);
    }

    public function update(){

    }

    public function destroy(){

    }

    /*public function deleteSignImage(Request $request){
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }
//            $id = [];
//            foreach (explode(",", $request->id) as $value) {
//                $id[] = Crypt::decrypt($value);
//            }
            Storage::disk('public')->delete($input['image_path']);
            ProposalTemplates::where('id', $input['id'])->update(["est_signature_img" => null]);

            return response()->json(['success' => 'Signature Deleted!'], 201);
        }
    }*/

    public function deleteSignImage(Request $request){
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }
//            $id = [];
//            foreach (explode(",", $request->id) as $value) {
//                $id[] = Crypt::decrypt($value);
//            }
//            Storage::disk('public')->delete('path-of-file');
            if ($input['image_path']) {
                Storage::disk('s3')->delete($input['image_path']);
            }
            ProposalTemplates::where('id', $input['id'])->update(["est_signature_img" => null]);

            return response()->json(['success' => 'Signature Deleted!'], 201);
        }
    }

    /*public function deleteAboutusImage(Request $request){
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }
//            $id = [];
//            foreach (explode(",", $request->id) as $value) {
//                $id[] = Crypt::decrypt($value);
//            }
            Storage::disk('public')->delete($input['image_path']);
            proposalTemplateAboutusPhoto::where('id', $input['id'])->delete();

            return response()->json(['success' => 'About us Image Deleted!'], 201);
        }
    }*/

    public function deleteAboutusImage(Request $request){ //s3 code
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }
//            $id = [];
//            foreach (explode(",", $request->id) as $value) {
//                $id[] = Crypt::decrypt($value);
//            }
//            Storage::disk('public')->delete('path-of-file');
            if ($input['image_path']) {
//                Storage::disk('s3')->delete($input['image_path']);
                Storage::disk('public')->delete($input['image_path']);
            }
            proposalTemplateAboutusPhoto::where('id', $input['id'])->delete();

            return response()->json(['success' => 'About us Image Deleted!'], 201);
        }
    }

    /*public function deleteCoverImage(Request $request){
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }
//            $id = [];
//            foreach (explode(",", $request->id) as $value) {
//                $id[] = Crypt::decrypt($value);
//            }
            Storage::disk('public')->delete($input['image_path']);
            ProposalTemplateCoverPhoto::where('id', $input['id'])->delete();

            return response()->json(['success' => 'Cover Image Deleted!'], 201);
        }
    }*/

    public function deleteCoverImage(Request $request){ //s3 code
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }
//            $id = [];
//            foreach (explode(",", $request->id) as $value) {
//                $id[] = Crypt::decrypt($value);
//            }
//            Storage::disk('public')->delete('path-of-file');
            if ($input['image_path']) {
                Storage::disk('s3')->delete($input['image_path']);
            }
            ProposalTemplateCoverPhoto::where('id', $input['id'])->delete();

            return response()->json(['success' => 'Cover Image Deleted!'], 201);
        }
    }

    /*public function uploadCropCoverImage(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();
        $company_id = ($user->company_id)? $user->company_id : $user->id;

        $folderPath = public_path('storage/template/');
        $image_parts = explode(";base64,", $request->image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $ext = explode(";", explode("/", $request->image)[1])[0];
        $imageName = uniqid() . '.'.$ext;
        $imageFullPath = $folderPath.$imageName;
        file_put_contents($imageFullPath, $image_base64);

        //  $saveFile = new Picture;
        //  $saveFile->name = $folderPath.$imageName;
        //  $saveFile->save();

        ProposalTemplateCoverPhoto::where('proposal_template_id', $input['id'])->update(["cover_flg" => 0]);
        ProposalTemplateCoverPhoto::create(["proposal_template_id" =>$input['id'],"image_icon" => "public/template/".$imageName, "cover_flg" => 1, "user_id" => $company_id,"company_id" => $company_id]);
        ProposalTemplates::where('id', $input['id'])->update(["cover_img" => "public/template/".$imageName]);
//        $path = $request->file('cover_img')->store('public/template');
//        $input['cover_img'] = $path;
//        ProposalTemplateCoverPhoto::where('proposal_template_id', $input['id'])->update(["cover_flg" => 0]);
//        ProposalTemplateCoverPhoto::create(["proposal_template_id" =>$input['id'],"image_icon" => $input['cover_img'], "cover_flg" => 1, "user_id" => $company_id,"company_id" => $company_id]);

        return response()->json(['success'=>'Crop Image Uploaded Successfully']);
    }*/

    public function uploadCropCoverImage(Request $request) //s3 code
    {
        $input = $request->all();
        $user = Auth::user();
        $company_id = ($user->company_id)? $user->company_id : $user->id;

        $ext = explode(";", explode("/", $request->image)[1])[0];
        $imageName = uniqid() . '.'.$ext;
        //S3 bucket
        $imageData = $request->input('image');
        // Decode the base64 image data
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

        // Upload the image to S3
        Storage::disk('s3')->put('public/'.$company_id.'/templates/cover/'.$imageName, $imageData,'public');

        // Get the S3 URL of the uploaded image
        $s3Url = Storage::disk('s3')->url('public/'.$company_id.'/templates/cover/'.$imageName);

        ProposalTemplateCoverPhoto::where('proposal_template_id', $input['id'])->update(["cover_flg" => 0]);
        ProposalTemplateCoverPhoto::create(["proposal_template_id" =>$input['id'],"image_icon" => 'public/'.$company_id.'/templates/cover/'.$imageName, "cover_flg" => 1, "user_id" => $company_id,"company_id" => $company_id]);
        ProposalTemplates::where('id', $input['id'])->update(["cover_img" => 'public/'.$company_id.'/templates/cover/'.$imageName]);
//        $path = $request->file('cover_img')->store('public/template');
//        $input['cover_img'] = $path;
//        ProposalTemplateCoverPhoto::where('proposal_template_id', $input['id'])->update(["cover_flg" => 0]);
//        ProposalTemplateCoverPhoto::create(["proposal_template_id" =>$input['id'],"image_icon" => $input['cover_img'], "cover_flg" => 1, "user_id" => $company_id,"company_id" => $company_id]);

        return response()->json(['success'=>'Crop Image Uploaded Successfully']);
    }

    /*public function uploadAboutusCoverImage(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();
        $company_id = ($user->company_id)? $user->company_id : $user->id;

        $folderPath = public_path('storage/template/');
        $image_parts = explode(";base64,", $request->image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $ext = explode(";", explode("/", $request->image)[1])[0];
        $imageName = uniqid() . '.'.$ext;
        $imageFullPath = $folderPath.$imageName;
        file_put_contents($imageFullPath, $image_base64);

        //  $saveFile = new Picture;
        //  $saveFile->name = $folderPath.$imageName;
        //  $saveFile->save();

        ProposalTemplateAboutusPhoto::where('proposal_template_id', $input['id'])->update(["about_flg" => 0]);
        ProposalTemplateAboutusPhoto::create(["proposal_template_id" =>$input['id'],"image_icon" => "public/template/".$imageName, "about_flg" => 1, "user_id" => $company_id,"company_id" => $company_id]);
        ProposalTemplates::where('id', $input['id'])->update(["aboutas_img" => "public/template/".$imageName]);
//        $path = $request->file('cover_img')->store('public/template');
//        $input['cover_img'] = $path;
//        ProposalTemplateCoverPhoto::where('proposal_template_id', $input['id'])->update(["cover_flg" => 0]);
//        ProposalTemplateCoverPhoto::create(["proposal_template_id" =>$input['id'],"image_icon" => $input['cover_img'], "cover_flg" => 1, "user_id" => $company_id,"company_id" => $company_id]);

        return response()->json(['success'=>'Crop Image Uploaded Successfully']);
    }*/

    public function uploadAboutusCoverImage(Request $request) //s3 code
    {
        $input = $request->all();
        $user = Auth::user();
        $company_id = ($user->company_id)? $user->company_id : $user->id;

        $ext = explode(";", explode("/", $request->image)[1])[0];
        $imageName = uniqid() . '.'.$ext;
        //S3 bucket
        $imageData = $request->input('image');
        // Decode the base64 image data
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

        // Upload the image to S3
        Storage::disk('s3')->put('public/'.$company_id.'/templates/aboutus/'.$imageName, $imageData,'public');

        // Get the S3 URL of the uploaded image
        $s3Url = Storage::disk('s3')->url('public/'.$company_id.'/templates/aboutus/'.$imageName);



        ProposalTemplateAboutusPhoto::where('proposal_template_id', $input['id'])->update(["about_flg" => 0]);
        ProposalTemplateAboutusPhoto::create(["proposal_template_id" =>$input['id'],"image_icon" => 'public/'.$company_id.'/templates/aboutus/'.$imageName, "about_flg" => 1, "user_id" => $company_id,"company_id" => $company_id]);
        ProposalTemplates::where('id', $input['id'])->update(["aboutas_img" => 'public/'.$company_id.'/templates/aboutus/'.$imageName]);
//        $path = $request->file('cover_img')->store('public/template');
//        $input['cover_img'] = $path;
//        ProposalTemplateCoverPhoto::where('proposal_template_id', $input['id'])->update(["cover_flg" => 0]);
//        ProposalTemplateCoverPhoto::create(["proposal_template_id" =>$input['id'],"image_icon" => $input['cover_img'], "cover_flg" => 1, "user_id" => $company_id,"company_id" => $company_id]);

        return response()->json(['success'=>'Crop Image Uploaded Successfully']);
    }
}
