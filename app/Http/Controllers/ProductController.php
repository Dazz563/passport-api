<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        $user_id = $req->user()->id;

        $products = Product::where('user_id', $user_id)->orderBy('created_at', 'desc')->withTrashed()->get();

        return response()->json(['data' => $products]);
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
    public function store(ProductRequest $req)
    {
        $data = $req->all();

        $data['user_id'] = $req->user()->id;

        $newProduct = Product::create($data);

        return response()->json(['message' => 'Product created successfully', 'data' => $newProduct], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);

        return response()->json(['message' => 'Product found', 'data' => $product], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $req, $id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        // check to see that the user id hasn't been tampered with in the request 
        if (Auth::user()->id != $product->user_id) {
            return response()->json(['error' => 'You cannot change this record',], 403);
        }

        $product->update($req->all());

        return response()->json(['message' => 'Product updated successfully', 'data' => $product], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Product::where('id', $id)->delete();

        $softDeletedProduct = Product::withTrashed()->findOrFail($id);

        return response()->json(['message' => 'Product set to inactive', 'data' => $softDeletedProduct], 200);
    }

    public function restoreProduct($id)
    {
        $product = Product::withTrashed()->find($id);

        if ($product) {
            $product->restore();
            return response()->json(['message' => 'Product restored successfully', 'data' => $product], 200);
        } else {
            return response()->json(['message' => 'Product not found'], 404);
        }
    }
}
