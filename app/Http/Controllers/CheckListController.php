<?php

namespace App\Http\Controllers;

use App\Models\CheckList;
use App\Models\CheckListCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CheckListController extends Controller
{
    public function getList()
    {
        $items = CheckList::get()->toArray();

        foreach ($items as $i => &$item) {
            $item['No'] = $i + 1;
            $item['check_list_categorys'] = CheckListCategory::where('check_list_id',$item['id'])->get();
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

        $col = ['id', 'name', 'detail', 'create_by', 'update_by', 'created_at', 'updated_at'];
        $orderby = ['', 'name', 'detail', 'create_by'];

        $query = CheckList::select($col);

        if (!empty($order[0]['column']) && isset($orderby[$order[0]['column']])) {
            $query->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if (!empty($search['value'])) {
            $query->where(function ($q) use ($search, $col) {
                foreach ($col as $field) {
                    $q->orWhere($field, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        $results = $query->paginate($length, ['*'], 'page', $page);

        foreach ($results as $i => $result) {
            $result->No = (($page - 1) * $length) + $i + 1;
            $result->check_list_categorys = CheckListCategory::where('check_list_id',$result->id)->get();
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $results);
    }

    public function store(Request $request)
    {
        $loginBy = $request->login_by;

        if (!$request->name) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้ครบถ้วน', 404);
        }

        DB::beginTransaction();

        try {
            $item = new CheckList();
            $item->name = $request->name;
            $item->detail = $request->detail;
            $item->create_by = $loginBy->id;

            $item->save();

            if (isset($request->category_products)) {
                foreach ($request->category_products as $category_product) {
                    $checkList = new CheckListCategory();
                    $checkList->check_list_id = $item->id;
                    $checkList->category_product_id = $category_product;
                    $checkList->save();
                }
            }

            $this->Log($loginBy->id, 'เพิ่ม Checklist: ' . $item->name, 'เพิ่มรายการ');

            DB::commit();
            return $this->returnSuccess('บันทึกข้อมูลสำเร็จ', $item);

        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $item = CheckList::find($id);

        if($item){
            $item->check_list_categorys = CheckListCategory::where('check_list_id',$item->id)->get();
        }

        return $item
            ? $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $item)
            : $this->returnErrorData('ไม่พบข้อมูล', 404);
    }

    public function update(Request $request, $id)
    {
        $loginBy = $request->login_by;

        if (!$request->name) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้ครบถ้วน', 404);
        }

        DB::beginTransaction();

        try {
            $item = CheckList::find($id);

            if (!$item) {
                return $this->returnErrorData('ไม่พบข้อมูลที่ต้องการแก้ไข', 404);
            }

            $item->name = $request->name;
            $item->detail = $request->detail;
            $item->update_by = $loginBy->id;

            $item->save();

            // ลบรายการ category เดิมทั้งหมดก่อนเพิ่มใหม่
            CheckListCategory::where('check_list_id', $item->id)->delete();

            if (isset($request->category_products)) {
                foreach ($request->category_products as $category_product) {
                    $checkList = new CheckListCategory();
                    $checkList->check_list_id = $item->id;
                    $checkList->category_product_id = $category_product;
                    $checkList->save();
                }
            }

            $this->Log($loginBy->id, 'แก้ไข Checklist: ' . $item->name, 'แก้ไขรายการ');

            DB::commit();
            return $this->returnSuccess('แก้ไขข้อมูลสำเร็จ', $item);

        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }


    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $item = CheckList::find($id);

            if (!$item) {
                return $this->returnErrorData('ไม่พบข้อมูล', 404);
            }

            $item->delete();
            $this->Log('admin', 'ลบ Checklist: ' . $item->name, 'ลบรายการ');

            DB::commit();
            return $this->returnSuccess('ลบข้อมูลสำเร็จ');

        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->returnErrorData('เกิดข้อผิดพลาด: ' . $e->getMessage(), 500);
        }
    }
}