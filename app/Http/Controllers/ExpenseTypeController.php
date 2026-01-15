<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExpenseTypeController extends Controller
{
    public function getList()
    {
        $Item = ExpenseType::get()->toArray();

        if (!empty($Item)) {
            foreach ($Item as $i => &$item) {
                $item['No'] = $i + 1;
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

        $col = ['id', 'name','detail', 'create_by', 'update_by', 'created_at', 'updated_at'];
        $orderby = ['', 'name','detail', 'create_by'];

        $D = ExpenseType::select($col);

        if (!empty($orderby[$order[0]['column']])) {
            $D->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if (!empty($search['value'])) {
            $D->where(function ($query) use ($search, $col) {
                foreach ($col as $c) {
                    $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        $d = $D->paginate($length, ['*'], 'page', $page);

        if ($d->isNotEmpty()) {
            $No = ($page - 1) * $length;
            foreach ($d as $i => $row) {
                $row->No = ++$No;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }

    public function store(Request $request)
    {
        $loginBy = $request->login_by;

        if (!isset($request->name)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        }

        DB::beginTransaction();
        try {
            $Item = new ExpenseType();
            $Item->name = $request->name;
            $Item->detail = $request->detail;

            $Item->save();

            $this->Log('admin', 'เพิ่มรายการ ' . $request->name, 'เพิ่มรายการ');

            DB::commit();
            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    public function show($id)
    {
        $Item = ExpenseType::find($id);
        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function update(Request $request, $id)
    {
        $loginBy = $request->login_by;

        if (!isset($id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        }

        DB::beginTransaction();
        try {
            $Item = ExpenseType::find($id);
            $Item->name = $request->name;
            $Item->detail = $request->detail;

            $Item->save();

            $this->Log('admin', 'แก้ไขรายการ ' . $request->name, 'แก้ไขรายการ');

            DB::commit();
            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $Item = ExpenseType::find($id);
            $Item->delete();

            $this->Log('admin', 'ลบรายการ ' . $Item->name, 'ลบผู้ใช้งาน');

            DB::commit();
            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }
}
