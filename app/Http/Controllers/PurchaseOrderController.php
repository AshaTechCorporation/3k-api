<?php

namespace App\Http\Controllers;

use App\Models\CategoryProduct;
use App\Models\Products;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderList;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class PurchaseOrderController extends Controller
{
    public function getList()
    {
        $Item = PurchaseOrder::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['supplier'] = Supplier::find($Item[$i]['supplier_id']);
                $Item[$i]['items'] = Products::where('pr_no', $Item[$i]['code'])->get();
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

        $Status = $request->status;

        $col = array('id', 'date', 'supplier_id', 'code', 'detail', 'total_price', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'date', 'supplier_id', 'code', 'detail', 'total_price', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = PurchaseOrder::select($col);

        if (isset($Status)) {
            $D->where('status', $Status);
        }

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
                $d[$i]->items = Products::where('pr_no', $d[$i]->code)->get();
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

        if (!isset($request->date)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลวันที่ให้เรียบร้อย', 404);
        } else if ($request->date == "Invalid date") {
            return $this->returnErrorData('กรุณาระบุข้อมูลวันที่ให้เรียบร้อย', 404);
        } else



            DB::beginTransaction();

        try {
            $prefix = "#PO-";
            $pr_no = IdGenerator::generate(['table' => 'purchase_orders', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);

            $Item = new PurchaseOrder();
            $Item->code = $pr_no;
            $Item->date = $request->date;
            $Item->detail = $request->detail;
            $Item->total_price = $request->total_price;
            $Item->supplier_id = $request->supplier_id;
            $Item->save();
            foreach ($request->items as $key => $value) {

                $product = Products::where('license_plate', $value['license_plate'])->first();

                if ($product) {
                    return $this->returnErrorData('ป้ายทะเบียน มีสินค้าในระบบอยู่แล้ว', 404);
                }

                $check1 = CategoryProduct::find($value['category_product_id']);
                if (!$check1) {
                    return $this->returnErrorData('ไม่พบข้อมูล category_product_id ในระบบ', 404);
                }

                $prefix = "#" . $check1->prefix . "-";
                $id = IdGenerator::generate(['table' => 'products', 'field' => 'code', 'length' => 13, 'prefix' => $prefix]);

                $ItemL = new Products();
                $ItemL->code = $id;
                $ItemL->category_product_id = $value['category_product_id'];
                $ItemL->pr_no = $pr_no;
                $ItemL->brand_id = $value['brand_id'];
                $ItemL->brand_model_id = $value['brand_model_id'];
                $ItemL->cc_id = $value['cc_id'];
                $ItemL->color_id = $value['color_id'];
                $ItemL->name = $value['name'];
                $ItemL->detail = $value['detail'];
                $ItemL->tank_no = $value['tank_no'];
                $ItemL->engine_no = $value['engine_no'];
                $ItemL->license_plate = $value['license_plate'];
                $ItemL->year = $value['year'];
                $ItemL->sale_price = $value['sale_price'];
                $ItemL->cost = $value['cost'];
                $ItemL->type = $value['type'];
                $ItemL->supplier_id = $request->supplier_id;
                $ItemL->mile = $value['mile'];

                $ItemL->save();
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
     * @param  \App\Models\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        //
    }
}
