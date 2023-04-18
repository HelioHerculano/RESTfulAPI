<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\ApiController;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $product = Product::all();

        return $this->showAll($product);
    }


    public function show(Product $product)
    {
        return $this->showOne($product);
    }


}
