<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ReserveExport implements WithMultipleSheets
{
    protected $data;
    protected $sel_doc;

    public function __construct(array $data, array $sel_doc)
    {
        $this->data = $data;
        $this->sel_doc = $sel_doc;

    }

    public function sheets(): array
    {
        // dd($this->data);
        $sheets = [];

        foreach ($this->sel_doc as $sheetNumber) {
            switch ($sheetNumber) {
                case 1:
                    $sheets[] = new namelist($this->data); //namelist
                    break;
                case 2:
                    $sheets[] = new booking($this->data); //1ใบจอง
                    break;
                case 3:
                    $sheets[] = new Condition($this->data); //2เงื่อนไข
                    break;
                case 4:
                    $sheets[] = new Technician_Notice($this->data); //3ใบแจ้งช่าง
                    break;
                case 5:
                    $sheets[] = new Purchase_sale($this->data); //4สัญญาซื้อ-ขาย
                    break;
                case 6:
                    $sheets[] = new Customer_check($this->data); //5ใบเช็ครถลูกค้า
                    break;
                case 7:
                    $sheets[] = new Mechanic_check($this->data); //6ใบเช็ครถของช่าง
                    break;
                case 8:
                    $sheets[] = new Delivery_note($this->data); //7ใบส่งมอบ
                    break;
                case 9:
                    $sheets[] = new Warranty_card($this->data); //8ใบรับประกัน
                    break;
                case 10:
                    $sheets[] = new Finance_confirmation($this->data); //9ใบคอนเฟิร์มไฟแนนท์
                    break;
                case 11:
                    $sheets[] = new Csr_before($this->data); //CSR ก่อนส่งมอบ
                    break;
                case 12:
                    $sheets[] = new Csr_after($this->data); //CSR หลังส่งมอบ
                    break;
                case 13:
                    $sheets[] = new Broker_Certificate($this->data); //ใบนายหน้า
                    break;
                case 14:
                    $sheets[] = new Mechanic_check_2($this->data); //ใบเช็ครถของช่าง
                    break;
                case 15:
                    $sheets[] = new Promotion_receipt($this->data); //ใบรับโปร 4 เด้ง
                    break;
                case 16:
                    $sheets[] = new approval_certificate($this->data); //ใบอนุมัติเฮีย
                    break;
                case 17:
                    $sheets[] = new System_check($this->data); //ใบเช็คระบบ
                    break;
                case 18:
                    $sheets[] = new Document_delivery($this->data); //ใบนำส่งเอกสาร
                    break;
                case 19:
                    $sheets[] = new Rubber_from($this->data); //ใบยาง
                    break;
                case 20:
                    $sheets[] = new Submit_work($this->data); //ใบส่งงานตรวจ
                    break;
                case 21:
                    $sheets[] = new Set_costs($this->data); //ตั้งต้นทุน
                    break;
                case 22:
                    $sheets[] = new Reservation($this->data); //คืนจอง
                    break;
                case 23:
                    $sheets[] = new vat_from($this->data); //ใบVAT
                    break;
                case 24:
                    $sheets[] = new Car_Notification($this->data); //แจ้งเตือนการรับรถ
                    break;
                case 25:
                    $sheets[] = new Sheet1($this->data); //Sheet1
                    break;
                case 26:
                    $sheets[] = new Booking_conditions($this->data); //เงื่อนไขการจอง
                    break;
                default:
                    break;
            }
        }

        return $sheets;
    }
}

class namelist implements FromView, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.namelist', [
            'data' => $this->data,
        ]);
    }

    public function title(): string
    {
        return 'namelist';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:CA100')->getFont()->setName('Tahoma');
            },
        ];
    }
}

class booking implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.booking', [
            'data' => $this->data,
        ]);
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp.png'));
        $drawing->setHeight(90);
        $drawing->setCoordinates('B1');

        return $drawing;
    }

    public function title(): string
    {
        return 'ใบจอง';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New');
                $event->sheet->getStyle('B11:F11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B7:B11')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B7:F7')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('F7:F11')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
            },
        ];
    }
}

class Condition implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Condition', [
            'data' => $this->data,
        ]);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('B1');
        $drawing->setOffsetX(20);
        $drawing->setOffsetY(10);

        return $drawing;
    }

    public function title(): string
    {
        return 'เงื่อนไข';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New');
                $event->sheet->getStyle('A4:L4')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('I8:K8')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('I9:K9')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D11:L11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D12:L12')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D13:L13')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D14:L14')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D15:F15')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('I15:L15')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
            },
        ];
    }
}

