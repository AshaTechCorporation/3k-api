<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Area;
use App\Models\AreaCompany;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AreaController extends Controller
{

    public function getList()
    {
        $Item = Area::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['image'] = url($Item[$i]['image']);
                $Item[$i]['products'] = Products::where('area_id', $Item[$i]['id'])->get();
                $Item[$i]['companies'] = AreaCompany::where('area_id', $Item[$i]['id'])->get();
                foreach ($Item[$i]['companies'] as $key => $value) {
                    $Item[$i]['companies'][$key]['company'] = Company::find($value['companie_id']);
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

        $type = $request->type;

        $col = array('id', 'name', 'detail', 'image', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'name', 'name', 'detail', 'image', 'create_by');

        $D = Area::select($col);

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
                $d[$i]->image = url($d[$i]->image);
                $d[$i]->products = Products::where('area_id', $d[$i]->id)->get();
                $d[$i]->companies = AreaCompany::where('area_id', $d[$i]->id)->get();
                foreach ($d[$i]->companies as $key => $value) {
                    $d[$i]->companies[$key]->company = Company::find($value['companie_id']);
                }
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
        $loginBy = $request->login_by;

        if (!isset($request->name)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = new Area();
            // $Item->companie_id = $request->companie_id;
            $Item->name = $request->name;
            $Item->detail = $request->detail;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/area/');
            }

            $Item->save();


            // foreach ($request->companies as $key => $value) {
            //     $ItemA = new AreaCompany();
            //     $ItemA->area_id = $Item->id;
            //     $ItemA->companie_id = $value['companie_id'];

            //     $ItemA->save();
            // }
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
     * @param  \App\Models\Area  $Area
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Area::where('id', $id)
            ->first();


        if (!empty($Item)) {
            $Item['image'] = url($Item['image']);

            $Item['products'] = Products::where('area_id', $Item['id'])->get();
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Area  $Area
     * @return \Illuminate\Http\Response
     */
    public function edit(Area $Area)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Area  $Shelf
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $loginBy = $request->login_by;

        if (!isset($id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = Area::find($id);
            // $Item->companie_id = $request->companie_id;
            $Item->name = $request->name;
            $Item->detail = $request->detail;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/area/');
            }

            $Item->save();


            // foreach ($request->companies as $key => $value) {
            //     $ItemA = new AreaCompany();
            //     $ItemA->area_id = $Item->id;
            //     $ItemA->companie_id = $value['companie_id'];

            //     $ItemA->save();
            // }
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
     * @param  \App\Models\Area  $Area
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Area::find($id);
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

    public function updateData(Request $request)
    {
        if (!isset($request->id)) {
            return $this->returnErrorData('[id] Data Not Found', 404);
        }

        DB::beginTransaction();

        try {
            $Item = Area::find($request->id);

            if (!$Item) {
                return $this->returnErrorData('ไม่พบรายการ', 404);
            }
            $Item->companie_id = $request->companie_id;
            $Item->name = $request->name;
            $Item->detail = $request->detail;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/area/');
            }

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
}
