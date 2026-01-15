@php
    use Carbon\Carbon;

    $date = Carbon::now()->format('d/m/Y');
    // dd($data);
@endphp
<table>
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
        <td style="text-align: left; width: 200px;">{{ $name }}</td>
        <td style="text-align: left; width: 65px;"></td>
        <td style="text-align: left; width: 110px;"></td>
        <td style="text-align: left; width: 70px;"></td>
        <td style="text-align: left; width: 70px;">วิกที่</td>
        <td style="text-align: left; width: 125px;">16-30/9/2566</td>
    </tr>
    <tr>
        <td style="text-align: left;">ตำแหน่ง</td>
        <td style="text-align: left;">{{ $data['position'] }}</td>
        <td></td>
        <td></td>
        <td></td>
        <td style="text-align: left;">วันที่สั่งจ่าย</td>
        <td style="text-align: left;">{{ $date }}</td>
    </tr>
    <tr>
        <th colspan="2" style="border: 1px solid black;">รายได้</th>
        <th style="border: 1px solid black;">จำนวนเงิน</th>
        <th colspan="2" style="border: 1px solid black; color: red;">รายการหัก</th>
        <th style="border: 1px solid black; color: red;">จำนวนเงิน</th>
        <th style="border: 1px solid black;">หมายเหตุ</th>
    </tr>
    @php
        $count = 0;
        $incomePaidsCount = count($data['income_paids']);
        $deductPaidsCount = count($data['Deduct_paids']);

        $row = max($incomePaidsCount, $deductPaidsCount);
        // return $row;
    @endphp

    @if ($data['total_ot'] != 0 || $data['total_late_deduct'] != 0)
        @php
            $count++;
        @endphp
        <tr>
            @if ($data['total_ot'] != 0)
                <td colspan="2" style="border: 1px solid black;">ค่าโอที</td>
                <td style="border: 1px solid black; text-align: right;">{{ $data['total_ot'] }}</td>
            @else
                <td colspan="2" style="border: 1px solid black;">ค่าโอที</td>
                <td style="border: 1px solid black; text-align: right;">0</td>
            @endif

            @if ($data['total_late_deduct'] != 0)
                <td colspan="2" style="border: 1px solid black; color: red;">ค่ามาสาย</td>
                <td style="border: 1px solid black; text-align: right; color: red;">{{ $data['total_late_deduct'] }}</td>
            @else
                <td colspan="2" style="border: 1px solid black; color: red;">ค่ามาสาย</td>
                <td style="border: 1px solid black; text-align: right; color: red;">0</td>
            @endif
            <td style="border: 1px solid black;"></td>
        </tr>
    @endif
    @for ($i = 0; $i < $row; $i++)
        @php
            $count++;
            $incomePaid = $data['income_paids'][$i] ?? null;
            $deductPaid = $data['Deduct_paids'][$i] ?? null;
        @endphp
        <tr>
            <td colspan="2" style="border: 1px solid black;">
                {{ $incomePaid && !empty($incomePaid['income_type']) ? $incomePaid['income_type'] : null }}
            </td>
            <td style="border: 1px solid black; text-align: right;">
                {{ $incomePaid && !empty($incomePaid['paid']) ? $incomePaid['paid'] : null }}
            </td>
            <td colspan="2" style="border: 1px solid black; color: red;">
                {{ $deductPaid && !empty($deductPaid['Deduct_type']) ? $deductPaid['Deduct_type'] : null }}
            </td>
            <td style="border: 1px solid black; color: red; text-align: right;">
                {{ $deductPaid && !empty($deductPaid['paid']) ? $deductPaid['paid'] : null }}
            </td>
            <td style="border: 1px solid black;"></td>
        </tr>
    @endfor

    @for ($count; $count < 10; $count++)
        <tr>
            <td colspan="2" style="border: 1px solid black;">&nbsp;</td>
            <td style="border: 1px solid black;"></td>
            <td colspan="2" style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;"></td>
        </tr>';
    @endfor

    <tr>
        <td colspan="2" rowspan="2" style="border: 1px solid black; text-align: right;">รวมรายการได้</td>
        <td rowspan="2" style="border: 1px solid black;">{{ $data['total_income'] }}</td>
        <td colspan="2" rowspan="2" style="border: 1px solid black; color: red; text-align: right;">รวมรายการหัก</td>
        <td rowspan="2" style="border: 1px solid black; color: red;">{{ $data['total_deduction'] }}</td>
        <td style="border: 1px solid black; text-align: center;">เงินได้สุทธิ</td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">{{ $data['net_income'] }}</td>
    </tr>




    <tr></tr>
    <tr>
        <td></td>
        <td colspan="2">ผู้บันทึก..........................................................</td>
        <td></td>
        <td colspan="2">ผู้รับเงิน..............................................................</td>
    </tr>
    <tr>
        <td></td>
        <td>(นางสาวอริษา แสนในเมือง)</td>
        <td></td>
        <td></td>
        <td>({{ $data['name'] }})</td>
    </tr>
    <tr></tr>
    <tr>
        <td></td>
        <td colspan="2">ผู้อนุมัติจ่าย...................................................</td>
    </tr>
    <tr>
        <td></td>
        <td>(นางสาวนาตยา นราวัฒน์)</td>
    </tr>
</table>