class Technician_Notice implements FromView, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Technician_Notice', [
            'data' => $this->data,
        ]);
    }

    public function title(): string
    {
        return 'ใบแจ้งช่าง';
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New');
                $event->sheet->getStyle('B2:G2')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('I2:M2')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B5:M5')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B6:M6')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B8:M8')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H10:M10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H18:M18')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H19:M19')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H24:M24')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H25:M25')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H29:M29')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H30:M30')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A3:A8')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('M3:M8')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('G3:G5')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H3:H5')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
            },
        ];
    }
}

class Purchase_sale implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Purchase_sale', [
            'data' => $this->data,
        ]);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp.png'));
        $drawing->setHeight(90);
        $drawing->setCoordinates('B2');

        return $drawing;
    }

    public function title(): string
    {
        return 'สัญญาซื้อ-ขาย';
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New');
                $event->sheet->getStyle('B34:L34')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B41:L41')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B42:L42')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('A35:A42')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L35:L42')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

            },
        ];
    }
}

class Customer_check implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Customer_check', [
            'data' => $this->data,
        ]);
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp2.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('E1');
        $drawing->setOffsetx(20);

        return $drawing;
    }

    public function title(): string
    {
        return 'ใบเช็ครถลูกค้า';
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New');
                $event->sheet->getStyle('A10:L10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A1:C1')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('A2:C2')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('A2')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

                $event->sheet->getStyle('L10')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
            },
        ];
    }
}

class Mechanic_check implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Mechanic_check', [
            'data' => $this->data,
        ]);
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp2.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('E1');
        $drawing->setOffsetx(20);

        return $drawing;
    }


    public function title(): string
    {
        return 'ใบเช็ครถของช่าง';
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New');
                $event->sheet->getStyle('A11:L11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A12:L12')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A22:L22')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A23:L23')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A32:L32')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A33:L33')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A40:L40')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A42:L42')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A43:L43')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A54:L54')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A55:L55')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('L10:L12')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L23')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L33')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L55')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L41:L43')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('B63:L63')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('A64:L64')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('A65:L65')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('A66:L66')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('A67:L67')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

            },
        ];
    }
}
class Delivery_note implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Delivery_note', [
            'data' => $this->data,
        ]);
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp_add.png'));
        $drawing->setHeight(120);
        $drawing->setCoordinates('B2');

        $drawing2 = new Drawing();
        $drawing2->setPath(public_path('/images/kp/pic10,000.png'));
        $drawing2->setCoordinates('G33');
        $drawing2->setHeight(180);

        return [$drawing, $drawing2];
    }


    public function title(): string
    {
        return 'ใบส่งมอบ';
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Tahoma');

                $event->sheet->getStyle('I10:L10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('I20:L20')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('B21:G21')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('I21:L21')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('B31:G31')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('I31:L31')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $event->sheet->getStyle('H11:H21')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('L11:L21')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('A22:A31')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('G22:G31')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('H22:H31')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('L22:L31')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            },
        ];
    }
}
class Warranty_card implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Warranty_card', [
            'data' => $this->data,
        ]);
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp2.png'));
        $drawing->setHeight(80);
        $drawing->setCoordinates('I1');
        $drawing->setOffsetx(20);
        $drawing->setOffsety(10);


        return $drawing;
    }


    public function title(): string
    {
        return 'ใบรับประกัน';
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Tahoma');

                $event->sheet->getStyle('B16:M16')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B22:M22')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B24:M24')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B31:M31')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('A17:A22')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A25:A31')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('M17:M22')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('M25:M31')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
            },
        ];
    }
}
class Finance_confirmation implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Finance_confirmation', [
            'data' => $this->data,
        ]);
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp3.png'));
        $drawing->setHeight(70);
        $drawing->setCoordinates('B2');
        $drawing->setOffsetx(30);
        $drawing->setOffsety(10);


        return $drawing;
    }


    public function title(): string
    {
        return 'ใบคอนเฟิร์มไฟแนนท์';
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New');

                // $event->sheet->getStyle('B16:M16')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);


                // $event->sheet->getStyle('A17:A22')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

            },
        ];
    }
}
class Csr_before implements FromView, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Csr_before', [
            'data' => $this->data,
        ]);
    }


    public function title(): string
    {
        return 'CSR ก่อนส่งมอบ';
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Tahoma');

            },
        ];
    }
}
class Csr_after implements FromView, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Csr_after', [
            'data' => $this->data,
        ]);
    }


    public function title(): string
    {
        return 'CSR หลังส่งมอบ';
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Tahoma');
            },
        ];
    }
}

