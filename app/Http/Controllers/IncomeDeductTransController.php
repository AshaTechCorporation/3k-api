<?php

namespace App\Http\Controllers;

use App\Models\IncomeDeductTrans;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class IncomeDeductTransController extends Controller
{
    public function getList($userid, $month)
    {
        $items = IncomeDeductTrans::where('user_id', $userid)
            ->whereMonth('transaction_date', $month)
            ->get()
            ->toArray();

        if (!empty($items)) {
            foreach ($items as $i => &$item) {
                $item['No'] = $i + 1;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $items);
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

        $col = ['id', 'transaction_date', 'type', 'type_ref_id', 'category', 'description', 'amount', 'payment_method', 'attachment', 'user_id', 'created_at', 'updated_at'];
        $orderby = ['id', 'transaction_date', 'type', 'type_ref_id', 'category', 'description', 'amount', 'payment_method', 'attachment', 'user_id', 'created_at', 'updated_at'];

        $D = IncomeDeductTrans::select($col);

        if ($month) {
            $D->whereMonth('transaction_date', $month);
        }

        if ($year) {
            $D->whereYear('transaction_date', $year);
        }

        if ($orderby[$order[0]['column']] ?? false) {
            $D->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if (!empty($search['value'])) {
            $D->where(function ($query) use ($search, $col) {
                foreach ($col as $c) {
                    $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        $data = $D->paginate($length, ['*'], 'page', $page);

        if ($data->isNotEmpty()) {
            $no = ($page - 1) * $length;
            foreach ($data as $i => $item) {
                $data[$i]->No = ++$no;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }

    public function store(Request $request)
    {

        $loginBy = $request->login_by;

        $validator = Validator::make($request->all(), [
            'transaction_date' => 'required|date',
            'type' => 'required|in:income,expense',
            'type_ref_id' => 'required|integer',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer',
            'attachment' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->returnError('ข้อมูลไม่ครบถ้วน', 422, $validator->errors());
        }

        DB::beginTransaction();

        try {
            $item = new IncomeDeductTrans();
            $item->transaction_date = $request->transaction_date;
            $item->type = $request->type;
            $item->type_ref_id = $request->type_ref_id;
            $item->category = $request->category;
            $item->description = $request->description;
            $item->amount = $request->amount;
            $item->payment_method = $request->payment_method;
            $item->attachment = $request->attachment;
            $item->user_id = $loginBy->id;

            $item->save();

            $userId = "admin";
            $type = 'เพิ่มรายการ';
            $desc = "ผู้ใช้งาน $userId ได้ทำการเพิ่มรายการ {$item->id}";
            $this->Log($userId, $desc, $type);

            DB::commit();

            return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', $item);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnError('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $item = IncomeDeductTrans::find($id);

        if (!$item) {
            return $this->returnError('ไม่พบข้อมูลที่ต้องการ', 404);
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $item);
    }

    public function update(Request $request, $id)
    {
        $loginBy = $request->login_by;

        $item = IncomeDeductTrans::find($id);

        if (!$item) {
            return $this->returnError('ไม่พบข้อมูลที่ต้องการอัปเดต', 404);
        }

        DB::beginTransaction();

        try {
            $item->transaction_date = $request->transaction_date;
            $item->type = $request->type;
            $item->type_ref_id = $request->type_ref_id;
            $item->category = $request->category;
            $item->description = $request->description;
            $item->amount = $request->amount;
            $item->payment_method = $request->payment_method;
            $item->attachment = $request->attachment;
            $item->user_id = $loginBy->id;

            $item->save();

            $userId = "admin";
            $type = 'อัปเดตรายการ';
            $desc = "ผู้ใช้งาน $userId ได้ทำการอัปเดตรายการ {$item->id}";
            $this->Log($userId, $desc, $type);

            DB::commit();

            return $this->returnSuccess('อัปเดตข้อมูลสำเร็จ', $item);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnError('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $item = IncomeDeductTrans::find($id);

            if (!$item) {
                return $this->returnError('ไม่พบรายการที่ต้องการลบ', 404);
            }

            $item->delete();

            $userId = "admin";
            $type = 'ลบรายการ';
            $desc = "ผู้ใช้งาน $userId ได้ทำการลบรายการ $id";
            $this->Log($userId, $desc, $type);

            DB::commit();

            return $this->returnUpdate('ลบข้อมูลสำเร็จ');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnError('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage());
        }
    }
}
