<?php

namespace Translator;

use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;
use Translator\Contracts\LocalizerInterface;

class Localizer implements LocalizerInterface
{
    /**
     *
     * @var \Illuminate\Config\Repository 
     */
    private $config;
    
    /**
     * Whether groups are caching or not. 
     * 
     * @var Bool
     */
    private $cache;
    
    /**
     * Whether autoflushing is activated. 
     * 
     * @var Bool 
     */
    private $autoflushing;
    
    /**
     * Whether prefix is also required for main locale. 
     * 
     * @var Bool 
     */
    private $prefixMain;
    
    /**
     * Array of locales available, taking key as URL prefixes and value as 
     * locale's code.
     * @var array 
     */
    private $available;
    
    /**
     * Main locale's code.
     * 
     * @var string 
     */
    private $main; 
    
    /**
     * Current locale setted by Localizer.
     * 
     * @var string
     */
    private $locale;


    public function __construct(Config $config, Request $request) {
        $this->config = $config;
        $this->main = $this->config->get('app.locale');
        $this->locale = $this->main;
        $this->request = $request;
        $this->setLocaleFromURL();
    }
    
    //Getters
    
    /**
     * Get an array of all supported locales, taking prefix URL as keys and
     * locales' code as values.
     * 
     * @return type
     */
    public function getAvailable(){
        if(is_null($this->available))
            $this->setAvailable();
        return $this->available;
    }
    
    /**
     * Get application's main locale
     * 
     * @return type
     */
    public function getMainLocale(){
        return $this->main;
    }
    
    /**
     * Get a URL prefix for a given locale or for current locale.
     * 
     * @param type $locale
     * @return string
     */
    public function getPrefixLocale($locale = NULL){
        $locale = is_null($locale)? $this->locale : $locale;
        if($locale == $this->main){
            return $this->isPrefixingMain() ? array_search($this->main, $this->getAvailable()) . '/' : '';
        }else{
            return array_search($locale, $this->getAvailable()) . '/';
        }
    }
    
    /**
     * Get current locale's code.
     * 
     * @return string 
     */
    public function getLocale(){
        return $this->locale;
    }
    
    /**
     * Get Cache timeout value
     * 
     * @return type
     */
    public function cacheTimeOut(){
        return  $this->config->get('translator.cache_timeout');
    }
    
    /**
     * Replace or add a prefix to given uri.
     * 
     * @param string $uri
     * @param string $locale
     * @return string
     */
    public function replacePrefix($uri, $locale){
        $uri = trim($uri, '/');
        preg_match("/^\/?(.+)\/(.*)/", $uri, $segments);
        if (count($segments) == 3) {
            $uri = trim($segments[2], '/');
        } else {
            $uri = isset($this->getAvailable()[$uri]) ? '' : $uri;
        }
        return trim($this->getPrefixLocale($locale).$uri, '/');
    }

    
    
    //Setters
    
    /**
     * Set an array of all locales supported by application, taking alias's URL
     * as keys and locales as values.
     * Array is stored in $available property.
     */
    protected function setAvailable(){
        $merged = [];
        $available = $this->config->get('translator.locales_available');
        $alias = $this->config->get('translator.alias_URL');
        
        if(is_null($available))
            return $this->available = [];
        
        foreach($available as $locale){
            $key = array_search( $locale, $alias, true );
            if(!$key)
                $key = $locale;
            $merged[$key] = $locale;
        }
        $this->available = $merged;
    }
    
    /**
     * Set application's locale. If main locale is prefixing, if first segment,
     * which should denote locale, can not be found in application's supported
     * locales, redirection is suggested. See redirect_no_prefix in config file.
     * 
     * @return void
     */
    public function setLocale(){
        
        $URLalias = $this->request->segment(1);
        
        if( !array_key_exists($URLalias, $this->getAvailable()) && 
            $this->isPrefixingMain() &&
            $this->config->get('translator.redirect_no_prefix')){
            
            // Redirect to main-prefixed-URL .

            $url = \App::make('url')->localize($this->request->getPathInfo(), $this->getMainLocale());
            \App::make('redirect')->to($url, 301)->send();
        }
        
        \App::setLocale($this->locale);
        
        // Set locale for Carbon
        \Carbon\Carbon::setLocale($this->locale);
    }
    
    /**
     * Set the current locale from URL prefix and from locales available.
     * 
     * return void
     */
    protected function setLocaleFromURL(){
        $URLalias = $this->request->segment(1);
        
        if(array_key_exists($URLalias, $this->getAvailable())){
            $this->locale = $this->getAvailable()[$URLalias];
        }
    }

    //Booleans 
   
    /**
     * Whether groups are stored in cache or not. 
     * 
     * @return bool
     */
    public function isCaching(){
        if(!is_null($this->cache))
            return $this->cache;
        
        return $this->cache = $this->config->get('translator.cache');
    }
    
    /**
     * Whether autoflushing is activated or not. 
     * 
     * @return bool
     */
    public function isAutoflushing(){
        if(!is_null($this->autoflushing))
            return $this->autoflushing;
        
        return $this->autoflushing = $this->config->get('translator.cache_auto_flush');
    }
    
    /**
     * Whether prefix is also required for main locale or not.
     * 
     * @return bool
     */
    public function isPrefixingMain(){
        if(!is_null($this->prefixMain))
            return $this->prefixMain;
        
        return $this->prefixMain = !$this->config->get('translator.remove_prefix');
    }
    
    /**
     * Whether current locale is main locale or not..
     * 
     * @return bool
     */
    public function isMain(){
        return $this->getLocale() == $this->getMainLocale();
    }
    
}
