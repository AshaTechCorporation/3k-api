<?php

namespace App\Http\Controllers;

use App\Models\IncomeExpensesTracker;
use App\Models\IncomeExpensesTrackerType;
use App\Models\ProductAttribute;
use App\Models\ArAp;
use App\Models\JobsExpensesList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IncomeExpensesTrackerController extends Controller
{
    /**
     * ดึงข้อมูลทั้งหมด
     */
    public function getList($date)
    {
        $items = IncomeExpensesTracker::with('type')
            ->whereDate('date', $date)
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();

        foreach ($items as $index => $item) {
            $items[$index]['No'] = $index + 1;
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $items);
    }

    /**
     * ดึงข้อมูลแบบแบ่งหน้า
     */
    public function getPage(Request $request)
    {
        $columns = ['id', 'income_expenses_tracker_type_id', 'name', 'detail', 'image', 'date', 'amount', 'type', 'created_at', 'updated_at'];
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;
        $orderby = ['', ...$columns];
        $date = $request->date;

        $query = IncomeExpensesTracker::with('type')->select($columns);

        if($date){
             $query->whereDate('date', $date);
             $query->orderBy('date', 'desc');
        }

        if (!empty($search['value'])) {
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $col) {
                    $q->orWhere($col, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        if (!empty($orderby[$order[0]['column']])) {
            $query->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        } else {
            $query->orderBy('date', 'desc');
        }

        $data = $query->paginate($length, ['*'], 'page', $page);

        if ($data->isNotEmpty()) {
            $no = ($page - 1) * $length;
            foreach ($data as $index => $item) {
                $data[$index]->No = ++$no;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }

    /**
     * เพิ่มรายการใหม่
     */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'income_expenses_tracker_type_id' => 'required|exists:income_expenses_tracker_types,id',
    //         'name' => 'nullable|string|max:250',
    //         'detail' => 'nullable|string',
    //         'image' => 'nullable|string|max:250',
    //         'date' => 'required|date',
    //         'amount' => 'required|numeric|min:0',
    //         'type' => 'required|in:income,expense',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->returnErrorData($validator->errors()->first(), 400);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $item = new IncomeExpensesTracker();
    //         $item->income_expenses_tracker_type_id = $request->income_expenses_tracker_type_id;
    //         $item->name = $request->name;
    //         $item->detail = $request->detail;
    //         $item->image = $request->image;
    //         $item->date = $request->date;
    //         $item->amount = $request->amount;
    //         $item->type = $request->type;
    //         $item->create_by = auth()->user()->name ?? 'system';
    //         $item->save();

    //         DB::commit();

    //         return $this->returnSuccess('เพิ่มข้อมูลสำเร็จ', $item);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
    //     }
    // }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'income_expenses_tracker_type_id' => 'required|exists:income_expenses_tracker_types,id',
            'partner_name' => 'nullable|string|max:250',
            'payment_type' => 'required|in:credit,cash,transfer',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'items' => 'nullable|array',
            'items.*.product_attribute_id' => 'nullable|integer|exists:product_attributes,id',
            'items.*.qty' => 'nullable|integer|min:1',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'car_id' => 'nullable|integer|exists:products,id',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        DB::beginTransaction();

        try {
            // 1️⃣ บันทึก income_expenses_tracker
            $item = new IncomeExpensesTracker();
            $item->income_expenses_tracker_type_id = $request->income_expenses_tracker_type_id;
            $item->name = $request->transaction_type;
            $item->payment_type = $request->payment_type;
            $item->detail = $request->description ?? '';
            $item->image = $request->image ?? '';
            $item->date = $request->date;
            $item->amount = $request->amount;
            $item->type = $request->type;
            $item->car_id = $request->car_id;
            $item->create_by = 'system';
            $item->save();

            // 2️⃣ บันทึก jobs_expenses_lists (ถ้ามี car_id)
            // if ($request->car_id) {
            //     $jobExpense = new JobsExpensesList();
            //     $jobExpense->job_id = null;
            //     $jobExpense->car_id = $request->car_id;
            //     $jobExpense->name = $item->detail;
            //     $jobExpense->amount = $item->amount;
            //     $jobExpense->create_by = auth()->user()->name ?? 'system';
            //     $jobExpense->save();
            // }

            // 3️⃣ ถ้าเป็น purchase_parts → เพิ่ม stock และเจ้าหนี้ถ้า credit
            if ($request->items) {
                foreach ($request->items as $part) {
                    // 3.1 → อัพเดท stock
                    $partItem = ProductAttribute::find($part['product_attribute_id']);
                    if ($partItem) {
                        $partItem->qty += $part['qty'];
                        $partItem->cost = $part['unit_cost']; // อัพเดทราคาทุนล่าสุด
                        $partItem->update_by = auth()->user()->name ?? 'system';
                        $partItem->save();
                    }
                }

                // 3.2 → ถ้า credit → เพิ่มเจ้าหนี้
                if ($request->payment_type === 'credit') {
                    $arap = new ArAp();
                    $arap->code = 'AP-' . date('YmdHis');
                    $arap->partner_type = $request->partner_type;
                    $arap->partner_name = $request->partner_name;
                    $arap->transaction_date = $request->date;
                    $arap->receipe_date = null;
                    $arap->direction = 'out';
                    $arap->amount = $request->amount;
                    $arap->status = 'pending';
                    $arap->description = 'เจ้าหนี้ - ซื้ออะไหล่';
                    $arap->create_by = auth()->user()->name ?? 'system';
                    $arap->save();
                }
            }else{
                $arap = new ArAp();
                $arap->code = 'AR-' . date('YmdHis');
                $arap->partner_type = $request->partner_type;
                $arap->partner_name = $request->partner_name;
                $arap->transaction_date = $request->date;
                $arap->receipe_date = null;
                $arap->direction = 'out';
                $arap->amount = $request->amount;
                $arap->status = 'pending';
                $arap->description = 'ลูกหนี้ - พนักงานยืมเงิน';
                $arap->create_by = auth()->user()->name ?? 'system';
                $arap->save();
            }

            DB::commit();

            return $this->returnSuccess('เพิ่มข้อมูลสำเร็จ', $item);

        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }


    /**
     * แสดงข้อมูลรายการเดียว
     */
    public function show($id)
    {
        $item = IncomeExpensesTracker::with('type')->find($id);

        if (!$item) {
            return $this->returnErrorData('ไม่พบข้อมูลนี้', 404);
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $item);
    }

    /**
     * อัปเดตรายการ
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'income_expenses_tracker_type_id' => 'required|exists:income_expenses_tracker_types,id',
            'name' => 'nullable|string|max:250',
            'detail' => 'nullable|string',
            'image' => 'nullable|string|max:250',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        DB::beginTransaction();

        try {
            $item = IncomeExpensesTracker::find($id);
            if (!$item) {
                return $this->returnErrorData('ไม่พบข้อมูลนี้', 404);
            }

            $item->income_expenses_tracker_type_id = $request->income_expenses_tracker_type_id;
            $item->name = $request->name;
            $item->detail = $request->detail;
            $item->image = $request->image;
            $item->date = $request->date;
            $item->amount = $request->amount;
            $item->type = $request->type;
            $item->update_by = auth()->user()->name ?? 'system';
            $item->save();

            DB::commit();

            return $this->returnSuccess('อัปเดตข้อมูลสำเร็จ', $item);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    /**
     * ลบรายการ
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $item = IncomeExpensesTracker::find($id);
            if (!$item) {
                return $this->returnErrorData('ไม่พบข้อมูลนี้', 404);
            }

            $item->delete();

            DB::commit();

            return $this->returnSuccess('ลบข้อมูลสำเร็จ');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }
}
