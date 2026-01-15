<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\BrokerProduct;
use App\Models\Products;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BrokerController extends Controller
{
    public function getList()
    {
        $Item = Broker::get()->toarray();

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


        $col = array('id', 'code', 'detail', 'image', 'name', 'tax', 'phone', 'email', 'address', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'code', 'detail', 'image', 'name', 'tax', 'phone', 'email', 'address', 'create_by', 'update_by', 'created_at', 'updated_at');



        $D = Broker::select($col);


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
            return $this->returnErrorData('[name] Data Not Found', 404);
        } else if (!isset($request->phone)) {
            return $this->returnErrorData('[phone] Data Not Found', 404);
        } 

        $check = Broker::where('phone', $request->phone)->first();
        if ($check) {
            return $this->returnErrorData('มีข้อมูล phone ในระบบแล้ว', 404);
        }

        DB::beginTransaction();

        try {
            $prefix = "#BRO-";
            $id = IdGenerator::generate(['table' => 'brokers', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);

            $Item = new Broker();
            $Item->code = $id;
            $Item->name = $request->name;
            $Item->phone = $request->phone;
            $Item->email = $request->email;
            $Item->address = $request->address;
            $Item->detail = $request->detail;
            $Item->tax = $request->tax;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/brokers/');
            }
            $Item->create_by = "admin";

            $Item->save();
            //

            foreach ($request->license_plates as $key => $value) {

                $ItemL = new BrokerProduct();
                $ItemL->broker_id = $Item->id;
                $ItemL->product_id = $value['product_id'];
                $ItemL->comission = $value['commision'];
                $ItemL->save();

                $ItemP = Products::find($value['product_id']);
                if($ItemP){
                    $ItemP->broker_id = $Item->id;
                    $ItemP->comission = $value['commision'];
                    $ItemP->save();
                }
            }

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

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาตรวจสอบข้อมูลให้ถูกต้อง ', 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Broker  $broker
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Broker::where('id', $id)
            ->first();

        if ($Item) {
            $Item->image = url($Item->image);
            $Item->license_plates = BrokerProduct::where('broker_id',$Item->id)->get();
            foreach ($Item->license_plates as $key => $value) {
                $Item->license_plates[$key]->product = Products::find($value['product_id']);
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Broker  $broker
     * @return \Illuminate\Http\Response
     */
    public function edit(Broker $broker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Broker  $broker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Broker $broker)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Broker  $broker
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Broker::find($id);

            $ItemB = BrokerProduct::where('broker_id', $Item->id)->get();
            foreach ($ItemB as $key => $value) {
                $ItemP = Products::find($value['product_id']);
                if($ItemP){
                    $ItemP->broker_id = null;
                    $ItemP->comission = 0;
                    $ItemP->save();
                }
            }
            BrokerProduct::where('broker_id', $Item->id)->delete();

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

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาตรวจสอบข้อมูลให้ถูกต้อง ', 404);
        }
    }

    public function updateData(Request $request)
    {
        if (!isset($request->id)) {
            return $this->returnErrorData('[id] Data Not Found', 404);
        }

        DB::beginTransaction();

        try {
            $Item = Broker::find($request->id);

            if (!$Item) {
                return $this->returnErrorData('ไม่พบรายการ', 404);
            }

            $Item->name = $request->name;
            $Item->phone = $request->phone;
            $Item->email = $request->email;
            $Item->address = $request->address;
            $Item->detail = $request->detail;
            $Item->tax = $request->tax;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/brokers/');
            }

            $Item->save();
            //
            $ItemB = BrokerProduct::where('broker_id', $Item->id)->get();
            foreach ($ItemB as $key => $value) {
                $ItemP = Products::find($value['product_id']);
                if($ItemP){
                    $ItemP->broker_id = null;
                    $ItemP->comission = 0;
                    $ItemP->save();
                }
            }
            BrokerProduct::where('broker_id', $Item->id)->delete();

            foreach ($request->license_plates as $key => $value) {

                $ItemL = new BrokerProduct();
                $ItemL->broker_id = $Item->id;
                $ItemL->product_id = $value['product_id'];
                $ItemL->comission = $value['commision'];
                $ItemL->save();

                $ItemP = Products::find($value['product_id']);
                if($ItemP){
                    $ItemP->broker_id = $Item->id;
                    $ItemP->comission = $value['commision'];
                    $ItemP->save();
                }
            }

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
