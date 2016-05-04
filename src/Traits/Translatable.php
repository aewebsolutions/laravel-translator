<?php

namespace Translator\Traits;

/**
 * This trait must be use inside Eloquent models. If a property does not exit,
 * model will search for a locale version. E.g., $model->name can return
 * $model->name_es if name property does not exit and current locale is 'es'.
 * 
 */
trait Translatable
{
    
    public function __get($key) {
        if($value = $this->getAttribute($key))
            return $value;
        
        return $this->getAttribute( $key . '_' . \App::getLocale());
        
    }
}
