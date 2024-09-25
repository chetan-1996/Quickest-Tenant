<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class TermConditionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = DB::table('units')->where([["company_id", "=", 1], ["name", "=", "Kw"]])->orderBy('id', 'ASC')->select("id")->first();
        
        $units1 = DB::table('units')->where([["company_id", "=", 1], ["name", "=", "Nos"]])->orderBy('id', 'ASC')->select("id")->first();

        $userDatas = DB::table('users')->where('id', '=', 1)->select(['company_category', 'name', 'email'])->first();
        if($userDatas->company_category == 1) {
            DB::table('term_conditions')->insert([
                [
                "name" => "Residential BOM & Terms", "description" => '<table align="center" cellspacing="0" style="border-collapse:collapse; width:100%">
<tbody>
    <tr>
        <td colspan="5" style="border-bottom:none; border-left:1px solid #e7e6e6; border-right:1px solid #e7e6e6; border-top:1px solid #e7e6e6; height:35px; text-align:center; vertical-align:bottom; white-space:nowrap; width:584px"><span style="font-size:27px"><span style="color:black"><span style="font-family:Calibri,sans-serif">Bill of Material&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:1px solid #bbbdc0; height:20px; text-align:center; vertical-align:bottom; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Sr No.</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:1px solid #bbbdc0; text-align:center; vertical-align:bottom; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Item</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:1px solid #bbbdc0; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Qty</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:1px solid #bbbdc0; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Unit</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:1px solid #bbbdc0; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Brand</span></strong></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Solar Panels&nbsp; (PV Modules)</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As specified in Quote</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">2</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Solar String Inverter&nbsp;&nbsp;</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As specified in Quote</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:59px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">3</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Solar 80 micron HDGI Structure*<br />
        60 x 40 mm x 2 mm For Leg , Rafters<br />
        40 x 40 mm x 2 mm For purlins</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As specified in Quote</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:26px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">4</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Protection Devices</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:49px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">4.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">ACDB (IP65) - With SPD, Fuse &amp; MCB<br />
        DCDB (IP65) - With SPD, Fuse &amp; MCB&nbsp;</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">polycab /schineder&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:27px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">5</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Cables</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">5.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">&nbsp;<span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">4 SQ MM </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">DC Solar Copper Cable, XLS-R, UV RESISTANT, 1100V Grade, Double Insulated</span></span></span></span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">30</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab / RR</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">5.2</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">4 SQ MM or 6 SQ MM AC Wire , XLS- R</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">30</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab / RR</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">5.3</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">4 SQ MM Copper Earthing wire for AC &amp; DC</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">30</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab / RR</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">5.4</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">16 SQ MM Aluminium Wire For LA</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">30</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab / RR</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">5.5</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">UPVC Conduit Pipe for wiring&nbsp;</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab / RR</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:33px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">6</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Earthing / LA - lightning arrestor</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">6.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">200 Micron Copper Coated 1 Meter Earthing Rod for AC / DC &amp; LA</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">3</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab / RR</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">6.2</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1 Meter copper LA with 3 spike &amp; insulator</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Standard&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">7</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Data Logger&nbsp;</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">7.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Wifi Stick : Data Loger for Oniline Monitoring</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As per inverter</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">8</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Other Accessories</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">&nbsp;</span></strong></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable tie, <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">SS304 </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">300mm (100Pcs/Pkt)</span></span></span></span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Ss304</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.2</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Ferules &amp; Cable Tags</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Standard</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:43px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.3</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:middle; white-space:normal; width:312px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Lugs Ring Type As per wiring requirements</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:77px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:63px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:89px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Coper</span></span></span></td>
    </tr>
</tbody>
</table>

<p>&nbsp;</p>

<div style="page-break-after:always"><span style="display:none">&nbsp;</span></div>

<table border="0" cellpadding="1" cellspacing="0" style="width:100%">
<tbody>
    <tr>
        <td style="text-align:center"><span style="font-size:18px"><strong>WARRANTY TERMS</strong></span></td>
    </tr>
</tbody>
</table>

<p><span style="font-size:16px"><strong>General Terms:</strong></span></p>

<ul>
<li>Material dispatch and Installation shall be started upon DISCOM approval only.</li>
<li>For better performance, solar panels should be cleaned by customer two times in a week.</li>
<li>Concealed wiring shall be done by company, if possible only. Otherwise, customer should do concealed wiring with their wiremen where material shall be provided by Company.</li>
<li>After successful installation, Customer shall take care of solar plant by doing timely cleaning. If we found less generation at the time of attending complaint due to non-cleaning, we may charge you additional service&nbsp; charge.</li>
<li>There is manufacturing warranty for all electronics equipment. Company will help to claim this warranty if require.&nbsp;</li>
</ul>

<p><span style="font-size:16px"><strong>Goverment Subsidy:</strong></span></p>

<ul>
<li><strong>Subsidy Credit:</strong> If applicable, any subsidy will be directly credited to the customer&#39;s account. Our company will handle all necessary documentation with the government.</li>
<li><strong>Delay Disclaimer:</strong> Please note that subsidy amounts may experience delays from the government&#39;s side. Our company is not liable to compensate for any delays or non-receipt of subsidies if not provided by the government.</li>
</ul>

<p><strong>1. Solar Panel (PV Modules) Performance Warranty</strong>:</p>

<ul>
<li>90% of rated capacity for the first 10 years.</li>
<li>80% of rated capacity for the next 15 years.</li>
<li>Total Panel Life: 25 years.</li>
<li>Refer Solar Panel Datasheet for detailed warranty terms</li>
</ul>

<p><strong>2. Inverter Manufacturing Defect Warranty:</strong></p>

<ul>
<li>5 years, extendable.</li>
<li>Refer Inverter Datasheet for detailed warranty terms</li>
</ul>

<p><strong>3. Balance of System (BOS):</strong></p>

<ul>
<li>Equipment/products supplied by us which are warranted against defects due to poor material, design, or workmanship.</li>
<li>This warranty is valid for 12 months from the date of commissioning or when put into service, whichever is earlier.</li>
</ul>

<p><strong>4. Operation &amp; Maintenance:</strong></p>

<ul>
<li>We offer 5 years of O&amp;M support, including fault finding, remote monitoring, site visits, and assistance with warranty claims for components.</li>
<li>O&amp;M does not include solar panel cleaning and washing.</li>
</ul>

<p><strong>Warranty&nbsp;Notes:</strong></p>

<p>- All warranties provided by the manufacturer/supplier are in favor of the buyer and cover the equipment for the specified period.<br />
- Warranties ensure safe working of individual components and vary in validity period.<br />
- Warranties do not cover damages caused by external hazardous conditions.</p>

<p><strong>Schedule for Site Completion:</strong></p>

<ul>
<li>Dispatch within 6 weeks after order confirmation with payment.</li>
<li>The entire power plant will be installed and commissioned within 60-70 days (approx.) after project accreditation, contract agreement, and possession of the site.</li>
</ul>

<p><strong>Payment Terms:</strong></p>

<ul>
<li>10% advance payment upon contract signing.</li>
<li>50% upon delivery of structure material.</li>
<li>30% upon delivery of modules.</li>
<li>10% before meter installation is complete.</li>
</ul>

<p><strong>Quotation Validity:</strong></p>

<ul>
<li>1 week from the date of issue.</li>
</ul>

<p><strong>Warranty Exclusions:</strong></p>

<ul>
<li>The warranty will not cover failures due to:</li>
<li>Damage or defect caused by transportation, accident, misuse, lack of maintenance, improper usage, or negligence by the owner.</li>
<li>Wilful damage, normal wear and tear, abuse, or misuse of equipment/product.</li>
<li>Damage or defect caused by Force Majeure events, including fire, earthquake, flood, or other natural disasters.</li>
<li>Damage or defect caused by unauthorized alterations, modifications, or conversions.</li>
<li>Repairs carried out by personnel not authorized by the contractor.</li>
<li>Defects or damages due to external causes.</li>
<li>Parts and components repaired or replaced during the warranty period are warranted only for the original warranty period. The contractor will take back replaced or defective material.</li>
</ul>

<p><strong>Scope of Work For Customer:</strong></p>

<ul>
<li>Providing access/approach to rooftop.</li>
<li>If any electrical modification is required from DISCOM ( i.e. ELCB, changeover switch etc.) Customer shall provide necessary support.</li>
<li>Provide necessary documents for project approvals from State/Central Government .</li>
<li>Site clearance, ladders, water, and electricity supply for smooth installation and commissioning of the project.</li>
<li>Customer shall provide Safe Place for Material unloading and storage during the work execution</li>
</ul>
', 'user_id' => 1, 'company_id' => 1
            ],
            [
                "name" => "Commercial BOM & Terms", "description" => '<table align="center" cellspacing="0" style="border-collapse:collapse; width:100%">
<tbody>
    <tr>
        <td colspan="5" style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:none; border-top:none; height:29px; text-align:center; vertical-align:bottom; white-space:nowrap; width:688px"><span style="font-size:27px"><span style="color:black"><span style="font-family:Calibri,sans-serif">Bill of Material&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Sr No.</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Item</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><strong><span style="color:black"><span style="font-family:Calibri,sans-serif">Qty</span></span></strong></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><strong><span style="color:black"><span style="font-family:Calibri,sans-serif">Unit</span></span></strong></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><strong><span style="color:black"><span style="font-family:Calibri,sans-serif">Brand</span></span></strong></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">PV Module, <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">570W </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">N-type TOPCON Technology</span></span></span></span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As specified&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1.2</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Solar Inverter Solis&nbsp;</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As specified&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">2</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Module Mounting Structure</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:none; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:116px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">2.1</span></span></span></td>
        <td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">For RCC Tarrace : -Leg,Rafter - 60x40x2mm HDGI Pipe<br />
        -Perlin, Support - 40x40x2mm HDGI Pipe<br />
        -Ss304 Nut Bolting structure<br />
        For Tin Shed:<br />
        Aluminium Mono Rails as per site requirements</span></span></span></td>
        <td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px">Nos</td>
        <td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">Standard</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:27px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">3</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">DC cables</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:46px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">3.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cables 1C X&nbsp; <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">4 SQ MM </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">DC Solar Copper Cable, XLS-R, UV RESISTANT, 1100V Grade, Double Insulated</span></span></span></span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">500</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:30px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">3.2</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">MC 4 Connector <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">1500Volt , IP65</span></strong></span></span></span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">20</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Elmex/Sibas</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">4</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">AC Cables</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:46px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">4.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1.1 KV GRADE, <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">3.5C X 25 Sq MM XLPE ALU. ARMOURED </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable (Inverter to ACDB) X (1 Run)</span></span></span></span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">5</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:52px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">4.2</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1.1 KV GRADE, <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">3.5C X&nbsp; 25 Sq MM XLPE ALU. ARMOURED </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable (ACDB to Costumer LT Panel) X (1 Run)</span></span></span></span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">40</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:23px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">5</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">ACDB</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:none; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:77px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">5.1</span></span></span></td>
        <td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">2 in 1 Out ACDB with MCB/ MCCB/ Fuse / Contactor &amp; RYB indicator with AL busbar or Copper Cable.<br />
        <br />
        For MCB rating please refer to Single Line Diagram</span></span></span></td>
        <td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px">
        <p>Nos</p>
        </td>
        <td style="border-bottom:none; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">polycab</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:23px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">6</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">DCDB</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">6.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">04 IN/04 OUT with 8 Nos. of <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">DC fuse</span></strong></span></span></span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Phoenix</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:20px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">7</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Conduit Pipe / Cable tray/Walkaway</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">7.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">UPVC <span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">pipe /</span></span></span><span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">FRP </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable tray for wiring</span></span></span></span></strong></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Waterway</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">7.2</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Walkway for Tin shed (FRP)&nbsp;</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Standard</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">7.2</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">T Connector - UPVC</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Waterway</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">7.3</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Elbows -UPVC</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Waterway</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">8</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Earthing &amp; LA&nbsp; (Earthing wires)</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">1Cx4 Sq.mm </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">green(Panel to panel earthing)</span></span></span></span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">100</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.2</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">1Cx10 Sq.mm Copper Wire OR GI strip</span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">25X3mm&nbsp; (inverter </span></span></span><span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">AC earthing &amp; structureearthing</span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">)</span></span></span></span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">100</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Polycab</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:117px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.3</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Earthing Kit<br />
        50 mm Dia x 3 Meter long HDGI Earthing Rod with Strip<br />
        with Earthing Chemical with 3 Meter deep with RCC Chamber<br />
        Inverter &amp; ACDB Earthing - 1 Nos.<br />
        Structure &amp; DC Earthing - 1 Nos.<br />
        Lightning arrestor - 1 Nos</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">3</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Elink Earthing&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:28px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.4</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable 1CX50 Sq.mm AL Wire Or&nbsp; <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">25x3 GI Strip </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">(L.A. Earthing)</span></span></span></span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">40</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Mtr</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Hotdip Galvanised</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.5</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">ESE Lightning Arrestor (107 meter Radius)</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Shockpro</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">8.6</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Insulator for LA in case of GI roof</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Standard</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">9</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Data Logger&nbsp;</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">9.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Wifi Stick : Data Loger for Oniline Monitoring</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Nos</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">As per inverter</span></span></span></td>
    </tr>
    <tr>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">10</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">Other Accessories</span></strong></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
        <td style="background-color:#e6e7e8; border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:black"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1.3</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Cable tie, <span style="font-size:11pt"><span style="color:#231f20"><strong><span style="font-family:Calibri,sans-serif">SS304 </span></strong></span></span><span style="font-size:11pt"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">300mm (100Pcs/Pkt)</span></span></span></span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Ss304</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">10.1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Ferules &amp; Cable Tags</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Standard</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">10.2</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Lugs Ring Type ( 4 Sq mm, 4mm Dia) for panel to panel Earthing</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Coper</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:39px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">10.3</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Lugs Ring Type ( 16 Sq mm cable ,10mm Dia)-structure/inverter earthing</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Coper</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">10.4</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Lugs Ring Type ( 35 Sq mm, 10mm Dia) for L.A.</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Coper</span></span></span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #bbbdc0; border-left:1px solid #bbbdc0; border-right:1px solid #bbbdc0; border-top:none; height:29px; text-align:center; vertical-align:middle; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">10.5</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:left; vertical-align:bottom; white-space:normal; width:386px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Lugs PIN Type (120 Sq mm,10mm Dia) for inverter</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:55px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">1</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:bottom; white-space:normal; width:64px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">Set</span></span></span></td>
        <td style="border-bottom:1px solid #bbbdc0; border-left:none; border-right:1px solid #bbbdc0; border-top:none; text-align:center; vertical-align:middle; white-space:normal; width:119px"><span style="font-size:15px"><span style="color:#231f20"><span style="font-family:Calibri,sans-serif">BI metalic</span></span></span></td>
    </tr>
    <tr>
    </tr>
</tbody>
</table>

<p>&nbsp;</p>

<div style="page-break-after:always"><span style="display:none">&nbsp;</span></div>

<p style="text-align:center"><span style="font-size:24px"><span style="color:#330099"><strong>WARRANTY TERMS</strong></span></span></p>

<p><strong>1. Solar Panel (PV Modules) Performance Warranty</strong>:</p>

<ul>
<li>90% of rated capacity for the first 10 years.</li>
<li>80% of rated capacity for the next 15 years.</li>
<li>Total Panel Life: 25 years.</li>
<li>Refer Solar Panel Datasheet for detailed warranty terms</li>
</ul>

<p><strong>2. Inverter Manufacturing Defect Warranty:</strong></p>

<ul>
<li>5 years, extendable.</li>
<li>Refer Inverter Datasheet for detailed warranty terms</li>
</ul>

<p><strong>3. Balance of System (BOS):</strong></p>

<ul>
<li>Equipment/products supplied by us which are warranted against defects due to poor material, design, or workmanship.</li>
<li>This warranty is valid for 12 months from the date of commissioning or when put into service, whichever is earlier.</li>
</ul>

<p><strong>Operation &amp; Maintenance:</strong></p>

<ul>
<li>We offer 5 years of O&amp;M support, including fault finding, remote monitoring, site visits, and assistance with warranty claims for components.</li>
<li>O&amp;M does not include solar panel cleaning and washing.</li>
</ul>

<p><strong>Notes:</strong></p>

<p>- All warranties provided by the manufacturer/supplier are in favor of the buyer and cover the equipment for the specified period.<br />
- Warranties ensure safe working of individual components and vary in validity period.<br />
- Warranties do not cover damages caused by external hazardous conditions.</p>

<p><strong>Schedule for Site Completion:</strong></p>

<ul>
<li>Dispatch within 6 weeks after order confirmation with payment.</li>
<li>The entire power plant will be installed and commissioned within 60-70 days (approx.) after project accreditation, contract agreement, and possession of the site.</li>
</ul>

<p><strong>Payment Terms:</strong></p>

<ul>
<li>10% advance payment upon contract signing.</li>
<li>50% upon delivery of structure material.</li>
<li>30% upon delivery of modules.</li>
<li>10% before meter installation is complete.</li>
</ul>

<p><strong>Quotation Validity:</strong></p>

<ul>
<li>1 week from the date of issue.</li>
</ul>

<p><strong>Warranty Exclusions:</strong></p>

<ul>
<li>The warranty will not cover failures due to:</li>
<li>Damage or defect caused by transportation, accident, misuse, lack of maintenance, improper usage, or negligence by the owner.</li>
<li>Wilful damage, normal wear and tear, abuse, or misuse of equipment/product.</li>
<li>Damage or defect caused by Force Majeure events, including fire, earthquake, flood, or other natural disasters.</li>
<li>Damage or defect caused by unauthorized alterations, modifications, or conversions.</li>
<li>Repairs carried out by personnel not authorized by the contractor.</li>
<li>Defects or damages due to external causes.</li>
<li>Parts and components repaired or replaced during the warranty period are warranted only for the original warranty period. The contractor will take back replaced or defective material.</li>
</ul>

<p><strong>Scope of Work For Customer:</strong></p>

<ul>
<li>Providing access/approach to rooftop.</li>
<li>If any electrical modification is required from DISCOM ( i.e. ELCB, changeover switch etc.) Customer shall provide necessary support.</li>
<li>Provide necessary documents for project approvals from State/Central Government .</li>
<li>Site clearance, ladders, water, and electricity supply for smooth installation and commissioning of the project.</li>
<li>Customer shall provide Safe Place for Material unloading and storage during the work execution</li>
</ul>

<p>&nbsp;</p>
', 'user_id' => 1, 'company_id' => 1
            ]
        ]);
    }

    if ($userDatas->company_category != 1) {
        DB::table('term_conditions')->insert([
            [
                "name" => "Basic Terms", "description" => '<p><span style="color:#3498db"><span style="font-size:16px"><strong>Terms &amp; Condition</strong></span></span></p>
<p>Material dispatch and Installation shall be started upon DISCOM approval only.</p>
<p>For better performance, solar panels should be cleaned by customer two times in a week.</p>
<p>Concealed wiring shall be done by company, if possible only. Otherwise, customer should do concealed wiring with their wiremen where material shall be provided by Company.</p>
<p>After successful installation, Customer shall take care of solar plant by doing timely cleaning. If we found less generation at the time of attending complaint due to non-cleaning, we may charge you additional service&nbsp; charge.</p>
<p>There is manufacturing warranty for all electronics equipment. Company will help to claim this warranty if require.&nbsp;</p>
<p>The company will provide up to 30 meter wire 25 Year warranty of PV Module, 10 Year warranty of Inverter and 5 Year O&amp;M of System by Company</p>
<p><span style="color:#3498db"><span style="font-size:16px"><strong>Scope of Work For Customer:</strong></span></span></p>
<p>Providing access/approach to rooftop&nbsp;</p>
<p>If any system modification is required from DISCOM ( i.e. ELCB, changeover etc.)&nbsp;</p>
<p>Provide necessary documents for project approvals from State/Central Government&nbsp;</p>
<p>Site clearance, water, and electricity for smooth installation and commissioning of the project&nbsp;</p>
<p>Required civil work and approvals to complete the project within the timeline proposed</p>
<p>Safe storage of materials (PV modules, Inverter, etc.) upon delivery&nbsp;</p>
<p>Customer shall provide Safe Place for Material unloading and storage during the work execution</p>
<p><span style="color:#3498db"><span style="font-size:16px"><strong>Warranty Exclusion:</strong></span></span></p>
<p>Damage due to improper handling&nbsp;</p>
<p>In absence of full payment&nbsp;</p>
<p>Damage to due to force majeure Defects due to third party inference (direct or indirect) or act to our system&nbsp;</p>
<p>This offer in itself or any subsequent Communications/documents will be subject to standard Force Majeure conditions.&nbsp;</p>
<p>Jurisdiction: Subject to Surat jurisdiction.</p>', 'user_id' => 1, 'company_id' => 1
                ]
            ]);
        }
    }
}
