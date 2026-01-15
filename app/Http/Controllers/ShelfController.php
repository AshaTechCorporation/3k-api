<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Floor;
use App\Models\Products;
use App\Models\Shelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ShelfController extends Controller
{

    public function getList()
    {
        $Item = Shelf::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['products'] = Products::where('shelve_id', $Item[$i]['id'])->get();
                $Item[$i]['channels'] = Channel::where('shelve_id', $Item[$i]['id'])->get();
                $Item[$i]['floors'] = Floor::where('shelve_id', $Item[$i]['id'])->get();
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

        $orderby = array('', 'name', 'detail', 'image', 'create_by');

        $D = Shelf::select($col);

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
                $d[$i]->products = Products::where('shelve_id', $d[$i]->id)->get();
                $d[$i]->channels = Channel::where('shelve_id', $d[$i]->id)->get();
                $d[$i]->floors = Floor::where('shelve_id', $d[$i]->id)->get();
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
            $Item = new Shelf();
            $Item->name = $request->name;
            $Item->detail = $request->detail;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/shelf/');
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shelf  $Shelf
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Shelf::where('id', $id)
            ->first();


        if (!empty($Item)) {

            $Item['products'] = Products::where('shelve_id', $Item['id'])->get();
            $Item['floors'] = Floor::where('shelve_id', $Item['id'])->orderBy('created_at', 'asc')->get();

            for ($i = 0; $i < count($Item['floors']); $i++) {
                $Item['floors'][$i]['channels'] = Channel::where('shelve_id', $Item['id'])
                    ->where('floor_id', $Item['floors'][$i]['id'])
                    ->get();

                for ($j = 0; $j < count($Item['floors'][$i]['channels']); $j++) {
                    $Item['floors'][$i]['channels'][$j]['product'] = Products::where('shelve_id', $Item['id'])
                        ->where('floor_id', $Item['floors'][$i]['id'])
                        ->where('channel_id', $Item['floors'][$i]['channels'][$j]['id'])
                        ->get();
                }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shelf  $Shelf
     * @return \Illuminate\Http\Response
     */
    public function edit(Shelf $Shelf)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shelf  $Shelf
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
            $Item = Shelf::find($id);
            $Item->name = $request->name;
            $Item->detail = $request->detail;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/shelf/');
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shelf  $Shelf
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Shelf::find($id);
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
            $Item = Shelf::find($request->id);

            if (!$Item) {
                return $this->returnErrorData('ไม่พบรายการ', 404);
            }

            $Item->name = $request->name;
            $Item->detail = $request->detail;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/shelf/');
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

    public function autoShelf(Request $request)
    {
        DB::beginTransaction();

        try {
            $Item = Floor::where('shelve_id', $request->id)->get();

            if (!empty($Item)) {
                for ($i = 0; $i < count($Item)-1; $i++) {

                    for ($n = 1; $n <= $request->qty; $n++) {
                        $ItemC = new Channel();

                        $ItemC->shelve_id = $request->id;
                        $ItemC->floor_id = $Item[$i]->id;
                        $ItemC->name = $Item[$i]->name . '' . $n;
                        $ItemC->detail = $Item[$i]->name . '' . $n;
                        $ItemC->save();
                    }
                }
            }

            //

            //

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }
}
