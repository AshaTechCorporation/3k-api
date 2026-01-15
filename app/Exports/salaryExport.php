<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class salaryExport implements WithMultipleSheets
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->data as $datasalary) {
            $name = $datasalary['name'];
            $sheets[] = new salarynameExport($datasalary, $name); //แยกตามคน group ตามคน
        }
        return $sheets;
    }
}
class salarynameExport implements FromView, WithTitle
{
    protected $datasalary;
    protected $name;

    public function __construct(array $datasalary, $name)
    {
        $this->datasalary = $datasalary;
        $this->name = $name;
    }

    public function view(): View
    {
        // dd($this->datasalary);
        // $total_income = array_sum(array_column($this->datasalary, 'total_income'));
        // $total_deduction = array_sum(array_column($this->datasalary, 'total_deduction'));
        // $net_income = $total_income - $total_deduction;

        return view('salarydata', [
            'data' => $this->datasalary,
            'name' => $this->name,
            // 'total_income' => $total_income,
            // 'total_deduction' => $total_deduction,
            // 'net_income' => $net_income,
        ]);
    }

    public function title(): string
    {
        return $this->name;
    }
}
