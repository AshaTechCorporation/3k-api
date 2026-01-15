<?php

namespace App\Http\Controllers;

use App\Imports\ProductImport;
use App\Models\CategoryProduct;
use App\Models\Channel;
use App\Models\Floor;
use App\Models\ProductImages;
use App\Models\Products;
use App\Models\Shelf;
use App\Models\Clients;
use App\Models\SubCategoryProduct;
use App\Models\Area;
use App\Models\Brand;
use App\Models\BrandModel;
use App\Models\Broker;
use App\Models\CC;
use App\Models\Color;
use App\Models\Company;
use App\Models\Finance;
use App\Models\Insurance;
use App\Models\ProductRaw;
use App\Models\StockTrans;
use App\Models\Supplier;
use App\Models\User;
use App\Models\JobsExpensesList;
use App\Models\IncomeExpensesTracker;
use Illuminate\Http\Request;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{

    public function getListAll()
    {
        // $Item = Products::where('broker_id',"!=",null)->get()->toarray();
        $Item = Products::whereNull('broker_id') // Checks if 'broker_id' is NULL
        ->orWhere('broker_id', '=', '') // Checks if 'broker_id' is an empty string
        ->get()
        ->toArray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['images'] = ProductImages::where('product_id', $Item[$i]['id'])->get();
                if ($Item[$i]['image']) {
                    $Item[$i]['image'] = url($Item[$i]['image']);
                }
                for ($n = 0; $n <= count($Item[$i]['images']) - 1; $n++) {
                    $Item[$i]['images'][$n]['image'] = url($Item[$i]['images'][$n]['image']);
                }
                $Item[$i]['brand'] = Brand::find($Item[$i]['brand_id']);
                $Item[$i]['brand_model'] = BrandModel::find($Item[$i]['brand_model_id']);
                $Item[$i]['supplier'] = Supplier::find($Item[$i]['supplier_id']);
                $Item[$i]['color'] = Color::find($Item[$i]['color_id']);
                $Item[$i]['cc'] = CC::find($Item[$i]['cc_id']);

                $Item[$i]['type'] = $Item[$i]['type'] == "First" ? "มือหนึ่ง" : "มือสอง";
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getListByBrand($id)
    {
        $Item = Products::where('brand_id', $id)->get();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['images'] = ProductImages::where('product_id', $Item[$i]['id'])->get();
                if ($Item[$i]['image']) {
                    $Item[$i]['image'] = url($Item[$i]['image']);
                }
                for ($n = 0; $n <= count($Item[$i]['images']) - 1; $n++) {
                    $Item[$i]['images'][$n]['image'] = url($Item[$i]['images'][$n]['image']);
                }
                $Item[$i]['brand'] = Brand::find($Item[$i]['brand_id']);
                $Item[$i]['brand_model'] = BrandModel::find($Item[$i]['brand_model_id']);
                $Item[$i]['supplier'] = Supplier::find($Item[$i]['supplier_id']);
                $Item[$i]['color'] = Color::find($Item[$i]['color_id']);
                $Item[$i]['cc'] = CC::find($Item[$i]['cc_id']);

                $Item[$i]['type'] = $Item[$i]['type'] == "First" ? "มือหนึ่ง" : "มือสอง";
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getListByModel($id)
    {
        $Item = Products::where('brand_model_id', $id)->get();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['images'] = ProductImages::where('product_id', $Item[$i]['id'])->get();
                if ($Item[$i]['image']) {
                    $Item[$i]['image'] = url($Item[$i]['image']);
                }
                for ($n = 0; $n <= count($Item[$i]['images']) - 1; $n++) {
                    $Item[$i]['images'][$n]['image'] = url($Item[$i]['images'][$n]['image']);
                }
                $Item[$i]['brand'] = Brand::find($Item[$i]['brand_id']);
                $Item[$i]['brand_model'] = BrandModel::find($Item[$i]['brand_model_id']);
                $Item[$i]['supplier'] = Supplier::find($Item[$i]['supplier_id']);
                $Item[$i]['color'] = Color::find($Item[$i]['color_id']);
                $Item[$i]['cc'] = CC::find($Item[$i]['cc_id']);

                $Item[$i]['type'] = $Item[$i]['type'] == "First" ? "มือหนึ่ง" : "มือสอง";
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

        $area_id = $request->area_id;
        $type = $request->type;
        $status = $request->status;
        $brand_id = $request->brand_id;
        $companie_id = $request->companie_id;
        $category_product_id = $request->category_product_id;

        $tax_start_date = $request->tax_start_date;
        $tax_end_date = $request->tax_end_date;

        $col = array('id', 'cost','province','vat_status','tax_expire','video_url','gift_price','promotion_discount','sale_price','status', 'mile', 'image', 'type', 'category_product_id', 'companie_id', 'area_id', 'brand_id', 'brand_model_id', 'cc_id', 'color_id', 'name', 'detail', 'code', 'tank_no', 'engine_no', 'license_plate', 'year', 'sale_status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('id', 'cost','province','vat_status','tax_expire','video_url','gift_price','promotion_discount','sale_price','status', 'mile', 'image', 'type', 'category_product_id', 'companie_id', 'area_id', 'brand_id', 'brand_model_id', 'cc_id', 'color_id', 'name', 'detail', 'code', 'tank_no', 'engine_no', 'license_plate', 'year', 'sale_status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = Products::select($col);
        // $D->where('sale_status', 'N');

        if ($companie_id) {
            $D->where('companie_id', $companie_id);
        }

        if ($area_id) {
            $D->where('area_id', $area_id);
        }

        if ($type) {
            $D->where('type', $type);
        }

        if ($status) {
            $D->where('status', $status);
        }

        if ($brand_id) {
            $D->where('brand_id', $brand_id);
        }

        if ($category_product_id) {
            $D->where('category_product_id', $category_product_id);
        }

        if ($tax_start_date && $tax_end_date) {
            $D->whereBetween('tax_expire', [$tax_start_date, $tax_end_date]);
        } elseif ($tax_start_date) {
            $D->where('tax_expire', '>=', $tax_start_date);
        } elseif ($tax_end_date) {
            $D->where('tax_expire', '<=', $tax_end_date);
        }

        if (empty($order) || !isset($orderby[$order[0]['column']])) {
            $D->orderBy('created_at', 'desc');
        } else {
            $D->orderBy($orderby[$order[0]['column']], $order[0]['dir']);
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
                if($d[$i]->image)
                // $d[$i]->image = url($d[$i]->image);
                $d[$i]->category_product = CategoryProduct::find($d[$i]->category_product_id);
                $d[$i]->images = ProductImages::where('product_id', $d[$i]->id)->get();
                if(count($d[$i]->images)>0)
                $d[$i]->image = url($d[$i]->images[0]->image);

                $d[$i]->area = Area::find($d[$i]->area_id);
                if ($d[$i]->area) {
                    if ($d[$i]->area->image) {
                        $d[$i]->area->image = url($d[$i]->area->image);
                    }
                    // $d[$i]->companie = Company::find($d[$i]->area->companie_id);
                }
                $d[$i]->companie = Company::find($d[$i]->companie_id);

                for ($n = 0; $n <= count($d[$i]->images) - 1; $n++) {
                    $d[$i]->images[$n]->image = url($d[$i]->images[$n]->image);
                }

                $d[$i]->brand = Brand::find($d[$i]->brand_id);
                $d[$i]->brand_model = BrandModel::find($d[$i]->brand_model_id);
                $d[$i]->cc = CC::find($d[$i]->cc_id);
                $d[$i]->color = Color::find($d[$i]->color_id);

                if ($d[$i]->type == "First") {
                    $d[$i]->type = "มือหนึ่ง";
                } else if ($d[$i]->type == "Secound") {
                    $d[$i]->type = "มือสอง";
                }


                if ($d[$i]->status == "sold") {
                    $d[$i]->status = "ขายแล้ว";
                } else if ($d[$i]->status == "free") {
                    $d[$i]->status = "ว่างอยู่";
                } else if ($d[$i]->status == "book") {
                    $d[$i]->status = "จองแล้ว";
                }

                $d[$i]->mile = $d[$i]->mile != 'null' ? $d[$i]->mile:"-";
                $d[$i]->province = $d[$i]->province != 'null' ? $d[$i]->province:"-";
                $d[$i]->gift_price = $d[$i]->gift_price != 'null' ? number_format((int)$d[$i]->gift_price,0):"-";
                $d[$i]->cost = $d[$i]->cost != 'null' ? number_format((int)$d[$i]->cost,0):"-";

            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }


    public function getByCode($code)
    {
        $Item = Products::where('code', $code)->first();
        if (!empty($Item)) {
            $Item['No'] = 1;
            $Item['image'] = url($Item['image']);
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
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

        if (!isset($request->category_product_id)) {
            return $this->returnErrorData('กรุณาเลือกหมวดสินค้าด้วยครับ', 404);
        }

        if (!isset($request->type)) {
            return $this->returnErrorData('กรุณาเลือกประเภทรถด้วยครับ', 404);
        }

        $check1 = CategoryProduct::find($request->category_product_id);
        if (!$check1) {
            return $this->returnErrorData('ไม่พบข้อมูล หมวดหมู่สินค้านี้ ในระบบ', 404);
        }


        DB::beginTransaction();

        try {

            $prefix = "#" . $check1->prefix . "-";
            $id = IdGenerator::generate(['table' => 'products', 'field' => 'code', 'length' => 13, 'prefix' => $prefix]);

            $Item = new Products();
            $Item->code = $id;
            $Item->category_product_id = $request->category_product_id;
            $Item->pr_no = $request->pr_no;
            $Item->companie_id = $request->companie_id == "null" ? null : $request->companie_id;
            $Item->area_id = $request->area_id;
            $Item->brand_id = $request->brand_id;
            $Item->brand_model_id = $request->brand_model_id;
            $Item->cc_id = $request->cc_id;
            $Item->color_id = $request->color_id;
            $Item->name = $request->name;
            $Item->detail = $request->detail;
            $Item->tank_no = $request->tank_no;
            $Item->engine_no = $request->engine_no;
            $Item->license_plate = $request->license_plate;
            $Item->year = $request->year;
            $Item->sale_price = $request->sale_price;
            $Item->cost = $request->cost;
            $Item->type = $request->type ? $request->type : "First";
            $Item->supplier_id = $request->supplier_id;
            $Item->mile = $request->mile;
            $Item->front_tire = $request->front_tire;
            $Item->back_tire = $request->back_tire;
            $Item->video_url = $request->video_url;
            $Item->vat_status = $request->vat_status;
            $Item->province = $request->province;
            $Item->tax_expire = $request->tax_expire;

            $Item->save();

            foreach ($request->first_images as $key => $value) {

                $Files = new ProductImages();
                $Files->product_id =  $Item->id;
                $Files->type =  "first_images";
                $Files->image = $value;
                $Files->save();
            }

            foreach ($request->repair_images as $key => $value) {

                $Files = new ProductImages();
                $Files->product_id =  $Item->id;
                $Files->type =  "repair_images";
                $Files->image = $value;
                $Files->save();
            }

            foreach ($request->ready_images as $key => $value) {

                $Files = new ProductImages();
                $Files->product_id =  $Item->id;
                $Files->type =  "ready_images";
                $Files->image = $value;
                $Files->save();
            }

            foreach ($request->after_sale_images as $key => $value) {

                $Files = new ProductImages();
                $Files->product_id =  $Item->id;
                $Files->type =  "after_sale_images";
                $Files->image = $value;
                $Files->save();
            }
           

            $ff = ProductImages::where('product_id', $Item->id)->where('type',"first_images")->first();
            if ($ff) {
                $Item->image = $ff->image;
                $Item->save();
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Products  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Products::with([
            'company',
            'area',
            'brand',
            'brandModel',
            'cc',
            'color',
            'supplier',
            'categoryProduct',
            'images',
            'jobs' => function ($q) {
                $q->with([
                    'images',
                    'otherExpenses',
                    'steps' => function ($query) {
                        $query->with([
                            'stepJobTypeLists' => function ($stepQuery) {
                                $stepQuery->with([
                                    'productAttributes' => function ($q) {
                                        $q->with('productAttribute');
                                    },
                                    'productAttributeOthers',
                                    'expenses',
                                    'workType',
                                    'images',
                                ]);
                            }
                        ]);
                    }
                ]);
            }
        ])->find($id);

        if (!$product) {
            return $this->returnErrorData('ไม่พบข้อมูลสินค้า', 404);
        }else{
            $product->expense_trackers = JobsExpensesList::where('car_id',$product->id)->get();
            $product->expense_others = IncomeExpensesTracker::where('car_id',$product->id)->get();

        }

        // ✅ แปลง path รูปเป็น URL เต็ม
        if ($product->video_url) {
            $product->video_url = url($product->video_url);
        }

        foreach ($product->images as $img) {
            $img->image = url($img->image);
        }

        foreach ($product->jobs as $job) {
            foreach ($job->images as $img) {
                $img->image = url($img->image);
            }

            foreach ($job->steps as $step) {
                foreach ($step->stepJobTypeLists as $typeList) {
                    foreach ($typeList->images as $img) {
                        $img->image = url($img->image);
                    }

                    foreach ($typeList->productAttributes as $attr) {
                        // Optional: แปลงข้อมูลเพิ่มเติม เช่น URL หรือชื่อสินค้า
                        $attr->full_name = $attr->name ?? '';
                    }
                }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $product);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Products  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Products $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Products  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $loginBy = $request->login_by;
    
        $Item = Products::find($id);
    
        if (!$Item) {
            return $this->returnErrorData('ไม่พบรายการสินค้าที่ต้องการแก้ไข', 404);
        }
    
        if (!isset($request->category_product_id)) {
            return $this->returnErrorData('กรุณาเลือกหมวดสินค้าด้วยครับ', 404);
        }
    
        if (!isset($request->type)) {
            return $this->returnErrorData('กรุณาเลือกประเภทรถด้วยครับ', 404);
        }
    
        $check1 = CategoryProduct::find($request->category_product_id);
        if (!$check1) {
            return $this->returnErrorData('ไม่พบข้อมูล หมวดหมู่สินค้านี้ ในระบบ', 404);
        }
    
        DB::beginTransaction();
    
        try {
            // อัปเดตข้อมูลหลัก
            $Item->category_product_id = $request->category_product_id;
            $Item->pr_no = $request->pr_no;
            $Item->companie_id = $request->companie_id == "null" ? null : $request->companie_id;
            $Item->area_id = $request->area_id;
            $Item->brand_id = $request->brand_id;
            $Item->brand_model_id = $request->brand_model_id;
            $Item->cc_id = $request->cc_id;
            $Item->color_id = $request->color_id;
            $Item->name = $request->name;
            $Item->detail = $request->detail;
            $Item->tank_no = $request->tank_no;
            $Item->engine_no = $request->engine_no;
            $Item->license_plate = $request->license_plate;
            $Item->year = $request->year;
            $Item->sale_price = $request->sale_price;
            $Item->cost = $request->cost;
            $Item->type = $request->type ? $request->type : "First";
            $Item->supplier_id = $request->supplier_id;
            $Item->mile = $request->mile;
            $Item->front_tire = $request->front_tire;
            $Item->back_tire = $request->back_tire;
            $Item->video_url = $request->video_url;
            $Item->vat_status = $request->vat_status;
            $Item->province = $request->province;
            $Item->tax_expire = $request->tax_expire;

            $Item->save();

    
            // เพิ่มรูปภาพใหม่ตาม type
            foreach ($request->first_images as $key => $value) {

                $Files = new ProductImages();
                $Files->product_id =  $Item->id;
                $Files->type =  "first_images";
                $Files->image = $value;
                $Files->save();
            }

            foreach ($request->repair_images as $key => $value) {

                $Files = new ProductImages();
                $Files->product_id =  $Item->id;
                $Files->type =  "repair_images";
                $Files->image = $value;
                $Files->save();
            }

            foreach ($request->ready_images as $key => $value) {

                $Files = new ProductImages();
                $Files->product_id =  $Item->id;
                $Files->type =  "ready_images";
                $Files->image = $value;
                $Files->save();
            }

            foreach ($request->after_sale_images as $key => $value) {

                $Files = new ProductImages();
                $Files->product_id =  $Item->id;
                $Files->type =  "after_sale_images";
                $Files->image = $value;
                $Files->save();
            }

    
            // กำหนดรูปปก
            $firstImage = ProductImages::where('product_id', $Item->id)->where('type', 'first_images')->first();
            if ($firstImage) {
                $Item->image = $firstImage->image;
                $Item->save();
            }
    
            // log
            $userId = "admin";
            $type = 'แก้ไขรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
            $this->Log($userId, $description, $type);
    
            DB::commit();
    
            return $this->returnSuccess('อัปเดตรายการสินค้าสำเร็จ', $Item);
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 500);
        }
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Products  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Products::find($id);
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
        if (!isset($request->category_product_id)) {
            return $this->returnErrorData('กรุณาเลือกหมวดหมู่สินค้าด้วยครับ', 404);
        }

        $check = CategoryProduct::find($request->category_product_id);
        if (!$check) {
            return $this->returnErrorData('ไม่พบข้อมูล หมวดหมู่สินค้านี้ ในระบบ', 404);
        }

        // $check = SubCategoryProduct::find($request->sub_category_product_id);
        // if (!$check) {
        //     return $this->returnErrorData('ไม่พบข้อมูล sub_category_product_id ในระบบ', 404);
        // }

        DB::beginTransaction();

        try {
            $Item = Products::find($request->id);

            if (!$Item) {
                return $this->returnErrorData('ไม่พบรายการนี้ในระบบ', 404);
            }
            $Item->category_product_id = $request->category_product_id;
            $Item->pr_no = $request->pr_no;
            $Item->companie_id = $request->companie_id;
            $Item->area_id = $request->area_id;
            $Item->brand_id = $request->brand_id;
            $Item->brand_model_id = $request->brand_model_id;
            $Item->cc_id = $request->cc_id;
            $Item->color_id = $request->color_id;
            $Item->name = $request->name;
            $Item->detail = $request->detail;
            $Item->tank_no = $request->tank_no;
            $Item->engine_no = $request->engine_no;
            $Item->license_plate = $request->license_plate;
            $Item->year = $request->year;
            $Item->sale_price = $request->sale_price;
            $Item->cost = $request->cost;
            $Item->type = $request->type;
            $Item->supplier_id = $request->supplier_id;
            $Item->mile = $request->mile;
            $Item->front_tire = $request->front_tire;
            $Item->back_tire = $request->back_tire;
            $Item->video_url = $request->video_url;
            $Item->vat_status = $request->vat_status;
            $Item->tax_expire = $request->tax_expire;
            $Item->province = $request->province;
            $Item->save();

            if ($request->hasFile('images')) {
                $allowedfileExtension = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
                $files = $request->file('images');
                $errors = [];

                foreach ($files as $file) {

                    if ($file->isValid()) {
                        $extension = $file->getClientOriginalExtension();

                        $check = in_array($extension, $allowedfileExtension);
                        

                        if ($check) {
                          
                            $Files = new ProductImages();
                            $Files->product_id =  $Item->id;
                            $Files->image = $this->uploadImage($file, '/images/products/');
                            $Files->save();
                        }
                    }
                }
            }
            $ff = ProductImages::where('product_id', $Item->id)->first();
            if ($ff) {
                $Item->image = $ff->image;
                $Item->save();
            }
            //log
            $userId = "admin";
            $type = 'แก้ไข';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการเพิ่ม ' . $request->username;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    public function Import(Request $request)
    {
        ini_set('memory_limit', '4048M');

        $file = request()->file('file');
        $fileName = $file->getClientOriginalName();

        $Data = Excel::toArray(new ProductImport(), $file);

        $data = $Data[0];

        if (count($data) > 0) {

            DB::beginTransaction();

            try {

                for ($i = 2; $i < count($data); $i++) {

                    $broker = Broker::where('name', trim($data[$i][48]))->first();
                    if (!$broker) {
                        $broker = new Broker();
                        $broker->name = trim($data[$i][48]);
                        $broker->number = trim($data[$i][49]);
                        $broker->address = trim($data[$i][50]);
                        $broker->phone = trim($data[$i][52]);
                        $broker->save();
                    }

                    $finance = Finance::where('name', trim($data[$i][30]))->first();
                    if (!$finance) {
                        $finance = new Finance();
                        $finance->name = trim($data[$i][30]);
                        $finance->phone = trim($data[$i][31]);
                        $finance->save();
                    }


                    $insurance = Insurance::where('name', trim($data[$i][34]))->first();
                    if (!$insurance) {
                        $insurance = new Insurance();
                        $insurance->name = trim($data[$i][34]);
                        $insurance->phone = trim($data[$i][35]);
                        $insurance->save();
                    }

                    $user = User::where('name', trim($data[$i][4]))->first();
                    if (!$user) {
                        $user = new User();
                        $user->name = trim($data[$i][4]);
                        $user->department_id = 2;
                        $user->position_id = 2;
                        $user->save();
                    }

                    $brand_models = explode(' ', $data[$i][5]);

                    if (count($brand_models) >= 1) {
                        $brand = Brand::where('name', trim($brand_models[0]))->first();
                        if (!$brand) {
                            $brand = new Brand();
                            $brand->name = $brand_models[0];
                            $brand->detail = $brand_models[0];
                            $brand->save();
                        }
                    }
                    $color = null;

                    if (count($brand_models) > 1) {
                        $md = str_replace($brand_models[0], '', $data[$i][5]);
                        $brand_model = BrandModel::where('name', $md)->first();
                        if (!$brand_model) {
                            $brand_model = new BrandModel();
                            $brand_model->name = $md;
                            $brand_model->detail = $md;
                            $brand_model->brand_id = $brand->id;
                            $brand_model->save();

                            $color = Color::where('name', trim($data[$i][10]))->first();
                            if (!$color) {
                                $color = new Color();
                                $color->brand_model_id = $brand_model->id;
                                $color->name = trim($data[$i][10]);
                                $color->save();
                            }
                        }
                    }


                    $product = Products::where('tank_no', trim($data[$i][9]))->first();
                    if (!$product || trim($data[$i][9]) == "") {
                        $product = new Products();
                        $product->name = trim($data[$i][5]);
                        $product->booking_date = trim($data[$i][1]);
                        $product->release_date = trim($data[$i][2]);
                        $product->release_time = trim($data[$i][3]);
                        $product->brand_id = $brand ? $brand->id : null;
                        $product->brand_model_id = $brand_model ? $brand_model->id : null;
                        $product->license_plate = trim($data[$i][6]);
                        $product->year = trim($data[$i][7]);
                        $product->color_id = $color ? $color->id : null;
                        $product->tank_no = trim($data[$i][8]);
                        $product->engine_no = trim($data[$i][9]);
                        $product->gear = trim($data[$i][11]);
                        $product->mile = trim($data[$i][12]);
                        $product->sale_status = trim($data[$i][17]);
                        $product->sale_price = trim($data[$i][18]);
                        $product->finance_buy = trim($data[$i][19]);
                        $product->car_book = trim($data[$i][63]) == 'มี' ? "Y" : 'N';
                        $product->cost = trim($data[$i][65]);
                        $product->gift_price = trim($data[$i][66]);
                        $product->promotion_discount = trim($data[$i][67]);
                        $product->front_tire = trim($data[$i][68]);
                        $product->back_tire = trim($data[$i][69]);

                        $product->save();
                    }


                    $client = Clients::where('idcard', trim($data[$i][15]))->first();
                    if (!$client || trim($data[$i][15]) == "") {
                        $prefix = "#C-";
                        $id = IdGenerator::generate(['table' => 'clients', 'field' => 'code', 'length' => 13, 'prefix' => $prefix]);

                        $client = new Clients();
                        $client->code = $id;
                        $client->name = trim($data[$i][13]);
                        $client->address = trim($data[$i][14]);
                        $client->phone = trim($data[$i][16]);
                        $client->idcard = trim($data[$i][15]);
                        $client->type = trim($data[$i][64]);
                        $client->save();
                    }
                }

                DB::commit();

                return $this->returnSuccess('ดำเนินการสำเร็จ', null);
            } catch (\Throwable $e) {

                DB::rollback();

                return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
            }
        }
    }

    public function updateImageSeq(Request $request,$id)
    {
        $id = $id; // Product ID
        $images = $request->images; // Array of image names in the desired order

        // // Fetch the current images for the product
        $currentImages = ProductImages::where('product_id', $id)->get();

        for ($i=0; $i < count( $images) ; $i++) { 
            $currentImages[$i]->image = $images[$i]['image'];
            $currentImages[$i]->save();
        }

    
        return $this->returnSuccess('อัปเดตรูปภาพสำเร็จ', $currentImages);
    }
}
