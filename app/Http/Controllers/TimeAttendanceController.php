<?php

namespace App\Http\Controllers;

use App\Imports\TimeAttendanceImport;
use App\Models\TimeAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TimeAttendanceController extends Controller
{
    public function getList($month, $year)
    {
        $Item = TimeAttendance::where('month', $month)->where('year', $year)->get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getPage(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $month = $request->month;
        $year = $request->year;

        $col = array('id', 'employeeNo', 'groupName', 'absentCount', 'actualWorkday', 'lateCount', 'percenWork', 'personalLeaveCount', 'sickLeaveCount', 'sumEarly', 'sumLate', 'sumOT', 'totalWorkday', 'name', 'month', 'year', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'employeeNo', 'groupName', 'absentCount', 'actualWorkday', 'lateCount', 'percenWork', 'personalLeaveCount', 'sickLeaveCount', 'sumEarly', 'sumLate', 'sumOT', 'totalWorkday', 'name', 'month', 'year', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = TimeAttendance::select($col);

        if ($month) {
            $D->where('month', $month);
        }

        if ($year) {
            $D->where('year', $year);
        }

        if ($orderby[$order[0]['column']]) {
            $D->orderby($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if ($search['value'] != '' && $search['value'] != null) {

            $D->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orWhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });

                //search with
                // $query = $this->withPermission($query, $search);
            });
        }

        $d = $D->paginate($length, ['*'], 'page', $page);

        if ($d->isNotEmpty()) {

            //run no
            $No = (($page - 1) * $length);

            for ($i = 0; $i < count($d); $i++) {

                $No = $No + 1;
                $d[$i]->No = $No;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }
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

        if (!isset($request->employee_no)) {
            return $this->returnError('กรุณาระบุชื่อประเภทงานให้เรียบร้อย', 404);
        }

        DB::beginTransaction();

        try {

            $Item = TimeAttendance::where('employee_no', $request->employee_no)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->first();

            if (!$Item) {
                $Item = new TimeAttendance();
            }


            $Item->employee_no = $request->employee_no;
            $Item->group_name = $request->group_name;
            $Item->absent_count = $request->absent_count;
            $Item->actual_workday = $request->actual_workday;
            $Item->late_count = $request->late_count;
            $Item->percen_work = $request->percen_work;
            $Item->personal_leave_count = $request->personal_leave_count;
            $Item->sick_leave_count = $request->sick_leave_count;
            $Item->sum_early = $request->sum_early;
            $Item->sum_late = $request->sum_late;
            $Item->sum_o_t = $request->sum_o_t;
            $Item->total_workday = $request->total_workday;
            $Item->total_forgot = $request->total_forgot;
            $Item->name = $request->name;
            $Item->month = $request->month;
            $Item->year = $request->year;
            $Item->create_by = "Admin";
            $Item->updated_at = Carbon::now()->toDateTimeString();

            $Item->save();


            $ItemU = User::where('user_no', $request->employee_no)->first();

            if ($ItemU) {
                $ItemU->name = $request->name;
                $ItemU->save();
            }

            //log
            $userId = "Admin";
            $type = 'เพิ่มประเภทงาน';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->user_id;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnError('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TimeAttendance  $timeAttendance
     * @return \Illuminate\Http\Response
     */
    public function show(TimeAttendance $timeAttendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TimeAttendance  $timeAttendance
     * @return \Illuminate\Http\Response
     */
    public function edit(TimeAttendance $timeAttendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TimeAttendance  $timeAttendance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TimeAttendance $timeAttendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TimeAttendance  $timeAttendance
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = TimeAttendance::find($id);
            $Item->delete();

            //log
            $userId = "admin";
            $type = 'ลบผู้ใช้งาน';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    public function Import(Request $request)
    {
        ini_set('memory_limit', '4048M');

        $file = request()->file('file');
        $fileName = $file->getClientOriginalName();

        $Data = Excel::toArray(new TimeAttendanceImport(), $file);

        $data = $Data[0];

        if (count($data) > 0) {

            $insert_data = [];

            for ($i = 1; $i < count($data); $i++) {
                $insert_data[] = array(
                    'user_no' => trim($data[$i][1]),
                    'date' => trim($data[$i][2]),
                    'time' => trim($data[$i][3]),
                    'time_status' => trim($data[$i][4]),
                    'location' => trim($data[$i][5])
                );
            }
        }

        if (!empty($insert_data)) {

            DB::beginTransaction();

            try {

                DB::table('time_attendances')->insert($insert_data);

                //log
                $type = 'นำเข้าข้อมูล';
                $description = 'ผู้ใช้งาน ได้ทำการ ' . $type;
                $this->Log("admin", $description, $type);
                //

                DB::commit();

                return $this->returnSuccess('นำเข้าข้อมูลสำเร็จ', []);
            } catch (\Throwable $e) {

                DB::rollback();

                return $this->returnErrorData('นำเข้าข้อมูลผิดพลาด ' . $e, 404);
            }
        }
    }



    public function getTimeCheck(Request $request)
    {

        $Item = User::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                if (isset($request->date)) {
                    $Item[$i]['No'] = $i + 1;
                    $Item[$i]['user_no'] = $Item[$i]['user_no'];
                    $timeIn = TimeAttendance::where('user_no', $Item[$i]['user_no'])->where('date', $request->date)->where('time_status', 'In')->orderBy('time', 'asc')->first();
                    $timeOut = TimeAttendance::where('user_no', $Item[$i]['user_no'])->where('date', $request->date)->where('time_status', 'Out')->orderBy('time', 'desc')->first();

                    if (isset($timeIn)) {
                        $Item[$i]['time_in'] = $timeIn->time;
                    } else {
                        $Item[$i]['time_in'] = "-";
                    }

                    if (isset($timeOut)) {
                        $Item[$i]['time_out'] = $timeOut->time;
                    } else {
                        $Item[$i]['time_out'] = "-";
                    }

                    $Item[$i]['No'] = $i + 1;
                } else {
                    $Item[$i]['No'] = $i + 1;
                    $Item[$i]['user_no'] = $Item[$i]['user_no'];
                    $timeIn = TimeAttendance::where('user_no', $Item[$i]['user_no'])->where('date', date('Y-m-d'))->where('time_status', 'In')->orderBy('time', 'asc')->first();
                    $timeOut = TimeAttendance::where('user_no', $Item[$i]['user_no'])->where('date', date('Y-m-d'))->where('time_status', 'Out')->orderBy('time', 'desc')->first();

                    if (isset($timeIn)) {
                        $Item[$i]['time_in'] = $timeIn->time;
                    } else {
                        $Item[$i]['time_in'] = "-";
                    }

                    if (isset($timeOut)) {
                        $Item[$i]['time_out'] = $timeOut->time;
                    } else {
                        $Item[$i]['time_out'] = "-";
                    }

                    $Item[$i]['No'] = $i + 1;
                }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }
}
