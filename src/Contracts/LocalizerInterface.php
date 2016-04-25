<?php

namespace Translator\Contracts;

interface LocalizerInterface
{
    
    public function getAvailable();
    
    public function getMainLocale();

    public function getPrefixLocale($locale = NULL);
        
    public function getLocale();

    public function replacePrefix($uri, $locale);

    public function setLocale();

    public function isCaching();
    
    public function isAutoflushing();
    
    public function isPrefixingMain();
    
    public function isMain();

}