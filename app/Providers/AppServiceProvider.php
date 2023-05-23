<?php

namespace App\Providers;

use App\Mail\UserCreated;
use App\Mail\UserMailChanged;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::created(function($user){
           retry(5,function() use ($user) {
            Mail::to($user->email)->send(new UserCreated($user));
           }, 100);
        });

        User::updated(function($user){
            if($user->isDirty('email')){
                retry(5,function() use ($user) {
                 Mail::to($user)->send(new UserMailChanged($user));
                }, 100);
            }
        });

        Product::updated(function($product){
            if($product->quantity == 0 && $product->isAvailable()){
                $product->status = Product::UNAVAILABLE_PRODUCT;

                $product->save();
            }
        });
    }
}
