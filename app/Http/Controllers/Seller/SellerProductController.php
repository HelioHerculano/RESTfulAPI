<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Seller $seller)
    {
        $products = $seller->products;

        return $this->showAll($products);
    }

    public function store(Request $request, User $seller){

        $rules = [
            'name' => 'required',
            'description' => 'required',
            'quantity' => 'required|integer|min:1',
            'image' => 'required|image',
        ];

        $validator = Validator::make($request->all(),$rules);

        if(!$validator->fails()){

            $data = $request->all();

            $data['status'] = Product::UNAVAILABLE_PRODUCT;
            $data['image'] = '2.jpg';
            $data['seller_id'] = $seller->id;

            $product = Product::create($data);

            return $this->showOne($product);

        }

        return $this->errorResponse($validator->errors(),422);

    }

    public function update(Request $request,Seller $seller, Product $product){
        $rules = [
            'quantity' => 'integer|min:1',
            'status' => 'in:' . Product::AVAILABLE_PRODUCT . ',' . Product::UNAVAILABLE_PRODUCT,
            'image' => 'image',
        ];    

        $validator = Validator::make($request->all(),$rules);

        if(!$validator->fails()){

            $this->checkSeller($seller,$product);

            $product->fill($request->only([
                'name',
                'description',
                'quantity',
            ]));

            if($request->has('status')){
                $product->status = $request->status;

                if($product->isAvailable() && $product->Categories()->count() == 0){
                    return $this->errorResponse('An active product must have at least one category',409);
                }
            }

            //The method isClean() verifie that anything changed at product table our not 
            if($product->isClean()){
                return $this->errorResponse('You neeed to specify a different value to update',422);
            }

            $product->update();

            return $this->showOne($product);
        }

        return $this->errorResponse($validator->errors(),422);

    }

    public function destroy(Seller $seller, Product $product){

            $this->checkSeller($seller,$product);


            $product->delete();

            return $this->showOne($product);
    

    }

    protected function checkSeller(Seller $seller, Product $product){
        if($seller->id != $product->seller_id){
            throw new HttpException(422,'The specified seller is not the actual seller of the product');
        }
    }

}
