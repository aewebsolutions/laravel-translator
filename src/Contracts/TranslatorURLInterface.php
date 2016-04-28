<?php

namespace Translator\Contracts;

interface TranslatorURLInterface
{
    public function route($name, $parameters = [], $absolute = true);
    
    public function routes($name, $parameters = [], $absolute = true);
    
    public function current($locale = NULL);
    
    public function hasRouteLocale($route, $locale);
    
    public function hasLocale($routename, $locale);
    
    public function localize($uri, $locale, $absolute = true, $uriHasPrefix = true);
}
