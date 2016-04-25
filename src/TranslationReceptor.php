<?php

namespace Translator;


trait TranslationReceptor
{
    /**
     * Groups of text that are being read. This speeds up application. 
     * 
     * @var array 
     */
    protected $groups = [];
    
    protected $cachePrefix = 'translator.group.';
    
    
    /**
     * Get a text or an array of locale => text for a needle.
     * 
     * @param string $groupDotNeedle E.g., "blog.title"
     * @param string $replaceRules E.g., ['name' => 'John']
     * @param string $locale Locale's code (e.g., "fr"), or "all" for all locales.
     * @param bool $orDefault If there is not text for locale, return main locale
     * @return mix String or array
     */
    public function text($localeGroupNeedle, $replacements = false, $orDefault = true){
        $pointer = $this->parseLocaleGroupNeedle($localeGroupNeedle);
        if(!$pointer->needle)
            return NULL; 
        
        if(!$pointer->locale)
            $pointer->locale = \App::getLocale();
        
        $text = $this->getText($pointer->group, $pointer->needle, $pointer->locale);
        
        // If there is no text for a speciffic locale, try with main locale
        if(!$text && $orDefault && $pointer->locale != $this->localizer->getMainLocale() ){
            $text = $this->getText($pointer->group, $pointer->needle, $this->localizer->getMainLocale());
        }
        
        if($replacements)
            $text = $this->makeReplacements($text, $replacements);
        
        return $text;
    }
    
    /**
     * Get texts for all locales. Returs an object with all available locales
     * as its properties.
     * 
     * @param string $groupDotNeedle E.g., "blog.title"
     * @return object
     */
    public function texts($groupDotNeedle){
        $pointer = $this->parseLocaleGroupNeedle($groupDotNeedle);
        if(!$pointer->needle)
            return $this->getObjectLocales();
        
        return $this->getAllTexts($pointer->group, $pointer->needle);
    }
    
    /**
     * Choice between options. Similar to Laravel's choice() function.
     * If you had written 'apple | apples' in DB, you now can get 'apples' like:
     * Translator::choice('fruts.apple', 4)
     * 
     * @param string $groupDotNeedle
     * @param number $count Number for search for inside intervals
     * @param array $replaceRules E.g., ['name' => 'John']
     * @param string $locale
     * @param bool $orDefault
     * @return mix
     */
    public function choice($groupDotNeedle, $count = 1, $replacements = false, $locale = NULL, $orDefault = true){
        $text = $this->text($groupDotNeedle, $replacements, $locale, $orDefault);
        $interval = new Interval();
        $interval->decodeStr($text);
        return $interval->search($count);
    }
    
    
    /**
     * 
     * @param type $text
     * @param array $rules Rules must by like ['needle' => 'replacement']
     * @return string
     */
    protected function makeReplacements($text, array $rules){
        foreach ($rules as $needle => $replacement){
            $text = str_replace(':'.$needle, $replacement, $text);
        }
        return $text;
    }
    
    /**
     * Get a group of rows
     * 
     * @param string $name
     * @return array
     */
    public function getGroup($name){
        
        //If group has not been stored in $this->group yet, store it.
        if(!array_key_exists($name, $this->groups)){
            
            if( $this->localizer->isCaching() && $this->cache->has( $this->cachePrefix.$name )){
                
                $this->groups[$name] = $this->cache->get( $this->cachePrefix.$name , 5);
              
            } else {
                
                $this->groups[$name] = $this->model->where('group', $name)->get();
                
                if($this->localizer->isCaching())
                    $this->cache->put( $this->cachePrefix.$name , $this->groups[$name], 5);
            }
        }
        return $this->groups[$name];
    }
    
    public function getLocale($locale = NULL, $group = NULL){
        If(is_null($locale))
            $locale = \App::getLocale();
        
        If(is_null($group))
            return $this->model->where('locale', $locale)->get();

        $rows = [];
        foreach ($this->getGroup($group) as $row) {
            if ($row->locale == $locale)
                $rows[] = $row;
        }
        return $rows;
    }
    
    /**
     * Get a text for a speciffic group, needle and locale.
     *  
     * @param string $group
     * @param string $needle
     * @param string $locale
     * @return string|NULL
     */
    protected function getText($group, $needle, $locale){
        foreach($this->getGroup($group) as $row){
            if($row->locale == $locale && $row->needle == $needle)
                return $row->text;
        }
        return NULL;
    }
    
    /**
     * Get all text of all available locales for a given group and needle. 
     * 
     * @param type $group
     * @param type $needle
     * @return type
     */
    protected function getAllTexts($group, $needle){
        $texts = $this->getObjectLocales();
        foreach($this->getGroup($group) as $row){
            if($row->needle == $needle)
                $texts->{$row->locale} = $row->text;
        }
        return $texts;
    }
    
    /**
     * Creates an object with all available locales as its properties.
     * @return \stdClass
     */
    protected function getObjectLocales(){
        $obj = new \stdClass();
        foreach($this->localizer->getAvailable() as $locale){
           $obj->{$locale} = NULL;
        }
        return $obj;
    }
    
    /**
     * Remove a group or all groups from cache.
     * 
     * @param type $group Name of a group. NULL if you want to remove all groups
     */
    public function cacheFlush($group = NULL){
        if($group)
            return $this->cache->forget($this->cachePrefix.$group);
        
        $groups = $this->model->select('group')->groupBy('group')->get();
        
        foreach($groups as $group){
            $this->cache->forget($this->cachePrefix.$group->group);
        }
    }

    

    
}
