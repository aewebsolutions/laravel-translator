<?php

namespace Translator\Contracts;

interface TranslatorURLInterface
{
    public function route($name, $parameters = [], $absolute = true, $locale = NULL);
    
    public function routes($name, $parameters = [], $absolute = true);
    
    public function current($locale = NULL, $absolute = true);
    
    public function hasRouteLocale($route, $locale);
    
    public function hasLocale($routename, $locale);
    
    public function localize($uri, $locale, $absolute = true, $uriHasPrefix = true);
}
