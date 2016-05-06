<?php

namespace Translator\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class Model extends Eloquent
{
    /**
     * List of all translatable fields.
     * 
     * @var array 
     */
    protected $translatable = [];
    
    /**
     * Whether model is able to return a null value if value or attribute_locale
     * do not exist, or if it must try to return main locale's value. 
     * 
     * @var bool 
     */
    protected $nullable = false;
    

    public function __get($key) {
        if(in_array($key, $this->translatable))
            return $this->getTranslatable($key);

        return parent::__get($key);
    }
    
    /**
     * Get a translatable attribute.
     * 
     * @param string $key
     * @return string
     */
    protected function getTranslatable($key){
        
        if(!is_null( $value = $this->getAttribute( $key . '_' . \App::getLocale())))
           return $value;
        
        if($this->nullable)
            return NULL;
        
        return $this->getAttribute( $key . '_' . \App::make('Translator\Localizer')->getMainLocale());

    }
    

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];
        
        //This is the only line that we have changed:
        foreach ($this->parseTransAttributes() as $key => $value) {

            if (!array_key_exists($key, $this->original)) {
                
                $dirty[$key] = $value;
                
            } elseif ($value !== $this->original[$key] &&
                      !$this->originalIsNumericallyEquivalent($key)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }
    
    /**
     * Perform a model insert operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $options
     * @return bool
     */
    protected function performInsert(Builder $query, array $options = [])
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }
        if ($this->timestamps && Arr::get($options, 'timestamps', true)) {
            $this->updateTimestamps();
        }

        //This is the only line that we have changed:
        $attributes = $this->parseTransAttributes();

        if ($this->incrementing) {
            $this->insertAndSetId($query, $attributes);
        }

        else {
            $query->insert($attributes);
        }
        $this->exists = true;

        $this->fireModelEvent('created', false);

        return true;
    }
    
    /**
     * Take all attributes and parse translatable ones. This method will return
     * a the attributes array with some changes for uploading or inserting.
     * 
     * @return array
     */
    protected function parseTransAttributes(){
        $attributes = [];
        foreach($this->attributes as $key => $value){
            
            if (in_array($key, $this->translatable)) {
                
                $attributes = $this->pushTransAttribute($attributes, $key);
                   
                continue;
            }
            $attributes[$key] = $value;
        }
        return $attributes;
    }
    
    /**
     * Add translatable attributes to an array.
     * 
     * @param array $array
     * @param string $attribute
     * @return array
     */
    protected function pushTransAttribute(array $array, $attribute) {

        if (!is_array($this->attributes[$attribute]))
            $array[$attribute . '_' . \App::getLocale()] = $this->attributes[$attribute];
        else
            foreach ($this->attributes[$attribute] as $locale => $text) {
                $array[$attribute . '_' . $locale] = $text;
            }
        return $array;
    }
    
    public function trans($key){
        $locales = \App::make('Translator\Localizer')->getAvailable();
        $values = new \stdClass();
        
        foreach($locales as $locale){
            $values->{$locale} =  $this->getAttribute( $key . '_' . $locale);
        }
        return $values;
    }

}
