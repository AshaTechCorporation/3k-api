<?php

namespace App\Http\Controllers;

use App\Models\orderPromotion;
use App\Models\Area;
use App\Models\Brand;
use App\Models\BrandModel;
use App\Models\CC;
use App\Models\Clients;
use App\Models\Color;
use App\Models\Factory;
use App\Models\Finance;
use App\Models\Garage;
use App\Models\OrderCheck;
use App\Models\OrderList;
use App\Models\Orders;
use App\Models\PaymentPeriod;
use App\Models\Products;
use App\Models\promotion;
use App\Models\User;
use App\Models\Discount;
use App\Models\IncomeDeductTrans;
use App\Models\Jobs;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    public function getList()
    {
        $Item = Orders::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['items'] = OrderList::where('order_id', $Item[$i]['id'])->get();
                foreach ($Item[$i]['items'] as $key => $value) {
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
        $status = $request->status;


        $col = [
            'id', 'client_id','code', 'brand', 'model', 'color', 'license', 'province', 'year',
            'client_name', 'client_phone', 'client_id_card', 'client_address',
            'booking_date', 'pickup_date', 'sale_channel', 'sale_id',
            'down_payment', 'sale_price', 'discount', 'sale_remark', 'status',
            'create_by', 'update_by', 'created_at', 'updated_at'
        ];

        $orderby = $col;

        $D = Orders::select($col);

        if ($status) {

            $D->where('status', $status);
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
                $d[$i]->jobs = Jobs::where('order_id',$d[$i]->id)->first();
                $d[$i]->client = Clients::find($d[$i]->client_id);
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

        $check = Products::where('license_plate',$request->license)->first();
        if (!$check) {
            return $this->returnErrorData('ไม่พบข้อมูลสินค้าที่ท่านเลือก ทะเบียน'.$request->license, 404);
        } else {
            $check->status = "book";
            $check->save();
        }     
        
        

        DB::beginTransaction();

        try {
            $client = Clients::where('idcard', $request->client_id_card)->first();

            if (!$client) {
                $client = new Clients();
                $client->name = $request->client_name;
                $client->phone = $request->client_phone;
                $client->idcard = $request->client_id_card;
                $client->address = $request->client_address;
                $client->save();
            }

            $prefix = "#OR-";
            $id = IdGenerator::generate(['table' => 'orders', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);

            $order = new Orders();
            $order->code = $id;
            $order->sale_id = $loginBy->id;
            $order->brand = $request->brand;
            $order->model = $request->model;
            $order->color = $request->color;
            $order->license = $request->license;
            $order->province = $request->province;
            $order->year = $request->year;
            $order->client_id = $client->id;
            $order->client_name = $request->client_name;
            $order->client_phone = $request->client_phone;
            $order->client_id_card = $request->client_id_card;
            $order->client_address = $request->client_address;

            $order->booking_date = $request->booking_date;
            $order->pickup_date = $request->pickup_date;
            $order->sale_channel = $request->sale_channel;

            $order->down_payment = $request->down_payment;
            $order->sale_price = $request->sale_price;
            $order->discount = $request->discount;
            $order->sale_remark = $request->sale_remark;

            $order->save();


            // รายการสินค้า
            $ItemP = new OrderList();
            $ItemP->order_id = $order->id;
            $ItemP->product_id = $check->id;
            $ItemP->save();

            // รายการตรวจสอบ
            foreach ($request->check_lists as $value) {
                $ItemC = new OrderCheck();
                $ItemC->order_id = $order->id;
                $ItemC->detail = $value['detail'];
                $ItemC->save();
            }

              // รายกาค่าใช้จ่าย
              foreach ($request->income_expense_trans as $value) {
                $ItemDeduct = new IncomeDeductTrans();
                $ItemDeduct->order_id = $order->id;
                $ItemDeduct->transaction_date = $value['transaction_date'] ?? "";
                $ItemDeduct->type = $value['type'] ?? "expense";
                $ItemDeduct->type_ref_id = $value['type_ref_id'] ?? "";
                $ItemDeduct->description = $value['description'] ?? "";
                $ItemDeduct->amount = $value['amount'] ?? "";
                $ItemDeduct->payment_method = $value['payment_method'] ?? "";
                $ItemDeduct->attachment = $value['attachment'] ?? "";
                $ItemDeduct->save();
            }

             if($request->master_jobs_id){
             $check = Jobs::find($request->master_jobs_id);
                if (!$check) {
                    return $this->returnErrorData('ไม่พบข้อมูลที่ท่านเลือก มาตราฐาน '.$request->master_jobs_id, 404);
                } else {
                    $check = Jobs::with([
                    'images',
                    'steps.stepJobTypeLists.productAttributes',
                    'steps.stepJobTypeLists.productAttributeOthers',
                    'steps.stepJobTypeLists.expenses',
                    'steps.stepJobTypeLists.workType',
                    'otherExpenses'
                ])->find($request->master_jobs_id);

                if (!$check) {
                    return $this->returnErrorData('ไม่พบข้อมูลที่ท่านเลือก มาตราฐาน ' . $request->master_jobs_id, 404);
                } else {
                    $job = $check->replicate();
                    $prefix = "#JO-";
                    $id = IdGenerator::generate(['table' => 'jobs', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);

                    $job->code = $id;
                    $job->completed_date = $request->completed_date;
                    $job->remark = $request->remark;
                    $job->order_id = $order->id;
                    $job->product_id = $ItemP->product_id;
                    $job->master_name = null;
                    $job->master = "N";
                    $job->status = "pending";
                    $job->save();

                    foreach ($check->images as $img) {
                        $image = $img->replicate();
                        $image->job_id = $job->id;
                        $image->save();
                    }

                    foreach ($check->steps as $step) {
                        $stp = $step->replicate();
                        $stp->job_id = $job->id;
                        $stp->status = "waiting";
                        $stp->save();

                        foreach ($step->stepJobTypeLists as $work_type) {
                            $jobtype = $work_type->replicate();
                            $jobtype->job_id = $job->id;
                            $jobtype->step_jobs_id = $stp->id;
                            $jobtype->status = "waiting";
                            $jobtype->save();

                            foreach ($work_type->productAttributes as $product_attribute) {
                                $product_attr = $product_attribute->replicate();
                                $product_attr->job_id = $job->id;
                                $product_attr->step_jobs_type_list_id = $jobtype->id;
                                $product_attr->save();
                            }

                            foreach ($work_type->productAttributeOthers as $product_attribute_other) {
                                $product_attr_other = $product_attribute_other->replicate();
                                $product_attr_other->job_id = $job->id;
                                $product_attr_other->step_jobs_type_list_id = $jobtype->id;
                                $product_attr_other->save();
                            }

                            foreach ($work_type->expenses as $expense) {
                                $expen = $expense->replicate();
                                $expen->job_id = $job->id;
                                $expen->step_jobs_type_list_id = $jobtype->id;
                                $expen->save();
                            }
                        }
                    }

                    foreach ($check->otherExpenses as $otherexpense) {
                        $other_expense = $otherexpense->replicate();
                        $other_expense->job_id = $job->id;
                        $other_expense->save();
                    }
                } 
            }
        }

            // log
            $userId = $loginBy->id ?? 'admin';
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' รหัสคำสั่งซื้อ: ' . $order->code;
            $this->Log($userId, $description, $type);

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $order);
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Orders  $orders
     * @return \Illuminate\Http\Response
     */
   public function show($id)
    {
        $order = Orders::with(['orderList', 'checkLists', 'incomeExpenses'])->find($id);

        if (!$order) {
            return $this->returnErrorData('ไม่พบข้อมูลคำสั่งซื้อ', 404);
        }

        $order->sale = User::find($order->sale_id);
        $order->product = Products::where('license_plate',$order->license)->first();
        $order->client = Clients::find($order->client_id);

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $order);
    } 

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Orders  $orders
     * @return \Illuminate\Http\Response
     */
    public function edit(Orders $orders)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Orders  $orders
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $loginBy = $request->login_by;

        DB::beginTransaction();

        try {
            $order = Orders::findOrFail($id);

            $order->sale_id = $loginBy->id;
            $order->brand = $request->brand;
            $order->model = $request->model;
            $order->color = $request->color;
            $order->license = $request->license;
            $order->province = $request->province;
            $order->year = $request->year;

            $order->client_name = $request->client_name;
            $order->client_phone = $request->client_phone;
            $order->client_id_card = $request->client_id_card;
            $order->client_address = $request->client_address;

            $order->booking_date = $request->booking_date;
            $order->pickup_date = $request->pickup_date;
            $order->sale_channel = $request->sale_channel;

            $order->down_payment = $request->down_payment;
            $order->sale_price = $request->sale_price;
            $order->discount = $request->discount;
            $order->sale_remark = $request->sale_remark;

            $order->save();

            OrderList::where('order_id', $order->id)->delete();
            $product = Products::where('license_plate', $request->license)->first();
            if ($product) {
                $product->status = "book";
                $product->save();

                $itemP = new OrderList();
                $itemP->order_id = $order->id;
                $itemP->product_id = $product->id;
                $itemP->save();
            }

            OrderCheck::where('order_id', $order->id)->delete();
            foreach ($request->check_lists as $value) {
                $ItemC = new OrderCheck();
                $ItemC->order_id = $order->id;
                $ItemC->detail = $value['detail'];
                $ItemC->save();
            }

            IncomeDeductTrans::where('order_id', $order->id)->delete();
            foreach ($request->income_expense_trans as $value) {
                $ItemDeduct = new IncomeDeductTrans();
                $ItemDeduct->order_id = $order->id;
                $ItemDeduct->transaction_date = $value['transaction_date'] ?? "";
                $ItemDeduct->type = $value['type'] ?? "expense";
                $ItemDeduct->type_ref_id = $value['type_ref_id'] ?? "";
                $ItemDeduct->description = $value['description'] ?? "";
                $ItemDeduct->amount = $value['amount'] ?? "";
                $ItemDeduct->payment_method = $value['payment_method'] ?? "";
                $ItemDeduct->attachment = $value['attachment'] ?? "";
                $ItemDeduct->save();
            }

            $userId = $loginBy->id ?? 'admin';
            $type = 'แก้ไขรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' รหัสคำสั่งซื้อ: ' . $order->code;
            $this->Log($userId, $description, $type);

            DB::commit();

            return $this->returnSuccess('อัปเดตข้อมูลสำเร็จ', $order);
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Orders  $orders
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Orders::find($id);
             if (!$Item) {
                return $this->returnErrorData('ไม่พบข้อมูลคำสั่งซื้อ', 404);
            }
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

    public function updateStatus(Request $request)
    {

        $id = $request->order_id;
        $status = $request->status;

        DB::beginTransaction();

        try {

            $Item = Orders::find($id);
             if (!$Item) {
                return $this->returnErrorData('ไม่พบข้อมูลคำสั่งซื้อ', 404);
            }
            $Item->status = $status;
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
