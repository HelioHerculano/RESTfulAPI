<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\ApiController;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductBuyerTransactionController extends ApiController
{
   
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,Product $product, User $buyer)
    {

        $rules = [
            'quantity' => 'required|integer|min:1'
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){
            return $this->errorResponse($validator->errors(),422);
        }

        if($buyer->id == $product->seller_id){
            return $this->errorResponse('The buyer must be different from the seller',409);
        }

        if(!$buyer->isVerified()){
            return $this->errorResponse('The buyer must be verified user',409);
        }

        if(!$product->seller->isVerified()){
            return $this->errorResponse('The seller must be verified user',409);
        }

        if(!$product->isAvailable()){
            return $this->errorResponse('The product is not available',409);
        }


        return DB::transaction(function() use ($request,$product,$buyer){
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
                'quantity' => $request->quantity,
                'buyer_id' => $buyer->id,
                'product_id' => $product->id
            ]);

            return $this->showOne($transaction,201);
        });
    }

}
