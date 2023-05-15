<?php

namespace App\Providers;

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
        \Validator::extend('current_password', function ($attribute, $value, $parameters, $validator) {
            
            if ( \Hash::check($value, \Auth::user()->password) ) {
                
                return true;
            }
            return false;
        });

        \Validator::extend('confirm_password', function ($attribute, $value, $parameters, $validator) {
            
            if ( $parameters[0] == $value ) {
                
                return true;
            }
            return false;
        });
    }
}
