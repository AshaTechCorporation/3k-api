<?php

namespace App\Http\Controllers;

use App\Models\promotion;
use App\Models\PromotionList;
use App\Models\Discount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function getList()
    {
        $Item = promotion::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['promotion_lists'] = PromotionList::where('promotion_id',$Item[$i]['id'])->get();
                foreach ($Item[$i]['promotion_lists'] as $key => $value) {
                    $Item[$i]['promotion_lists'][$key]['discount'] = Discount::find($value['discount_id']);
                }
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


        $col = array('id', 'name', 'remark','start','end', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'name', 'remark','start','end', 'create_by', 'update_by', 'created_at', 'updated_at');


        $D = promotion::select($col);


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
        if (!isset($request->name)) {
            return $this->returnError('กรุณาระบุชื่อประเภทงานให้เรียบร้อย', 404);
        }
        $remark = $request->remark;
        $name = $request->name;
        $start = $request->start;
        $end = $request->end;

        $checkName = promotion::where(function ($query) use ($remark, $name) {
            $query->orWhere('name', $name);
        })
            ->first();

        if ($checkName) {
            return $this->returnError($name . ' มีข้อมูลในระบบแล้ว');
        }

        DB::beginTransaction();

        try {

            $Item = new promotion();
            $Item->remark = $remark;
            $Item->name = $name;
            $Item->start = $start;
            $Item->end = $end;

            $Item->create_by = "Admin";
            $Item->updated_at = Carbon::now()->toDateTimeString();

            $Item->save();

            foreach ($request->promotion_lists as $key => $value) {

                $ItemC = new PromotionList();
                $ItemC->promotion_id = $Item->id;
                $ItemC->discount_id = $value['discount_id'];
                $ItemC->save();
            }

            //log
            $userId = "Admin";
            $type = 'เพิ่มประเภทงาน';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $name;
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
     * @param  \App\Models\promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = promotion::where('id', $id)
            ->first();
            if($Item){
                $Item->promotiom_lists = PromotionList::where('promotion_id',$Item->id)->get();
                foreach ($Item->promotiom_lists as $key => $value) {
                    $Item->promotiom_lists[$key]->discount = Discount::find($value['discount_id']);
                }
            }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function edit(promotion $promotion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!isset($request->name)) {
            return $this->returnError('กรุณาระบุชื่อประเภทงานให้เรียบร้อย', 404);
        }

        $remark = $request->remark;
        $name = $request->name;
        $start = $request->start;
        $end = $request->end;

        if (!isset($id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = promotion::find($id);
            $Item->remark = $remark;
            $Item->name = $name;
            $Item->start = $start;
            $Item->end = $end;
            $Item->updated_at = Carbon::now()->toDateTimeString();

            $Item->save();

            $ItemDel = PromotionList::where('promotion_id', $Item->id)
            ->delete();

            foreach ($request->promotion_lists as $key => $value) {

       

                if($ItemDel)
                {
                    $ItemC = new PromotionList();
                    $ItemC->promotion_id = $Item->id;
                    $ItemC->discount_id = $value['discount_id'];
                    $ItemC->save();
                }

                
            }
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = promotion::find($id);
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
}
