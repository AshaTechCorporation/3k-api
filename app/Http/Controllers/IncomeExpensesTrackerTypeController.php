<?php
namespace App\Http\Controllers;

use App\Models\IncomeExpensesTrackerType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IncomeExpensesTrackerTypeController extends Controller
{
    /**
     * ดึงข้อมูลทั้งหมด
     */
    public function getList()
    {
        $items = IncomeExpensesTrackerType::orderBy('type', 'asc')
        ->orderBy('id', 'asc')
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
        $columns = ['id', 'name', 'detail', 'type', 'created_at', 'updated_at'];
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;
        $orderby = ['', ...$columns];
        $type = $request->type;

        $query = IncomeExpensesTrackerType::select($columns);

        if($type){
            $query->where('type',$type);
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
            $query->orderBy('id', 'desc');
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
     * สร้างข้อมูลใหม่
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:250',
            'detail' => 'nullable|string',
            'type' => 'required|in:income,expense',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        DB::beginTransaction();

        try {
            $item = new IncomeExpensesTrackerType();
            $item->name = $request->name;
            $item->detail = $request->detail;
            $item->type = $request->type;
            $item->create_by = auth()->user()->name ?? 'system';
            $item->save();

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
        $item = IncomeExpensesTrackerType::find($id);

        if (!$item) {
            return $this->returnErrorData('ไม่พบข้อมูลนี้', 404);
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $item);
    }

    /**
     * อัปเดตข้อมูล
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:250',
            'detail' => 'nullable|string',
            'type' => 'required|in:income,expense',
        ]);

        if ($validator->fails()) {
            return $this->returnErrorData($validator->errors()->first(), 400);
        }

        DB::beginTransaction();

        try {
            $item = IncomeExpensesTrackerType::find($id);
            if (!$item) {
                return $this->returnErrorData('ไม่พบข้อมูลนี้', 404);
            }

            $item->name = $request->name;
            $item->detail = $request->detail;
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
     * ลบข้อมูล (Soft Delete)
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $item = IncomeExpensesTrackerType::find($id);
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
