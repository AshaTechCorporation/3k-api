<?php

namespace App\Imports;

use App\TimeAttendance;
use Maatwebsite\Excel\Concerns\ToModel;

class TimeAttendanceImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new TimeAttendance([
            //
        ]);
    }
}
