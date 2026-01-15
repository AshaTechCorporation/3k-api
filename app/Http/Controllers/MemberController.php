<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Hospital;
use App\Models\Khet;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\memberExport;
use Maatwebsite\Excel\Facades\Excel;

class MemberController extends Controller
{
    public function getList()
    {
        $Item = Member::get()->toarray();

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

        $type = $request->type;

        $col = array('id', 'fname', 'lname', 'idcard', 'email', 'phone', 'address', 'sex', 'birthday', 'khet_id', 'province_id', 'hospital_id', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'fname', 'lname', 'idcard', 'email', 'phone', 'address', 'sex', 'birthday', 'khet_id', 'province_id', 'hospital_id', 'create_by');

        $D = Member::select($col);

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
                $d[$i]->khet = Khet::find($d[$i]->khet_id);
                $d[$i]->province = Province::find($d[$i]->province_id);
                $d[$i]->hospital = Hospital::find($d[$i]->hospital_id);

                // Create a Carbon instance from the birthday string
                $birthday = Carbon::createFromFormat('d/m/Y', $d[$i]->birthday);

                // Get the current date
                $now = Carbon::now();

                // Calculate the age
                $age = $now->diffInYears($birthday);

                $d[$i]->age = $age;
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
        if (!isset($request->fname)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = new Member();
            $Item->code = $request->code;
            $Item->fname = $request->fname;
            $Item->lname = $request->lname;
            $Item->idcard = $request->idcard;
            $Item->email = $request->email;
            $Item->phone = $request->phone;
            $Item->address = $request->address;
            $Item->sex = $request->sex;
            $Item->birthday = $request->birthday;
            $Item->khet_id = $request->khet_id;
            $Item->province_id = $request->province_id;
            $Item->hospital_id = $request->hospital_id;
            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Member::where('id', $id)
            ->first();

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function edit(Member $member)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Member $member)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Member::find($id);
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

    public function excel_export($id)
    {
        $hospital = $id;

        $users = Member::get();
        if ($users->isEmpty()) {
            return $this->DatareturnErrorData('ไม่พบข้อมูลพนักงาน', 404);
        }

        

        $users = $users->toArray();

        // return $users;
        $result = new memberExport($users);
        return Excel::download($result, 'member.xlsx');
    }
}
