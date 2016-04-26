<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Locales available
    |--------------------------------------------------------------------------
    |
    | All locales available supported by application. This list will be used
    | for getting translated texts from database and routing.
    | Set 'locale' as the main locale in config/app.php file.
    | 
    */
    
    'locales_available' => ['es', 'en', 'fr'],

    
    /*
    |--------------------------------------------------------------------------
    | URL prefix-alias for locales
    |--------------------------------------------------------------------------
    |
    | Available locales are shown itselves as URL's prefixes. But you can make
    | them look nicer with an alias. E.g., set 'br' => 'pt-br' if you want to 
    | get URLs like www.yourweb.com/br/nicer/path
    */
    
    'alias_URL' => [
        //'br' => 'pt-br'
        //'uk' => 'en-gb'
    ],
    
    
    /*
    |--------------------------------------------------------------------------
    | Remove prefix main locale
    |--------------------------------------------------------------------------
    |
    | You can remove the main locale's prefix from URL. So, you can reserve
    | prefixless URLs for main locale.
    */
    
    'remove_prefix' => true,
    
    
    /*
    |--------------------------------------------------------------------------
    | Redirect to main locale when no prefix is found in URL
    |--------------------------------------------------------------------------
    |
    | When remove_prefix is false and requested url is prefixless, you can force
    | redirect to main prefixed url version setting true this option.
    */
    
    'redirect_no_prefix' => true,
        
    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Group of texts should be cached for speed up the application. 
    | Translator uses Laravel's cache service. So, make sure to set 
    | config/cache.php file properly.
    | Turn true for enabling cache. 
    | 
    */
    
    'cache' => true,
    
    /*
    |--------------------------------------------------------------------------
    | Cache Timeout
    |--------------------------------------------------------------------------
    |
    | You must specify the number of minutes for which the group of text should 
    | be cached.
    | 
    */
    
    'cache_timeout' => 60,
    
    /*
    |--------------------------------------------------------------------------
    | Cache Auto Flush
    |--------------------------------------------------------------------------
    |
    | When a group is created, updated or deleted from database, cache will 
    | automatically flush.
    | 
    */
    
    'cache_auto_flush' => true,
    
];
