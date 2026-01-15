<?php

namespace App\Http\Controllers;

use App\Models\PromotionList;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PromotionListController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PromotionList  $promotionList
     * @return \Illuminate\Http\Response
     */
    public function show(PromotionList $promotionList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PromotionList  $promotionList
     * @return \Illuminate\Http\Response
     */
    public function edit(PromotionList $promotionList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PromotionList  $promotionList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PromotionList $promotionList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PromotionList  $promotionList
     * @return \Illuminate\Http\Response
     */
    public function destroy(PromotionList $promotionList)
    {
        //
    }
}