class Broker_Certificate implements FromView, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Broker_certificate', [
            'data' => $this->data,
        ]);
    }


    public function title(): string
    {
        return 'ใบนายหน้า';
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New');

                $event->sheet->getStyle('B16:G16')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('B23:G23')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $event->sheet->getStyle('H8:L8')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H9:L9')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H10:L10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H11:L11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H12:L12')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H13:L13')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H14:L14')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H15:L15')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('A24:L24')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A25:L25')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A34:L34')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('H17:L17')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('H18:L18')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('H19:L19')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

                $event->sheet->getStyle('A17:A23')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $event->sheet->getStyle('G17:G23')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $event->sheet->getStyle('G9:G15')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('I9:I15')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L9:L15')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('F25:F34')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L25:L34')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
            },
        ];
    }
}
class Mechanic_check_2 implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Mechanic_check_2', [
            'data' => $this->data,
        ]);
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp2.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('E1');
        $drawing->setOffsetx(20);

        return $drawing;
    }


    public function title(): string
    {
        return 'ใบเช็ครถของช่าง';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:N120')->getFont()->setName('Angsana New');
                $event->sheet->getStyle('A11:L11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A12:L12')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A22:L22')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A23:L23')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A32:L32')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A33:L33')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A40:L40')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A42:L42')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A43:L43')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A54:L54')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A55:L55')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('L10:L12')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L23')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L33')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L55')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L41:L43')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('B63:L63')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('A64:L64')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('A65:L65')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('A66:L66')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('A67:L67')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

            },
        ];
    }
}

class Promotion_receipt implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Promotion_receipt', [
            'data' => $this->data,
        ]);
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp2.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('B2');
        $drawing->setOffsetY(20);

        return $drawing;
    }


    public function title(): string
    {
        return 'ใบรับโปร 4 เด้ง';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New'); //Tahoma,Angsana New
                $event->sheet->getStyle('B8:M8')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B10:M10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B15:M15')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B32:M32')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B37:M37')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('A11:A15')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('M11:M15')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A33:A37')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('M33:M37')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

            },
        ];
    }
}

class approval_certificate implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.approval_certificate', [
            'data' => $this->data,
        ]);
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('B1');
        $drawing->setOffsetx(50);

        return $drawing;
    }


    public function title(): string
    {
        return 'ใบอนุมัติเฮีย';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New'); //Tahoma,Angsana New
                $event->sheet->getStyle('B24')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B31')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('A37:H37')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A42:H42')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('A24')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B24')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A31')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B31')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H38:H42')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('C3:H3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C4:H4')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C5:H5')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C6:H6')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C7:H7')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

                $event->sheet->getStyle('C27:D27')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('G27:H27')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C28:D28')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('G28:H28')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C34:D34')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('G34:H34')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

                $event->sheet->getStyle('B39:C39')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('E39:H39')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C41:E41')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

            },
        ];
    }
}

class System_check implements FromView, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.System_check', [
            'data' => $this->data,
        ]);
    }


    public function title(): string
    {
        return 'ใบเช็คระบบ';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New'); //Tahoma,Angsana New
                $event->sheet->getStyle('B32:M32')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B36:M36')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('A33:A36')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('M33:M36')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('E3:M3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('E4:M4')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('E5:M5')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('E6:M6')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('E7:M7')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('F11:M11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('F12:M12')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('F13:M13')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('F14:M14')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('F15:M15')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('F16:M16')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('F17:M17')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('F18:M18')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('H19:M19')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('E20:M20')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('F22:M22')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('F23:M23')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('F25:M25')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('F26:M26')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('E30:M30')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('B31:M31')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D34:F34')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('I34:M34')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D35:F35')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('I35:M35')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

            },
        ];
    }
}

class Document_delivery implements FromView, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Document_delivery', [
            'data' => $this->data,
        ]);
    }

    public function title(): string
    {
        return 'ใบนำส่งเอกสาร';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New'); //Tahoma,Angsana New
                $event->sheet->getStyle('C13')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('C15')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('E30')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('E32')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('B14:B15')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('C14:C15')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('D31:D32')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('E31:E32')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                // $event->sheet->getStyle('B63:L63')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

            },
        ];
    }
}

class Rubber_from implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Rubber_from', [
            'data' => $this->data,
        ]);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/rubber.png'));
        $drawing->setHeight(654);
        $drawing->setCoordinates('A8');
        $drawing->setOffsetx(100);

        $drawing2 = new Drawing();
        $drawing2->setPath(public_path('/images/kp/check_rubber_from.png'));
        $drawing2->setCoordinates('C2');
        $drawing2->setHeight(60);
        $drawing->setOffsetx(30);
        $drawing->setOffsety(20);

        return [$drawing, $drawing2];
    }


    public function title(): string
    {
        return 'ใบยาง';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Tahoma'); //Tahoma,Angsana New
                // $event->sheet->getStyle('A11:L11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                // $event->sheet->getStyle('L10:L12')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('B1:D1')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('I1:K1')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
            },
        ];
    }
}

