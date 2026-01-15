<?php

namespace App\Http\Controllers;

use App\Models\ArAp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class ArApController extends Controller
{
    public function getList($date)
    {
        $items = ArAp::where('transaction_date',$date)->orderBy('transaction_date', 'desc')->get();

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
        $status = $request->status;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $partner_type = $request->partner_type;
        $partner_name = $request->partner_name;
        $col = [
            'id', 'code', 'partner_type', 'partner_name', 
            'transaction_date','receipe_date', 'direction', 'amount', 
            'status', 'create_by', 'update_by', 'created_at', 'updated_at'
        ];

        $orderby = [
            '', 'code', 'partner_type', 'partner_name', 
            'transaction_date','receipe_date', 'direction', 'amount', 
            'status', 'create_by'
        ];

        $D = ArAp::select($col);

        if($status){
            $D->where('status',$status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $D->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $D->whereDate('transaction_date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $D->whereDate('transaction_date', '<=', $request->end_date);
        }

        if($partner_type){
            $D->where('partner_type',$partner_type);
        }

        if($partner_name){
            $D->where('partner_name',$partner_name);
        }

        // กำหนดลำดับการเรียงข้อมูล
        if (!empty($orderby[$order[0]['column']])) {
            $D->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        }

        // การค้นหาจากค่า search
        if (!empty($search['value'])) {
            $D->where(function ($query) use ($search, $col) {
                foreach ($col as $c) {
                    $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        $d = $D->paginate($length, ['*'], 'page', $page);

        if ($d->isNotEmpty()) {
            $No = (($page - 1) * $length);

            foreach ($d as $i => $item) {
                $No++;
                $item->No = $No;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }


    public function show($id)
    {
        $item = ArAp::find($id);

        if (!$item) {
            return $this->returnErrorData('ไม่พบข้อมูล', 404);
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $item);
    }

    public function store(Request $request)
    {
        $loginBy = $request->login_by;

        if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        $validator = Validator::make($request->all(), [
            'partner_type'    => 'required|in:debtor,creditor',
            'partner_name'    => 'required|string|max:255',
            'transaction_date'=> 'required|date',
            'direction'       => 'required|in:in,out',
            'amount'          => 'required|numeric|min:0',
            'status'          => 'in:pending,paid',
        ]);

        if ($validator->fails()) {
            return $this->returnValidate($validator->errors());
        }

        // กำหนด prefix ตามประเภท
        $prefix = $request->partner_type === 'debtor' ? '#AR-' : '#AP-';

        // generate code
        $id = IdGenerator::generate([
            'table' => 'ar_aps',
            'field' => 'code',
            'length' => 9,
            'prefix' => $prefix
        ]);

        // สร้างข้อมูลใหม่
        $item = new ArAp();
        $item->code = $id;
        $item->partner_type = $request->partner_type;
        $item->partner_name = $request->partner_name;
        $item->transaction_date = $request->transaction_date;
        $item->receipe_date = $request->receipe_date;
        $item->direction = $request->direction;
        $item->amount = $request->amount;
        $item->status = $request->status ?? 'pending';
        $item->description = $request->description ?? null;
        $item->create_by = $loginBy->username ?? 'system';

        $item->save();

        return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', $item);
    }


    public function update(Request $request, $id)
    {
        $loginBy = $request->login_by;

        if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        $item = ArAp::find($id);
        if (!$item) {
            return $this->returnErrorData('ไม่พบข้อมูล', 404);
        }

        $validator = Validator::make($request->all(), [
            'partner_type'    => 'required|in:debtor,creditor',
            'partner_name'    => 'required|string|max:255',
            'transaction_date'=> 'required|date',
            'direction'       => 'required|in:in,out',
            'amount'          => 'required|numeric|min:0',
            'status'          => 'in:pending,paid',
        ]);

        if ($validator->fails()) {
            return $this->returnValidate($validator->errors());
        }

        $item->partner_type = $request->partner_type;
        $item->partner_name = $request->partner_name;
        $item->transaction_date = $request->transaction_date;
        $item->receipe_date = $request->receipe_date;
        $item->direction = $request->direction;
        $item->amount = $request->amount;
        $item->status = $request->status ?? $item->status;
        $item->description = $request->description ?? $item->description;
        $item->update_by = $loginBy->username ?? 'system';

        $item->save();

        return $this->returnSuccess('อัปเดตข้อมูลสำเร็จ', $item);
    }


    public function destroy($id)
    {
        $item = ArAp::find($id);

        if (!$item) {
            return $this->returnErrorData('ไม่พบข้อมูล', 404);
        }

        $item->delete();

        return $this->returnSuccess('ลบข้อมูลสำเร็จ');
    }

    public function updateStatus(Request $request, $id)
    {
        $loginBy = $request->login_by;

        if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,paid',
        ]);

        if ($validator->fails()) {
            return $this->returnValidate($validator->errors());
        }

        $item = ArAp::find($id);
        if (!$item) {
            return $this->returnErrorData('ไม่พบข้อมูล', 404);
        }

        $item->status = $request->status;
        $item->update_by = $loginBy->username ?? 'system';
        $item->save();

        return $this->returnSuccess('อัปเดตสถานะสำเร็จ', $item);
    }
}
