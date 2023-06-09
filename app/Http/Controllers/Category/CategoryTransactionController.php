<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryTransactionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Category $category)
    {
        $transactions = $category->products()   
                                 ->whereHas('transactions')//return only product hove tra
                                 ->with('transactions')
                                 ->get()
                                 ->pluck('transactions')
                                 ->collapse();
        
        return $this->showAll($transactions);
    }
}
