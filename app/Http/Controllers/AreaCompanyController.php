<?php

namespace App\Http\Controllers;

use App\Models\AreaCompany;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AreaCompanyController extends Controller
{
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

        if (!isset($request->area_id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            AreaCompany::where('area_id', $request->area_id)->delete();

            foreach ($request->companies as $key => $value) {

                $check = Company::find($value['companie_id']);

                if (!$check) {
                    return $this->returnErrorData('ไม่พบข้อมูลบริษัท', 404);
                }


                $Item = new AreaCompany();
                $Item->area_id = $request->area_id;
                $Item->companie_id = $value['companie_id'];

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
     * @param  \App\Models\AreaCompany  $areaCompany
     * @return \Illuminate\Http\Response
     */
    public function show(AreaCompany $areaCompany)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AreaCompany  $areaCompany
     * @return \Illuminate\Http\Response
     */
    public function edit(AreaCompany $areaCompany)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AreaCompany  $areaCompany
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AreaCompany $areaCompany)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AreaCompany  $areaCompany
     * @return \Illuminate\Http\Response
     */
    public function destroy(AreaCompany $areaCompany)
    {
        //
    }
}
