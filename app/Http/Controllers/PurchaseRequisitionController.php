<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionList;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class PurchaseRequisitionController extends Controller
{
    public function getList()
    {
        $Item = PurchaseRequisition::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['items'] = PurchaseRequisitionList::where('transfer_id', $Item[$i]['id'])->get();

                foreach ($Item[$i]['items']  as $key => $value) {
                    $Item[$i]['items'][$key]['product'] = Products::find($value['product_id']);
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

        $Status = $request->status;

        $col = array('id', 'date', 'companie_id', 'area_id', 'remark', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'date', 'companie_id', 'area_id', 'remark', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = PurchaseRequisition::select($col);

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
                $d[$i]->items = PurchaseRequisitionList::where('transfer_id', $d[$i]->id)->get();
                foreach ($d[$i]->items as $key => $value) {
                    $d[$i]->items[$key]->product = Products::find($value['product_id']);
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

        if (!isset($request->companie_id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลบริษัทให้เรียบร้อย', 404);
        } else if (!isset($request->area_id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลสาขาให้เรียบร้อย', 404);
        } else if (!isset($request->date)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลวันที่ให้เรียบร้อย', 404);
        } else if ($request->date == "Invalid date") {
            return $this->returnErrorData('กรุณาระบุข้อมูลวันที่ให้เรียบร้อย', 404);
        } else



            DB::beginTransaction();

        try {
            $prefix = "#PR-";
            $id = IdGenerator::generate(['table' => 'transfers', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);

            $Item = new PurchaseRequisition();
            $Item->code = $id;
            $Item->companie_id = $request->companie_id;
            $Item->area_id = $request->area_id;
            $Item->date = $request->date;
            $Item->remark = $request->remark;
            $Item->type = $request->type;

            $Item->save();
            foreach ($request->items as $key => $value) {

                $product = Products::find($value['product_id']);

                if (!$product) {
                    return $this->returnErrorData('ไม่พบสินค้าในระบบ', 404);
                }

                $ItemL = new PurchaseRequisitionList();
                $ItemL->transfer_id = $Item->id;
                $ItemL->product_id = $value['product_id'];
                $ItemL->qty = $value['qty'];
                $ItemL->selling_price = $value['selling_price'];
                $ItemL->purchase_price = $value['purchase_price'];
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
     * @param  \App\Models\PurchaseRequisition  $purchaseRequisition
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseRequisition $purchaseRequisition)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PurchaseRequisition  $purchaseRequisition
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseRequisition $purchaseRequisition)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PurchaseRequisition  $purchaseRequisition
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PurchaseRequisition  $purchaseRequisition
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseRequisition $purchaseRequisition)
    {
        //
    }
}
