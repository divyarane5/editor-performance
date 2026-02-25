<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use App\Imports\TasksImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class UploadController extends Controller
{
    public function index()
    {
        return view('uploads.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        // 1️⃣ Save file
        $filePath = $request->file('file')->store('uploads');

        // 2️⃣ Create Upload record
        $upload = Upload::create([
            'file_name' => $request->file('file')->getClientOriginalName(),
            'month' => Carbon::now()->month,
            'year' => Carbon::now()->year,
        ]);

        // 3️⃣ Import Excel (PASS MODEL, NOT ID)
        Excel::import(new TasksImport($upload), $request->file('file'));

        return back()->with('success', 'Excel report uploaded to database successfully!');
    }
}