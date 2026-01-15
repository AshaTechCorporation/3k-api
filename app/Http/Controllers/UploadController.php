<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;

class UploadController extends Controller
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
     * @param  \App\Models\Upload  $upload
     * @return \Illuminate\Http\Response
     */
    public function show(Upload $upload)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Upload  $upload
     * @return \Illuminate\Http\Response
     */
    public function edit(Upload $upload)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Upload  $upload
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Upload $upload)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Upload  $upload
     * @return \Illuminate\Http\Response
     */
    public function destroy(Upload $upload)
    {
        //
    }

    public function uploadFile(Request $request)
    {

        try {

            if ($request->hasFile('file')) {

                $files = $request->file('file');
                $filePath = $request->file_path;
                $fileName = $request->file_name;

                $path_files = [];

                $destinationPath = public_path($request->path);

                $objScan = scandir($destinationPath);

                $file = $files;
                $filename = $file->getClientOriginalName();

                $str_filename = explode('.', $filename);
                $filetype = $str_filename[1];

                $dt = date("Y-m-d H:i:s");
                $key_gen = "$dt" . '_' . $fileName . "";
                $name = md5(uniqid($key_gen, true)) . '.' . "$filetype";

                $file->move($destinationPath . '/' . $filePath, $name);
                $path_files['name'] = $fileName;
                $path_files['path'] = $request->path.$name;

                return $path_files;
            } else {

                return $this->returnErrorData('File Not Found', 404);
            }
        } catch (\Throwable $e) {

            return $this->returnErrorData('Something went wrong Please try again ' . $e, 404);
        }
    }
}
