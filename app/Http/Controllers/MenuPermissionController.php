<?php

namespace App\Http\Controllers;

use App\Models\MenuPermission;
use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuPermissionController extends Controller
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

        if (!isset($request->user_id)) {
            return $this->returnErrorData('[user_id] Data Not Found', 404);
        } else if (!isset($request->menu_id)) {
            return $this->returnErrorData('[menu_id] Data Not Found', 404);
        }

        $checkName = MenuPermission::where('user_id', $request->user_id)
            ->where('menu_id', $request->menu_id)
            ->where('actions', $request->actions)
            ->first();

        if ($checkName) {

            DB::beginTransaction();

            try {

                $checkName->delete();

                //log
                $userId = "Admin";
                $type = 'Delete Permission';
                $description = 'User ' . $userId . ' has ' . $type . ' ' . $request->menu_id;
                $this->Log($userId, $description, $type);
                //

                DB::commit();

                return $this->returnSuccess('Successful operation', []);
            } catch (\Throwable $e) {

                DB::rollback();

                return $this->returnErrorData('Something went wrong Please try again ' . $e, 404);
            }
        } else {

            DB::beginTransaction();

            try {

                $Item = new MenuPermission();
                $Item->user_id = $request->user_id;
                $Item->menu_id = $request->menu_id;

                $Item->actions = $request->actions;

                $Item->create_by = "Admin";

                $Item->save();

                //log
                $userId = "Admin";
                $type = 'Add Permission';
                $description = 'User ' . $userId . ' has ' . $type . ' ' . $request->menu_id;
                $this->Log($userId, $description, $type);
                //

                DB::commit();

                return $this->returnSuccess('Successful operation', []);
            } catch (\Throwable $e) {

                DB::rollback();

                return $this->returnErrorData('Something went wrong Please try again ' . $e, 404);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MenuPermission  $menuPermission
     * @return \Illuminate\Http\Response
     */
    public function show(MenuPermission $menuPermission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MenuPermission  $menuPermission
     * @return \Illuminate\Http\Response
     */
    public function edit(MenuPermission $menuPermission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MenuPermission  $menuPermission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MenuPermission $menuPermission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MenuPermission  $menuPermission
     * @return \Illuminate\Http\Response
     */
    public function destroy(MenuPermission $menuPermission)
    {
        //
    }

    public function checkAll(Request $request)
    {
        if (!isset($request->user_id)) {
            return $this->returnErrorData('[user_id] Data Not Found', 404);
        }

        DB::beginTransaction();

        try {

            if ($request->check == true) {
                $Menus = Menu::get()->toarray();

                for ($i = 0; $i < count($Menus); $i++) {
                    $Item = new MenuPermission();
                    $Item->user_id = $request->user_id;
                    $Item->menu_id = $Menus[$i]['id'];

                    $Item->actions = "View";

                    $Item->create_by = "Admin";

                    $Item->save();
                }
            } else {
                $Item = MenuPermission::where("user_id",$request->user_id)->get();

                for ($i = 0; $i < count($Item); $i++) {
                    $Item[$i]->delete();
                }
               
            }




            $log_type = 'แก้ไข การทำรายการข่าววัด';
            $log_description = 'ผู้ใช้งาน admin ได้ทำการ เพิ่มสิทธิเมนู';
            $this->Log("admin", $log_description, $log_type);

            DB::commit();

            return $this->returnSuccess('Successful operation', []);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('Something went wrong Please try again' . $e, 404);
        }
    }
}
