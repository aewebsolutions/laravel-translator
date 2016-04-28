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
    
    /**
     * Prefix to store groups by cache. 
     * 
     * @var string 
     */
    protected $cachePrefix = 'translator.group.';
    
    
    /**
     * Get a text from a specified locale.group.needle.
     * 
     * @param string $localeGroupNeedle E.g., "blog.title" or "es.blog.title"
     * @param array $replacements Replacement rules E.g., ['name' => 'John']
     * @param bool $orDefault If there is not text for locale, return main locale
     * @return string
     */
    public function text($localeGroupNeedle, $replacements = [], $orDefault = true){
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
        
        if(count($replacements))
            $text = $this->makeReplacements($text, $replacements);
        
        return $text;
    }
    
    /**
     * Get texts for all locales. Returs an object with all available locales
     * as its properties.
     * 
     * @param string $groupDotNeedle E.g., "blog.title"
     * @param array $replacements Replacement rules. E.g., ['name' => 'John']
     * @return object
     */
    public function texts($groupDotNeedle, $replacements = []){
        
        $pointer = $this->parseLocaleGroupNeedle($groupDotNeedle);
        
        if(!$pointer->needle)
            return $this->getObjectLocales();
        
        $texts = $this->getAllTexts($pointer->group, $pointer->needle);
        
        if(count($replacements))
            foreach($texts as $locale => $text){
                $texts->$locale = $this->makeReplacements($text, $replacements);
            }
        
        return $texts;
    }
    
    /**
     * Choice between options. Similar to Laravel's choice() function.
     * If you had written 'apple | apples' in DB, you now can get 'apples' like:
     * Translator::choice('fruts.apple', 4)
     * 
     * @param string $localeGroupNeedle
     * @param number $count Number for search for inside intervals
     * @param array $replaceRules E.g., ['name' => 'John']
     * @param string $locale
     * @param bool $orDefault
     * @return mix
     */
    public function choice($localeGroupNeedle, $count = 1, $replacements = false, $orDefault = true){
        $text = $this->text($localeGroupNeedle, $replacements, $orDefault);
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
     * Get a Collection from a group name
     * 
     * @param string $name
     * @return Collection
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
    
    /**
     * Get a Collection from a locale and, optionally, a group name.
     * Current locale will be asume by default.
     * 
     * @param string $locale
     * @param string $group Group name
     * @return Collection
     */ 
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