class Submit_work implements FromView, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Submit_work', [
            'data' => $this->data,
        ]);
    }


    public function title(): string
    {
        return 'ใบส่งงานตรวจ';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Tahoma'); //Tahoma,Angsana New
                $event->sheet->getStyle('B1:L1')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B2:L2')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('J3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('J4')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('B20:L20')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B21:L21')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('J22')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('J23')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('A2:A37')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L2:L37')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('I3:I4')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('J3:J4')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('A2:A37')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L2:L37')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('I22:I23')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('J22:J23')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('D3:H3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D4:H4')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D5:H5')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C7')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C8')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C9')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C12')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C13')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('I13:L13')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D15:L15')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('B16:L16')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('B17:L17')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('B18:L18')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

                $event->sheet->getStyle('D22:H22')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D23:H23')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D24:H24')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C26')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C27')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C28')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C29')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C30')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C31')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C32')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('I32:L32')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D34:L34')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('B35:L35')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('B36:L36')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('B37:L37')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

            },
        ];
    }
}

class Set_costs implements FromView, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Set_costs', [
            'data' => $this->data,
        ]);
    }


    public function title(): string
    {
        return 'ตั้งต้นทุน';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Tahoma'); //Tahoma,Angsana New
                $event->sheet->getStyle('B4:F4')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B5:C5')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('E5:F5')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B7:C7')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('E7:F7')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B8:C8')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('E8:F8')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('E10:F10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('E11:F11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B15:C15')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B16:C16')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B19:C19')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B20:C20')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B21:C21')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B23:C23')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('A1:A4')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('F1:F4')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A6:A20')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A22:A23')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B8')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B16')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('C6:C20')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('C22:C23')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('D6:D11')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('F6:F11')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
            },
        ];
    }
}

class Reservation implements FromView, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Reservation', [
            'data' => $this->data,
        ]);
    }


    public function title(): string
    {
        return 'คืนจอง';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New'); //Tahoma,Angsana New
                $event->sheet->getStyle('B12:L12')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B31:L31')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B36:L36')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('A32:A36')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('L32:L36')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('I3:L3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J4:L4')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C5:G5')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C6:G6')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D8:G8')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('I8:L8')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D9:G9')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('I9:L9')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('I10:L10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D14:G14')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J14:L14')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('D15:G15')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J15:L15')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C28:E28')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('I28:L28')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('I30:L30')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
            },
        ];
    }
}

class vat_from implements FromView, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.vat_from', [
            'data' => $this->data,
        ]);
    }


    public function title(): string
    {
        return 'ใบVAT';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Tahoma'); //Tahoma,Angsana New
                $event->sheet->getStyle('A2:M2')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A3:M3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('M3')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('F5:F22')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('C6:E6')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J6:M6')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C7:E7')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J7:M7')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C8:E8')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J8:M8')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C9:E9')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J9:M9')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C10:E10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J10:M10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C11:E11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J11:M11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C12:E12')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J12:M12')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C16:E16')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J16:M16')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C17:E17')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J17:M17')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C18:E18')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J18:M18')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('C20:E20')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $event->sheet->getStyle('J20:M20')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

            },
        ];
    }
}

class Car_Notification implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Car_Notification', [
            'data' => $this->data,
        ]);
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp2.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('B1');
        $drawing->setOffsetx(20);
        $drawing->setOffsetx(40);

        return $drawing;
    }


    public function title(): string
    {
        return 'แจ้งเตือนการรับรถ';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Tahoma'); //Tahoma,Angsana New
                // $event->sheet->getStyle('A11:L11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                // $event->sheet->getStyle('L10:L12')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                // $event->sheet->getStyle('B63:L63')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

            },
        ];
    }
}

class Sheet1 implements FromView, WithTitle, WithDrawings, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Sheet1', [
            'data' => $this->data,
        ]);
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/images/kp/logo_kp2.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('B1');
        $drawing->setOffsetx(20);
        $drawing->setOffsetx(40);
        return $drawing;
    }


    public function title(): string
    {
        return 'Sheet1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Angsana New'); //Tahoma,Angsana New
                // $event->sheet->getStyle('A11:L11')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                // $event->sheet->getStyle('L10:L12')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                // $event->sheet->getStyle('B63:L63')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);

            },
        ];
    }
}

class Booking_conditions implements FromView, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        // dd($aggregatedData);
        return view('export.Booking_conditions', [
            'data' => $this->data,
        ]);
    }

    public function title(): string
    {
        return 'เงื่อนไขการจอง';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:S100')->getFont()->setName('Tahoma'); //Tahoma,Angsana New
                $event->sheet->getStyle('A1:H1')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A2:H2')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B3:H3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B4:H4')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B12:H12')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B13:H13')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B14:H14')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B15:H15')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B16:H16')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('B17:H17')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $event->sheet->getStyle('H2')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('D15')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('D17')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('A4:A17')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('H4:H17')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('D4:D13')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->getStyle('F4:F12')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
            },
        ];
    }
}
