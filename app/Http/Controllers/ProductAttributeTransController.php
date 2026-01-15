<?php

namespace App\Http\Controllers;

use App\Models\ProductAttributeTrans;
use App\Models\ProductAttributeTransList;
use App\Models\CategoryAttribute;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeImages;
use Illuminate\Http\Request;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductAttributeTransController extends Controller
{
    public function getList()
    {
        $Item = ProductAttributeTrans::with([
                'product_attribute_trans_lists.product_attribute',
                'job.product.area',
                'job.product.brand',
                'job.product.brandModel',
                'job.product.cc',
                'job.product.color',
                'job.images',
                'job.otherExpenses',
                'job.steps' => function ($query) {
                    $query->with([
                        'stepJobTypeLists' => function ($stepQuery) {
                            $stepQuery->with([
                                'productAttributes.product_attribute', // ← เพิ่มตรงนี้
                                'productAttributeOthers',
                                'expenses',
                                'workType',
                                'images',
                            ]);
                        }
                    ]);
                }
            ])->get()->toArray();

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
            'code',
            'job_id',
            'remark',
            'status',
            'create_by',
            'update_by',
            'created_at',
            'updated_at'
        );

        $orderby = array(
            '',                 // สำหรับลำดับ No
            'code',
            'job_id',
            'remark',
            'status',
            'create_by',
            'update_by',
            'created_at',
            'updated_at'
        );

        $D = ProductAttributeTrans::with([
                'product_attribute_trans_lists.product_attribute',
                'job.product.area',
                'job.product.brand',
                'job.product.brandModel',
                'job.product.cc',
                'job.product.color',
                'job.images',
                'job.otherExpenses',
                'job.steps' => function ($query) {
                    $query->with([
                        'stepJobTypeLists' => function ($stepQuery) {
                            $stepQuery->with([
                                'productAttributes.product_attribute', // ← เพิ่มตรงนี้
                                'productAttributeOthers',
                                'expenses',
                                'workType',
                                'images',
                            ]);
                        }
                    ]);
                }
            ])->select($col);

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
        $loginBy = $request->login_by ?? 'system';

        if (empty($request->items)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้ครบถ้วน', 404);
        }

        DB::beginTransaction();

        try {
            // สร้างหัวเอกสาร
            $head = new ProductAttributeTrans();

            $prefix = "#WD-";
            $id = IdGenerator::generate(['table' => 'product_attribute_trans', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);

            $head->code = $id;
            $head->job_id = $request->job_id;
            $head->remark = $request->remark;
            $head->status = $request->status ?? 'pending';
            $head->create_by = $loginBy;
            $head->save();

            // วนรายการสินค้า
            foreach ($request->items as $row) {
                $product = ProductAttribute::find($row['product_attribute_id']);
                if (!$product) {
                    throw new \Exception('ไม่พบสินค้า ID: ' . $row['product_attribute_id']);
                }

                // ตรวจสอบสต๊อก
                if ($product->qty < $row['qty']) {
                    throw new \Exception('สต๊อกไม่พอสำหรับสินค้า ' . $product->id);
                }

                // หักสต๊อก
                $product->qty -= $row['qty'];
                $product->save();

                // บันทึกรายการ
                $list = new ProductAttributeTransList();
                $list->product_attribute_tran_id = $head->id;
                $list->product_attribute_id = $row['product_attribute_id'];
                $list->qty = $row['qty'];
                $list->step_jobs_type_list_id = $row['step_jobs_type_list_id'] ?? null;
                $list->work_type_id = $row['work_type_id'] ?? null;
                $list->create_by = $loginBy;
                $list->save();
            }

            // log
            $desc = "ผู้ใช้งาน $loginBy ได้ทำการเบิกสินค้า เอกสารเลขที่: {$head->code}";
            $this->Log($loginBy, $desc, 'เบิกสินค้า');

            DB::commit();
            return $this->returnSuccess('บันทึกข้อมูลเรียบร้อยแล้ว', $head);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }


    public function show($id)
    {
        $Item = ProductAttributeTrans::with([
                'product_attribute_trans_lists.product_attribute',
                'job.product.area',
                'job.product.brand',
                'job.product.brandModel',
                'job.product.cc',
                'job.product.color',
                'job.images',
                'job.otherExpenses',
                'job.steps' => function ($query) {
                    $query->with([
                        'stepJobTypeLists' => function ($stepQuery) {
                            $stepQuery->with([
                                'productAttributes.product_attribute', // ← เพิ่มตรงนี้
                                'productAttributeOthers',
                                'expenses',
                                'workType',
                                'images',
                            ]);
                        }
                    ]);
                }
            ])->find($id);

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function update(Request $request, $id)
    {
        $loginBy = $request->login_by ?? 'system';

        if (empty($id) || empty($request->items)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้ครบถ้วน', 404);
        }

        DB::beginTransaction();

        try {
            $head = ProductAttributeTrans::find($id);
            if (!$head) {
                return $this->returnErrorData('ไม่พบข้อมูลที่ต้องการแก้ไข', 404);
            }

            // 1. คืนสต๊อกจากรายการเก่า
            $oldItems = ProductAttributeTransList::where('product_attribute_tran_id', $id)->get();
            foreach ($oldItems as $item) {
                $product = ProductAttribute::find($item->product_attribute_id);
                if ($product) {
                    $product->qty += $item->qty; // คืนสต๊อก
                    $product->save();
                }
            }

            // 2. ลบรายการเก่า
            ProductAttributeTransList::where('product_attribute_tran_id', $id)->delete();

            // 3. แก้ไขหัวเอกสาร
            $head->job_id = $request->job_id;
            $head->remark = $request->remark;
            $head->status = $request->status ?? $head->status;
            $head->update_by = $loginBy;
            $head->save();

            // 4. บันทึกรายการใหม่
            foreach ($request->items as $row) {
                $product = ProductAttribute::find($row['product_attribute_id']);
                if (!$product) {
                    throw new \Exception('ไม่พบสินค้า ID: ' . $row['product_attribute_id']);
                }

                if ($product->qty < $row['qty']) {
                    throw new \Exception('สต๊อกไม่พอสำหรับสินค้า ID: ' . $product->id);
                }

                $product->qty -= $row['qty'];
                $product->save();

                $list = new ProductAttributeTransList();
                $list->product_attribute_tran_id = $head->id;
                $list->product_attribute_id = $row['product_attribute_id'];
                $list->qty = $row['qty'];
                $list->step_jobs_type_list_id = $row['step_jobs_type_list_id'] ?? null;
                $list->work_type_id = $row['work_type_id'] ?? null;
                $list->create_by = $loginBy;
                $list->save();
            }

            // 5. log
            $desc = "ผู้ใช้งาน $loginBy ได้แก้ไขรายการเบิกสินค้า เลขที่: {$head->code}";
            $this->Log($loginBy, $desc, 'แก้ไขรายการเบิกสินค้า');

            DB::commit();
            return $this->returnSuccess('แก้ไขข้อมูลเรียบร้อยแล้ว', $head);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $Item = ProductAttributeTrans::find($id);
            if (!$Item) {
                return $this->returnErrorData('ไม่พบข้อมูลที่ต้องการลบ', 404);
            }

            // 1. คืนสต๊อกจากรายการที่เคยเบิก
            $lists = ProductAttributeTransList::where('product_attribute_tran_id', $id)->get();

            foreach ($lists as $list) {
                $product = ProductAttribute::find($list->product_attribute_id);
                if ($product) {
                    $product->qty += $list->qty; // คืนสต๊อก
                    $product->save();
                }
            }

            // 2. ลบรายการย่อยก่อน (soft delete)
            ProductAttributeTransList::where('product_attribute_tran_id', $id)->delete();

            // 3. ลบหัวเอกสาร
            $Item->delete();

            // 4. log
            $userId = auth()->user()->name ?? 'system';
            $type = 'ลบรายการ';
            $desc = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' เบิกสินค้า เลขที่: ' . $Item->code;
            $this->Log($userId, $desc, $type);

            DB::commit();

            return $this->returnUpdate('ลบข้อมูลและคืนสต๊อกเรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage(), 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $loginBy = $request->login_by ?? 'system';
        $newStatus = $request->status;

        if (!in_array($newStatus, ['draft', 'approved', 'rejected', 'completed', 'cancelled'])) {
            return $this->returnErrorData('สถานะที่ระบุไม่ถูกต้อง', 400);
        }

        DB::beginTransaction();

        try {
            $Item = ProductAttributeTrans::find($id);
            if (!$Item) {
                return $this->returnErrorData('ไม่พบเอกสารที่ต้องการอัปเดต', 404);
            }

            // ถ้าเปลี่ยนสถานะเป็น cancelled และเดิมไม่ใช่ cancelled → คืนสต๊อก
            if ($newStatus === 'cancelled' && $Item->status !== 'cancelled') {
                $lists = ProductAttributeTransList::where('product_attribute_tran_id', $Item->id)->get();

                foreach ($lists as $list) {
                    $product = ProductAttribute::find($list->product_attribute_id);
                    if ($product) {
                        $product->qty += $list->qty;
                        $product->save();
                    }
                }
            }

            // อัปเดตสถานะ
            $Item->status = $newStatus;
            $Item->update_by = $loginBy;
            $Item->save();

            // log
            $desc = "ผู้ใช้งาน $loginBy ได้อัปเดตสถานะเอกสารเบิกสินค้าเป็น [$newStatus] เลขที่: {$Item->code}";
            $this->Log($loginBy, $desc, 'อัปเดตสถานะ');

            DB::commit();

            return $this->returnSuccess("อัปเดตสถานะเป็น $newStatus เรียบร้อยแล้ว", $Item);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }


}
