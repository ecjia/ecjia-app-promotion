<?php

namespace Ecjia\App\Promotion;

use Royalcms\Component\App\AppServiceProvider;

class PromotionServiceProvider extends  AppServiceProvider
{
    
    public function boot()
    {
        $this->package('ecjia/app-promotion');
    }
    
    public function register()
    {
        
    }
    
    
    
}