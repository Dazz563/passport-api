<?php

namespace App\Http\Controllers;

use Log;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function uploadProductImages(Request $req)
    {
        Log::info('Incoming request:', ['request' => $req->all()]);
        $req->validate([
            'files.*' => 'required|image',
        ]);

        $user_id = auth()->user()->id;

        $filenames = [];

        foreach ($req->file('files') as $file) {
            $dt = Carbon::now();
            $filename = $dt->format('YmdHis') . uniqid() . '_' . $user_id . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/product_images', $filename);
            $filenames[] = $filename;
        }

        return response()->json(["message" => 'Successfully uploaded your images', "data" => $filenames], 200);
    }
}
