<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ProductImages;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function uploadProductImages(Request $req, $product_id)
    {
        $req->validate([
            'files.*' => 'required|image',
        ]);

        $user_id = auth()->user()->id;

        $filenames = [];

        foreach ($req->file('files') as $file) {
            $dt = Carbon::now();
            $filename = $dt->format('YmdHis') . uniqid() . '_' . $user_id . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/product_images', $filename);

            $productImage = new ProductImages();
            $productImage->image = $filename;
            $productImage->product_id = $product_id;
            $productImage->save();

            $filenames[] = $filename;
        }

        return response()->json(["message" => 'Successfully uploaded your images', "data" => $filenames], 200);
    }

    public function deleteProductImage($productImage_id)
    {
        $productImage = ProductImages::findOrFail($productImage_id);

        // Storage::delete($productImage->image);
        Storage::delete(str_replace("/storage/", "public/", $productImage->image));

        // Delete the record from the database
        $productImage->delete();

        // Return a response
        return response()->json(['message' => 'Product image deleted successfully']);
    }
}
