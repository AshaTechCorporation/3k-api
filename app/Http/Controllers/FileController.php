<?php

namespace App\Http\Controllers;

use App\Exports\ReserveExport;
use App\Exports\salaryExport;
use App\Models\DeductPaid;
use App\Models\DeductType;
use App\Models\IncomePaid;
use App\Models\IncomeType;
use App\Models\Payroll;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function excel_payslip(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        $users = User::get();
        if ($users->isEmpty()) {
            return $this->DatareturnErrorData('ไม่พบข้อมูลพนักงาน', 404);
        }

        // return $users;

        $users->each(function ($user) use (&$year, &$month) {
            $income_paids = IncomePaid::where('user_id', $user->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();
            $Deduct_paids = DeductPaid::where('user_id', $user->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();
            $position = Position::find($user->position_id);
            $total_income = 0;
            $total_deduction = 0;

            $income_paids->each(function ($income_paid) use (&$total_income) {
                $income_type = IncomeType::where('code', $income_paid->incode)->first();
                if ($income_type) {
                    $income_paid->income_type = $income_type->name;
                    $total_income += $income_paid->paid;
                }
            });
            $Deduct_paids->each(function ($Deduct_paid) use (&$total_deduction) {
                $Deduct_type = DeductType::where('code', $Deduct_paid->decode)->first();
                if ($Deduct_type) {
                    $Deduct_paid->Deduct_type = $Deduct_type->name;
                    $total_deduction += $Deduct_paid->paid;
                }
            });
            $user->position = $position->name;
            $user->income_paids = $income_paids;
            $user->Deduct_paids = $Deduct_paids;

            $payroll = Payroll::where('user_no', $user->user_no)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->first();
            // $user->total_income = $total_income;
            // $user->total_deduction = $total_deduction;
            // $user->net_income = $total_income-$total_deduction;
            $user->total_ot = $payroll->total_ot;
            $user->total_late_deduct = $payroll->total_late_deduct;
            $user->total_income = $payroll->total_income;
            $user->total_deduction = $payroll->total_deduct;
            $user->net_income = $payroll->total_summary;
        });

        $users = $users->toArray();

        // return $users;
        $result = new salaryExport($users);
        return Excel::download($result, 'saraly.xlsx');
    }
    public function pdf_payslip(Request $request)
    {   //ฟิวเตอร์ name
        $user_no = $request->user_no;
        if (!$user_no) {
            return $this->DatareturnErrorData('ไม่พบข้อมูลที่ส่งมา', 404);
        }
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        $date = Carbon::now();
        $thaiYear = $date->year + 543;
        $thaiDate = $date->format('d/m') . '/' . $thaiYear;

        $users = User::where('user_no', $user_no)
            //  ->whereYear('created_at', $year)
            //  ->whereMonth('created_at', $month)
            ->get();

        if ($users->isEmpty()) {
            return $this->returnErrorData('ไม่พบข้อมูลพนักงาน', 404);
        }
        $position = Position::find($users[0]->position_id);

        $total_incomes = 0;
        $total_deduction = 0;

        $users->each(function ($user) use (&$year, &$month) {
            $income_paids = IncomePaid::where('user_id', $user->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();
            $Deduct_paids = DeductPaid::where('user_id', $user->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();

            $income_paids->each(function ($income_paid) use (&$total_incomes) {
                $income_type = IncomeType::where('code', $income_paid->incode)->first();
                if ($income_type) {
                    $income_paid->income_type = $income_type->name;
                    $total_incomes += $income_paid->paid;
                }
            });
            $Deduct_paids->each(function ($Deduct_paid) use (&$total_deduction) {
                $Deduct_type = DeductType::where('code', $Deduct_paid->decode)->first();
                if ($Deduct_type) {
                    $Deduct_paid->Deduct_type = $Deduct_type->name;
                    $total_deduction += $Deduct_paid->paid;
                }
            });
            $user->income_paids = $income_paids;
            $user->Deduct_paids = $Deduct_paids;
            $user->total_income = $total_incomes;
            $user->total_deduction = $total_deduction;
            $user->net_income = $total_incomes - $total_deduction;
        });

        // return $position->name;
        if ($users->isEmpty()) {
            return $this->returnErrorData('ไม่พบข้อมูลพนักงาน', 404);
        }

        $payroll = Payroll::where('user_no', $user_no)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->first();

        $content = '
            <table style="width: 100%; border-collapse: collapse; font-size: 20px;">
                <tr>
                    <td colspan="7" style="text-align: center">ห้างหุ้นส่วนจำกัด ส.สปีดออโต้ปากน้ำ (สำนักงานใหญ่)</td>
                </tr>
                <tr>
                    <td colspan="7" style="text-align: center">251/2 หมู่ที่ 11 ตำบลนาป่า อำเภอเมือง จังหวัดเพชรบูรณ์ 67000 โทร. 081-2393070</td>
                </tr>
                <tr>
                    <td colspan="7" style="text-align: center">เลขประจำตัวผู้เสียภาษีอากร 0-6735-63000-95-1</td>
                </tr>
                <tr>
                    <td colspan="7" style="text-align: center">ใบแจ้งรายได้ PAY SLIP</td>
                </tr>
                <tr>
                    <td style="text-align: left; width: 70px;">ชื่อพนักงาน</td>
                    <td style="text-align: left; width: 200px;">' . $users[0]->name . '</td>
                    <td style="text-align: left; width: 65px;"></td>
                    <td style="text-align: left; width: 110px;"></td>
                    <td style="text-align: left; width: 70px;"></td>
                    <td style="text-align: left; width: 70px;">วิกที่</td>
                    <td style="text-align: left; width: 125px;">16-30/9/2566</td>
                </tr>
                <tr>
                    <td style="text-align: left;">ตำแหน่ง</td>
                    <td style="text-align: left;">' . $position->name . '</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align: left;">วันที่สั่งจ่าย</td>
                    <td style="text-align: left;">' . $thaiDate . '</td>
                </tr>
                <tr>
                    <th colspan="2" style="border: 1px solid black;">รายได้</th>
                    <th style="border: 1px solid black;">จำนวนเงิน</th>
                    <th colspan="2" style="border: 1px solid black; color: red;">รายการหัก</th>
                    <th style="border: 1px solid black; color: red;">จำนวนเงิน</th>
                    <th style="border: 1px solid black;">หมายเหตุ</th>
                </tr>';
        $count = 0;
        $incomePaidsCount = count($users[0]->income_paids);
        $deductPaidsCount = count($users[0]->Deduct_paids);

        $row = max($incomePaidsCount, $deductPaidsCount);
        // return $row;
        if ($payroll->total_ot != 0 || $payroll->total_late_deduct != 0) {
            $count++;
            $content .= '
                <tr>';
            if ($payroll->total_ot != 0) {
                $content .= '
                        <td colspan="2" style="border: 1px solid black;">' . 'ค่าโอที' . '</td>
                        <td style="border: 1px solid black; text-align: right;">' . $payroll->total_ot . '</td>
                    ';
            } else {
                $content .= '
                        <td colspan="2" style="border: 1px solid black;">' . 'ค่าโอที' . '</td>
                        <td style="border: 1px solid black; text-align: right;">' . '0' . '</td>
                    ';
            }

            if ($payroll->total_late_deduct != 0) {
                $content .= '
                        <td colspan="2" style="border: 1px solid black; color: red;">' . 'ค่ามาสาย' . '</td>
                        <td style="border: 1px solid black; text-align: right; color: red;">' . $payroll->total_late_deduct . '</td>
                    ';
            } else {
                $content .= '
                        <td colspan="2" style="border: 1px solid black; color: red;">' . 'ค่ามาสาย' . '</td>
                        <td style="border: 1px solid black; text-align: right; color: red;">' . '0' . '</td>
                    ';
            }
            $content .= '
                    <td style="border: 1px solid black;">' . '</td>
                </tr>
            ';
        }
        for ($i = 0; $i < $row; $i++) {
            $count++;
            $content .= '
                <tr>
                    <td colspan="2" style="border: 1px solid black;">' . (
                !empty($users[0]) && !empty($users[0]->income_paids) && !empty($users[0]->income_paids[$i]) && !empty($users[0]->income_paids[$i]->income_type)
                ? $users[0]->income_paids[$i]->income_type : '&nbsp;'
            ) . '</td>
                            <td style="border: 1px solid black; text-align: right;">' . (
                !empty($users[0]) && !empty($users[0]->income_paids) && !empty($users[0]->income_paids[$i]) && !empty($users[0]->income_paids[$i]->paid)
                ? $users[0]->income_paids[$i]->paid : '&nbsp;'
            ) . '</td>
                            <td colspan="2" style="border: 1px solid black; color: red;">' . (
                !empty($users[0]) && !empty($users[0]->Deduct_paids) && !empty($users[0]->Deduct_paids[$i]) && !empty($users[0]->Deduct_paids[$i]->Deduct_type)
                ? $users[0]->Deduct_paids[$i]->Deduct_type : '&nbsp;'
            ) . '</td>
                            <td style="border: 1px solid black; color: red; text-align: right;">' . (
                !empty($users[0]) && !empty($users[0]->Deduct_paids) && !empty($users[0]->Deduct_paids[$i]) && !empty($users[0]->Deduct_paids[$i]->paid)
                ? $users[0]->Deduct_paids[$i]->paid : '&nbsp;'
            ) . '</td>
                    <td style="border: 1px solid black;">' . '</td>
                </tr>';
        }




        for ($count; $count < 10; $count++) {
            $content .= '
                <tr>
                    <td colspan="2" style="border: 1px solid black;">&nbsp;</td>
                    <td style="border: 1px solid black;"></td>
                    <td colspan="2" style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                </tr>';
        }

        $total = $payroll->total_summary ?? 0;
        $total_income = $payroll->total_income ?? 0;
        $total_deduct = $payroll->total_deduct ?? 0;

        $content .= '
                <tr>
                    <td colspan="2" rowspan="2" style="border: 1px solid black; text-align: right;">รวมรายการได้</td>
                    <td rowspan="2" style="border: 1px solid black; text-align: right;">' . $total_income  . '</td>
                    <td colspan="2" rowspan="2" style="border: 1px solid black; color: red; text-align: right;">รวมรายการหัก</td>
                    <td rowspan="2" style="border: 1px solid black; color: red; text-align: right">' . $total_deduct . '</td>
                    <td style="border: 1px solid black; text-align: center;">เงินได้สุทธิ</td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; text-align: center">' . $total . '</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="2">ผู้บันทึก..........................................................</td>
                    <td></td>
                    <td colspan="3">ผู้รับเงิน..........................................................</td>
                </tr>
                <tr>
                    <td></td>
                    <td>(นางสาวอริษา แสนในเมือง)</td>
                    <td></td>
                    <td></td>
                    <td colspan="3">(' . $users[0]->name . ')</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="2">ผู้อนุมัติจ่าย...................................................</td>
                </tr>
                <tr>
                    <td></td>
                    <td>(นางสาวนาตยา นราวัฒน์)</td>
                </tr>
            </table>';

        //PDF
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                base_path() . '/custom/font/directory',
            ]),
            'fontdata' => $fontData + [ // lowercase letters only in font key
                'th-sarabun-it' => [
                    'R' => 'THSarabunIT๙.ttf',
                    'I' => 'THSarabunIT๙ Italic.ttf',
                    'B' => 'THSarabunIT๙ Bold.ttf',
                    'BI' => 'THSarabunIT๙ BoldItalic.ttf',
                ], 'th-sarabun' => [
                    'R' => 'THSarabun.ttf',
                    'I' => 'THSarabun Italic.ttf',
                    'B' => 'THSarabun Bold.ttf',
                    'BI' => 'THSarabun BoldItalic.ttf',
                ],
            ],
            'default_font' => 'th-sarabun',
            'mode' => 'utf-8',
            'format' => 'A4',
            // 'default_font_size' => 12,
            // 'default_font' => 'sarabun',
            // 'margin_left' => 5,
            // 'margin_right' => 5,
            // 'margin_top' => 5,
            // 'margin_bottom' => 5,
            // 'margin_header' => 5,
            // 'margin_footer' => 5,
        ]);
        $mpdf->SetTitle('เงินเดือน');
        $mpdf->AddPage();
        $mpdf->WriteHTML($content);
        $mpdf->Output('เงินเดือน.pdf', 'I');
    }

    public function downloadExcel(Request $request)
    {
        $data = [];
        $select_doc = $request->input('sheets', []);

        try {
            $result = new ReserveExport($data,$select_doc);

            return Excel::download($result, 'Reserve.xlsx');
        } catch (\Exception $e) {
            return $this->returnErrorData($e->getMessage(), 'Error downloading file');
        }
    }
}
