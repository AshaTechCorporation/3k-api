<?php

namespace App\Http\Controllers;

use App\Models\ClientFiles;
use App\Models\Clients;
use App\Models\Orders;
use App\Models\OrderShop;
use Facade\FlareClient\Http\Client;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ClientsController extends Controller
{
    public function getList()
    {
        $Item = Clients::get()->toarray();

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
        $type = $request->type;

        $col = array('id', 'name', 'phone', 'idcard','type', 'email', 'address', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'name', 'phone', 'idcard','type', 'email', 'address', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = Clients::select($col);

        if ($type) {
            $D->where('type', $type);
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

        $check = Clients::where('phone', $request->phone)->first();
        if ($check) {
            return $this->returnErrorData('มีข้อมูล phone ในระบบแล้ว', 404);
        }

        DB::beginTransaction();

        try {
            $prefix = "#C-";
            $id = IdGenerator::generate(['table' => 'clients', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);

            $Item = new Clients();
            $Item->code = $id;
            $Item->name = $request->name;
            $Item->phone = $request->phone;
            $Item->phone2 = $request->phone2;
            $Item->email = $request->email;
            $Item->address = $request->address;
            $Item->age = $request->age;
            $Item->gender = $request->gender;
            $Item->idcard = $request->idcard;
            $Item->company = $request->company;
            $Item->type = $request->type;

            $Item->sale_id = $loginBy->id;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/client/');
            }

            $Item->save();

            $files = $request->file('files');

            if ($files) {
                foreach ($files as $file) {

                    if ($file->isValid()) {
                        $Files = new ClientFiles();
                        $Files->client_id =  $Item->id;
                        $Files->file = $this->uploadFile($file, $file->getPath());
                        $Files->save();
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
     * @param  \App\Models\Clients  $clients
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Clients::where('id', $id)
            ->first();

        if ($Item) {
            $Item->orders = Orders::where('client_id', $id)->get();
            $Item->files = ClientFiles::where('client_id', $id)->get();
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Clients  $clients
     * @return \Illuminate\Http\Response
     */
    public function edit(Clients $clients)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Clients  $clients
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
            $Item = Clients::find($id);
            $Item->name = $request->name;
            $Item->phone = $request->phone;
            $Item->email = $request->email;
            $Item->address = $request->address;

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
     * @param  \App\Models\Clients  $clients
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Clients::find($id);
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
        $loginBy = $request->login_by;

        if (!isset($request->id)) {
            return $this->returnErrorData('[id] Data Not Found', 404);
        }

        DB::beginTransaction();

        try {
            $Item = Clients::find($request->id);

            if (!$Item) {
                return $this->returnErrorData('ไม่พบรายการ', 404);
            }

            $Item->name = $request->name;
            $Item->phone = $request->phone;
            $Item->phone2 = $request->phone2;
            $Item->email = $request->email;
            $Item->address = $request->address;
            $Item->age = $request->age;
            $Item->gender = $request->gender;
            $Item->idcard = $request->idcard;
            $Item->company = $request->company;
            $Item->type = $request->type;
            $Item->sale_id = $loginBy->id;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/client/');
            }

            $Item->save();

            $files = $request->file('files');

            if ($files) {
                foreach ($files as $file) {

                    if ($file->isValid()) {
                        $Files = new ClientFiles();
                        $Files->client_id =  $Item->id;
                        $Files->file = $this->uploadFile($file, $file->getPath());
                        $Files->save();
                    }
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
}
