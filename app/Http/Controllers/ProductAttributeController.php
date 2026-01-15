<?php

namespace App\Http\Controllers;

use App\Models\CategoryAttribute;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeImages;
use Illuminate\Http\Request;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductAttributeController extends Controller
{
    public function getListAll()
    {
        $Item = ProductAttribute::get()
        ->toArray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['images'] = ProductAttributeImages::where('product_attribute_id', $Item[$i]['id'])->get();
                if ($Item[$i]['image']) {
                    $Item[$i]['image'] = url($Item[$i]['image']);
                }
                for ($n = 0; $n <= count($Item[$i]['images']) - 1; $n++) {
                    $Item[$i]['images'][$n]['image'] = url($Item[$i]['images'][$n]['image']);
                }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }
    
    public function getList($id)
    {
        $Item = ProductAttribute::where('category_attribute_id', $id)->get();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['images'] = ProductAttributeImages::where('product_attribute_id', $Item[$i]['id'])->get();
                if ($Item[$i]['image']) {
                    $Item[$i]['image'] = url($Item[$i]['image']);
                }
                for ($n = 0; $n <= count($Item[$i]['images']) - 1; $n++) {
                    $Item[$i]['images'][$n]['image'] = url($Item[$i]['images'][$n]['image']);
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
        $category_attribute_id = $request->category_attribute_id;


        $col = array('id', 'category_attribute_id', 'code','qty', 'image', 'name', 'serial', 'sale_price', 'cost', 'image', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('id', 'category_attribute_id', 'code','qty', 'image', 'name', 'serial', 'sale_price', 'cost', 'image', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = ProductAttribute::select($col);
        // $D->where('sale_status', 'N');

        if ($status) {
            $D->where('status', $status);
        }

        if ($category_attribute_id) {
            $D->where('category_attribute_id', $category_attribute_id);
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
                $d[$i]->image = url($d[$i]->image);
                $d[$i]->category_attribute = CategoryAttribute::find($d[$i]->category_attribute_id);

                $d[$i]->images = ProductAttributeImages::where('product_attribute_id', $d[$i]->id)->get();

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

        if (!isset($request->category_attribute_id)) {
            return $this->returnErrorData('[category_attribute_id] Data Not Found', 404);
        }

        $check1 = CategoryAttribute::find($request->category_attribute_id);
        if (!$check1) {
            return $this->returnErrorData('ไม่พบข้อมูล category_attribute_id ในระบบ', 404);
        }


        $check3 = ProductAttribute::where('code', $request->code)->first();
        if ($check3) {
            return $this->returnErrorData('มี code ในระบบอยู่แล้ว', 404);
        }

        DB::beginTransaction();

        try {

            $prefix = "#" . $check1->code . "-";
            $id = IdGenerator::generate(['table' => 'product_attributes', 'field' => 'code', 'length' => 13, 'prefix' => $prefix]);

            $Item = new ProductAttribute();
            $Item->code = $id;
            $Item->category_attribute_id = $request->category_attribute_id;
            $Item->name = $request->name;
            $Item->serial = $request->serial;
            $Item->sale_price = $request->sale_price;
            $Item->cost = $request->cost;
            $Item->qty = $request->qty;
            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/product_attributes/');
            }

            $Item->save();

            $allowedfileExtension = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
            $files = $request->file('images');
            $errors = [];

            if ($files) {

                foreach ($files as $file) {

                    if ($file->isValid()) {
                        $extension = $file->getClientOriginalExtension();

                        $check = in_array($extension, $allowedfileExtension);

                        if ($check) {
                            $Files = new ProductAttributeImages();
                            $Files->product_attribute_id =  $Item->id;
                            $Files->image = $this->uploadImage($file, '/images/product_attributes/');
                            $Files->save();
                        }
                    }
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


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductAttribute  $productAttribute
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = ProductAttribute::where('id', $id)
            ->first();

        if ($Item) {

            $Item->images = ProductAttributeImages::where('product_attribute_id', $Item->id)->get();

            for ($n = 0; $n <= count($Item->images) - 1; $n++) {
                $Item->images[$n]->image = url($Item->images[$n]->image);
            }

            $Item->category_attribute = CategoryAttribute::find($Item->category_attribute_id);
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductAttribute  $productAttribute
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductAttribute $productAttribute)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductAttribute  $productAttribute
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductAttribute $productAttribute)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductAttribute  $productAttribute
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = ProductAttribute::find($id);
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
        if (!isset($request->category_attribute_id)) {
            return $this->returnErrorData('[category_attribute_id] Data Not Found', 404);
        }

        $check = CategoryAttribute::find($request->category_attribute_id);
        if (!$check) {
            return $this->returnErrorData('ไม่พบข้อมูล category_attribute_id ในระบบ', 404);
        }

        DB::beginTransaction();

        try {
            $Item = ProductAttribute::find($request->id);

            if (!$Item) {
                return $this->returnErrorData('ไม่พบรายการนี้ในระบบ', 404);
            }
            $Item->category_attribute_id = $request->category_attribute_id;
            $Item->name = $request->name;
            $Item->serial = $request->serial;
            $Item->sale_price = $request->sale_price;
            $Item->cost = $request->cost;
            $Item->qty = $request->qty;
            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/product_attributes/');
            }

            $Item->save();
            $allowedfileExtension = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
            $files = $request->file('images');
            $errors = [];
            if ($files) {
                foreach ($files as $file) {

                    if ($file->isValid()) {
                        $extension = $file->getClientOriginalExtension();

                        $check = in_array($extension, $allowedfileExtension);

                        if ($check) {
                            $Files = new ProductAttributeImages();
                            $Files->product_attribute_id =  $Item->id;
                            $Files->image = $this->uploadImage($file, '/images/product_attributes/');
                            $Files->save();
                        }
                    }
                }
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
}
