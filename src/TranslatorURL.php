<?php

namespace Translator;

use Translator\Contracts\TranslatorURLInterface;
use Illuminate\Http\Request;
use Translator\Localizer;
use Illuminate\Routing\UrlGenerator;
use Closure;


use Illuminate\Routing\RouteCollection;
use InvalidArgumentException;

class TranslatorURL extends UrlGenerator //implements TranslatorURLInterface
{
    /**
     * Create a new URL Generator instance.
     *
     * @param  \Illuminate\Routing\RouteCollection  $routes
     * @param  \Illuminate\Http\Request  $request
     * @param  \Translator\Localizer $localizer
     * @return void
     */
    public function __construct(RouteCollection $routes, Request $request, Localizer $localizer)
    {
        parent::__construct($routes, $request);
        $this->localizer = $localizer;
    }

    public function current($locale = NULL)
    {
      
        if(is_null($locale))
            return $this->to($this->request->getPathInfo());
        
        return $this->localize($this->request->getPathInfo(), $locale);
        
    }
    

    
    /**
     * Get the URL to a named route.
     * If no locale is required, current locale is searched.
     *
     * @param  string  $name Syntaxis: locale.routename.
     * @param  mixed   $parameters
     * @param  bool  $absolute
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function route($name, $parameters = [], $absolute = true){
        
        $parseName = $this->parseRouteName($name);
        $name = $parseName['name'];
        $locale = $parseName['locale'];
        
        if (!is_null($route = $this->routes->getByName($name))) {
            if(!$this->hasRouteLocale($route, $locale))
                return NULL;
            return $this->toRoute($route, $parameters, $absolute, $locale);
        }

        throw new InvalidArgumentException("Route [{$name}] not defined.");
    }
    
    /**
     * Get an object with URLs to a named route for all locales available.
     *
     * @param  string  $name Syntaxis: locale.routename.
     * @param  mixed   $parameters
     * @param  bool  $absolute
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function routes($name, $parameters = [], $absolute = true){
        if (!is_null($route = $this->routes->getByName($name))) {
            $routes = new \stdClass();
            foreach($this->localizer->getAvailable() as $locale){
                $uri = NULL;
                if($this->hasRouteLocale($route, $locale))
                    $uri = $this->toRoute($route, $parameters, $absolute, $locale);
                
                $routes->{$locale} = $uri;
            }
            return $routes;
        }
        throw new InvalidArgumentException("Route [{$name}] not defined.");
    }
    
    /**
     * Decode a locale and route name from a string with dot notation
     * @param string $name
     * @return array ['name' => 'routename', 'locale' => locale]
     */
    protected function parseRouteName($name){
        $pieces = explode('.', $name);
        if(count($pieces) > 1){
            $locale = $pieces[0];
            $routeName = count($pieces) > 2? implode('.', array_shift($pieces)): $pieces[1];
        }else{
            $locale = $this->localizer->getLocale();
            $routeName = $name;
        }
        return [
            'locale' => $locale,
            'name' => $routeName
        ];
    }
    
    
    /**
     * Get the URL for a given route instance.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  mixed  $parameters
     * @param  bool   $absolute
     * @return string
     */
    protected function toRoute($route, $parameters, $absolute, $locale = NULL)
    {
        $parameters = $this->formatParameters($parameters);

        $domain = $this->getRouteDomain($route, $parameters);
        

        $uri = strtr(
                rawurlencode(
                    $this->addQueryString(
                        $this->trimUrl(
                            $root = $this->replaceRoot($route, $domain, $parameters),
                            $this->localizer->getPrefixLocale($locale) . $this->replaceRouteParameters($route->getAction()['base_localization'], $parameters)
                        ),
                        $parameters
                    )
                ),
                $this->dontEncode
             );

        return $absolute ? $uri : '/'.ltrim(str_replace($root, '', $uri), '/');
    }
    
    
    /**
     * Verify if route has a locale
     * 
     * @param \Illuminate\Routing\Route  $route
     * @param string $locale
     * @return boolean
     */
    public function hasRouteLocale($route, $locale){
        if(( in_array('all', $route->getAction()['locales']) ||
             in_array($locale, $route->getAction()['locales'])
            ) &&
           in_array($locale, $this->localizer->getAvailable()))
            return true;
        return false;
    }
    
    /**
     * Verify if a route' name has a locale.
     * 
     * @param \Illuminate\Routing\Route  $route
     * @param string $locale
     * @return boolean
     */
    public function hasLocale($routename, $locale){
        if(!is_null($route = $this->routes->getByName($routename))){
            return $this->hasRouteLocale($route, $locale);
        }
        
        return false;
                
    }
    

    /**
     * Get an absolute or relative URL for a given URI and a locale.
     * 
     * @param string $uri
     * @param string $locale
     * @param bool $absolute
     * @return string
     */
    public function localize($uri, $locale, $absolute = true, $uriHasPrefix = true){
        if(!$this->localizer->isMain() || $this->localizer->isPrefixingMain() ){

        }
        
        if($uriHasPrefix){
            preg_match("/^\/?([^\/]+)\/(.*)/", $uri, $segments);
        
            if(count($segments) == 3){
                $prefix = $segments[1];
                $base = $segments[2];
            }else{
                $prefix = trim($uri, '/');
                $base = '';
            }

            if(!array_key_exists($prefix, $this->localizer->getAvailable())){
                
                $base = $uri;
            }
            
        }else{
            $base = $uri;
        }
        
       
        return $absolute ? 
            $this->to($this->addPrefix($base, $locale)) : 
            $this->addPrefix($base, $locale);
    }
    
    protected function addPrefix($baseURI, $locale){
        return trim($this->localizer->getPrefixLocale($locale).trim($baseURI, '/'), '/');
    }

}
