<?php

namespace Translator\Contracts;

interface TranslatorURLInterface
{
    public function route($name, $parameters = [], $absolute = true, $locale = NULL);
    
    public function current($locale = NULL, $absolute = true);
}