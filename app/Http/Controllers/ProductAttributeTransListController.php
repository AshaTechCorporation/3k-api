<?php

namespace App\Http\Controllers;

use App\Models\ProductAttributeTransList;
use Illuminate\Http\Request;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductAttributeTransListController extends Controller
{
   public function getList()
    {
        $Item = ProductAttributeTrans::get()->toArray();

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

        $col = array(
            'id',
            'product_attribute_id',
            'qty',
            'purpose',
            'remark',
            'status',
            'create_by',
            'update_by',
            'created_at',
            'updated_at'
        );

        $orderby = array(
            '',                 // สำหรับลำดับ No
            'product_attribute_id',
            'qty',
            'purpose',
            'remark',
            'status',
            'create_by',
            'update_by',
            'created_at',
            'updated_at'
        );

        $D = ProductAttributeTrans::select($col);

        if ($orderby[$order[0]['column']]) {
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
            $No = (($page - 1) * $length);
            foreach ($d as $index => $item) {
                $item->No = ++$No;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }

    public function store(Request $request)
    {
        if (!isset($request->product_attribute_id) || !isset($request->qty)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้ครบถ้วน', 404);
        }

        DB::beginTransaction();

        try {
            $Item = new ProductAttributeTrans();
            $Item->product_attribute_id = $request->product_attribute_id;
            $Item->qty = $request->qty;
            $Item->purpose = $request->purpose;
            $Item->remark = $request->remark;
            $Item->status = $request->status ?? 'draft';
            $Item->create_by = $request->login_by ?? 'system';
            $Item->save();

            // log
            $userId = $request->login_by ?? 'system';
            $type = 'เพิ่มรายการ';
            $desc = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' เบิกสินค้า ID: ' . $Item->product_attribute_id;
            $this->Log($userId, $desc, $type);

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $Item = ProductAttributeTrans::find($id);

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function update(Request $request, $id)
    {
        if (!isset($id)) {
            return $this->returnErrorData('ไม่พบข้อมูลที่ต้องการอัปเดต', 404);
        }

        DB::beginTransaction();

        try {
            $Item = ProductAttributeTrans::find($id);
            $Item->qty = $request->qty ?? $Item->qty;
            $Item->purpose = $request->purpose ?? $Item->purpose;
            $Item->remark = $request->remark ?? $Item->remark;
            $Item->status = $request->status ?? $Item->status;
            $Item->update_by = $request->login_by ?? 'system';
            $Item->save();

            // log
            $userId = $request->login_by ?? 'system';
            $type = 'อัปเดตรายการ';
            $desc = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' เบิกสินค้า ID: ' . $Item->product_attribute_id;
            $this->Log($userId, $desc, $type);

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $Item = ProductAttributeTrans::find($id);
            $Item->delete();

            $userId = auth()->user()->name ?? 'system';
            $type = 'ลบรายการ';
            $desc = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' เบิกสินค้า ID: ' . $id;
            $this->Log($userId, $desc, $type);

            DB::commit();

            return $this->returnUpdate('ลบข้อมูลเรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage(), 500);
        }
    }
}
