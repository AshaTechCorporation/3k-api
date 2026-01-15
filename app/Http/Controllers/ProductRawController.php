<?php

namespace App\Http\Controllers;

use App\Models\ProductRaw;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductRawController extends Controller
{
    public function getList($id)
    {
        $Item = ProductRaw::where('product_id', $id)->get();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['product'] = Products::find($Item[$i]['product_id']);
                $Item[$i]['product_raw'] = Products::find($Item[$i]['raw_id']);
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


        $col = array('id', 'category_product_id', 'sub_category_product_id', 'area_id', 'shelve_id', 'floor_id', 'channel_id', 'name', 'detail', 'code', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'category_product_id', 'sub_category_product_id', 'area_id', 'shelve_id', 'floor_id', 'channel_id', 'name', 'detail', 'code', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = Products::select($col);


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
                $d[$i]->product = Products::find($d[$i]->product_id);
                $d[$i]->product_raw = Products::find($d[$i]->raw_id);

                for ($n = 0; $n <= count($d[$i]->images) - 1; $n++) {
                    $d[$i]->images[$n]->image = url($d[$i]->images[$n]->image);
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

        DB::beginTransaction();

        if (!isset($request->product_id)) {
            return $this->returnErrorData('[product_id] Data Not Found', 404);
        }

        try {

            foreach ($request->raws as $key => $value) {
                $Item = new ProductRaw();
                $Item->product_id = $request->product_id;
                $Item->raw_id = $value['product_id'];
                $Item->qty = $value['qty'];
                $Item->detail = $value['detail'];
                $Item->save();
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
     * Display the specified resource.
     *
     * @param  \App\Models\ProductRaw  $productRaw
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = ProductRaw::where('id', $id)
            ->first();

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductRaw  $productRaw
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductRaw $productRaw)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductRaw  $productRaw
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
            $Item = ProductRaw::find($id);
            $Item->product_id = $request->product_id;
            $Item->raw_id = $request->raw_id;
            $Item->qty = $request->qty;
            $Item->detail = $request->detail;
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductRaw  $productRaw
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductRaw $productRaw)
    {
        //
    }
}
