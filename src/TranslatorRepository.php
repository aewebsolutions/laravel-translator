<?php
namespace Translator;

use App\Translation;
use Translator\TranslationReceptor;
use Translator\TranslatorObserver;
use Translator\Contracts\TranslatorInterface;
use Translator\Localizer;
use Illuminate\Events\Dispatcher as Event;
use Illuminate\Cache\Repository as Cache;

class TranslatorRepository implements TranslatorInterface
{
    use TranslationReceptor, TranslatorObserver;
    
    /**
     * @var \App\Translation
     */
    protected $model;
    
    /*
     * 
     * @var \Illuminate\Cache\Repository
     */
    protected $cache;
    
    /*
     * 
     * @var \Illuminate\Events\Dispatcher
     */
    protected $event;
      
    /**
     *
     * @var \Translator\Localizer 
     */
    private $localizer;
    
    /**
     * Attributes that only can be updated by a secure mode.
     * 
     * @var type 
     */
    protected $sensitiveAttributes = ['group', 'locale', 'needle'];
    
    
    
    public function __construct(Translation $model, Localizer $localizer, Cache $cache, Event $event) {
        $this->localizer = $localizer;
        $this->model = $model;
        $this->cache = $cache;
        $this->event = $event;
        $this->registerEventListeners();
    }

    /**
     * Try to get
     * @param type $groupDotNeedle
     * @param type $lang
     * @return type
     */
    public function getOrRedirect($groupDotNeedle, $lang = NULL){
         $first = $this->text($groupDotNeedle, $lang);
         if($first){
             return $first;
         }else{
             $second = $this->text($groupDotNeedle, $this->localizer->getMainLocale());
             return $second ? redirect($this->route() , 301) : abort();
         }
    }
    
    /**
     * Get a collection of a group or of a [locale.]group.needle
     * If locale is not defined, all locales will be returned.
     * 'fruits.apple' will return all locales; 'es.fruit.apple' will return
     * only spanish locale row.
     * 
     * @param string $localeGroupNeedle Dot notation. String locale optional.
     * @return Collection of rows
     */
    public function get($localeGroupNeedle = NULL) {
        return $this->query($localeGroupNeedle)->get();
    }
    
    public function paginate($count = 15, $localeGroupNeedle = NULL) {
        return $this->query($localeGroupNeedle)->paginate($count);
    }
    
    /**
     * Delete a whole group or a [locale.]group.needle for a locale or for 
     * all locales.
     * 
     * @param string $localeGroupNeedle Dot notation. String locale optional.
     */
    public function delete($localeGroupNeedle){
        $this->query($localeGroupNeedle)->delete();
        $pointer = $this->parseLocaleGroupNeedle($localeGroupNeedle);
        if($pointer->group && $this->localizer->isAutoflushing() && $this->localizer->isCaching())
            $this->cacheFlush($pointer->group);
    }
    
    /**
     * Create a new group.needle for all locales, or some of them.
     * If a row of group, needle and locale already exists, new row will be ignored.
     * 
     * @param string $groupDotNeedle E.g., 'fruits.apple'
     * @param array $texts Texts for locales. E.g., ['es' => 'manzana', 'en' => 'apple']
     * @param array $extra Extra fillable columns. E.g., ['type' => 'home']
     */
    public function create($localeGroupNeedle, $texts, array $extra = []){

        $pointer = $this->parseLocaleGroupNeedle($localeGroupNeedle);
        
        //Created locales list. It will be used as a parameter for event
        $locales = []; 
        
        if(is_null($pointer->needle))
            return false;
        
        //Extra paramenter can not have sensitive attributes. So, we remove them.
        $extra = $this->removeSensitiveAttributes($extra);

        if(!is_null($pointer->locale)){
            
            if(!is_array($texts)){
                if($this->model->where('group', $pointer->group)->where('needle', $pointer->needle)->where('locale', $pointer->locale)->count() < 1){
                    $this->model->create(array_merge($extra, [
                        'locale' => $pointer->locale,
                        'group' => $pointer->group,
                        'needle' => $pointer->needle,
                        'text' => $texts
                    ]));
                    $locales[] = $pointer->locale;
                }
            }
            
        }else{
        
            foreach($texts as $locale => $text):
                //Duplicates avoid
                if($this->model->where('group', $pointer->group)->where('needle', $pointer->needle)->where('locale', $locale)->count() > 0)
                    continue;
                
                $this->model->create(array_merge($extra, [
                    'locale' => $locale,
                    'group' => $pointer->group,
                    'needle' => $pointer->needle,
                    'text' => $text,
                ]));
                $locales[] = $locale;
            endforeach;
            
        }
        
        if(count($locales))
            $this->event->fire('translator.created', [$locales, $pointer->group, $pointer->needle]);

    }

    /**
     * Update a single locale or a group of them for a group.needle.
     * For a single locale, use locale.group.needle notation as the first
     * param and a string as the second one. For a group of locales, use as the
     * first param group.needle notation, and an [locale => text] array as the
     * second one. 
     * For a secure needle or group string updating, see updateGroupNeedle method.
     * 
     * @param string $localeGroupNeedle E.g., 'fruits.apple' or 'es.fruits.apple'
     * @param string|array $texts Texts for locales. E.g., ['es' => 'manzana', 'en' => 'apple']
     * @param array $extra Extra fillable columns. E.g., ['type' => 'home']
     */
    public function update($localeGroupNeedle, $texts, array $extra = []){
        $pointer = $this->parseLocaleGroupNeedle($localeGroupNeedle);
        
        //Updated locales list. It will be used as a parameter for event
        $locales = [];
        
        if(is_null($pointer->needle))
            return false;
        
        //Extra paramenter can not have sensitive attributes. So, we remove them.
        $extra = $this->removeSensitiveAttributes($extra);
        
        //Two ways: a single locale to update or a list of locales.
        if(!is_null($pointer->locale)){
            
            if(!is_array($texts)){
                $row = $this->model->where('group', $pointer->group)->where('needle', $pointer->needle)->where('locale', $pointer->locale)->first();
                if($row->count()){
                    $row->update(array_merge($extra, [
                        'text' => $texts
                    ]));
                    $locales[] = $pointer->locale;
                }
            }
            
        }else{
            
            foreach($texts as $locale => $text){

                $row = $this->model->where('group', $pointer->group)->where('needle', $pointer->needle)->where('locale', $locale)->first();
                if(!$row->count())
                    continue;

                $row->update(array_merge($extra, [
                    'text' => $text
                ]));
                $locales[] = $locale;
            }
        }
        
        if(count($locales))
            $this->event->fire('translator.updated', [$locales, $pointer->group, $pointer->needle]);
    }
    
    
    /**
     * Update a whole group or a group.needle on the only secure way. 
     * If the new group or group.needle already exists, update will not be done.
     * Use this method to guarantee database stability.
     * 
     * @param string $localeGroupNeedle
     * @param string $newNeedle
     * @return bool
     */
    public function updateGroupNeedle($groupNeedle, $newGroupNeedle){
        
        $pointer = $this->parseLocaleGroupNeedle($groupNeedle);
        $newPointer = $this->parseLocaleGroupNeedle($newGroupNeedle);
        
        if(!$pointer->group 
                || !$newPointer->group 
                || ($pointer->needle && !$newPointer->needle)
                || $pointer->locale || $newPointer->locale
                )
            return false;
        
    
        //if the new group or new group.needle already exists, we can not update
        if ($this->query($newGroupNeedle)->count() > 0)
           return false;
        
        // We search for rows to update and, if they exist, update them and 
        // fire translator.updated event.
        $rows = $this->query($groupNeedle)->get();
        $locales = [];
        $needle = NULL;
        $newValues = ['group' => $newPointer->group];
        if($newPointer->needle){
           $newValues['needle'] = $newPointer->needle;
           $needle = $newPointer->needle;
        }
        foreach($rows as $row){
            $row->update($newValues);
            $locales[] = $row->locale;
        }
        
        if(count($locales))
            $this->event->fire('translator.updated', [$locales, $newPointer->group, $needle]);
        
        return true;
    }
    
    /**
     * Remove sensitive attributes from an array.
     * 
     * @param type $array
     * @return type
     */
    protected function removeSensitiveAttributes($array = []){
        foreach($array as $attribute => $value){
            if(in_array($attribute, $this->sensitiveAttributes))
                unset($array[$attribute]);
        }
        return $array;
    }
    
    /**
     * Prepare an Eloquent query. Parameter uses dot notation. Syntaxis: 
     * [locale.]group[.needle]. E.g., 'es.fruits.apple', 'fruits.apple', 'fruits'
     * 
     * @param string $localeGroupNeedle Dot notation.
     * @return type
     */
    protected function query($localeGroupNeedle = NULL) {
        $query = $this->model;

        if ($localeGroupNeedle) {
            
            $pointer = $this->parseLocaleGroupNeedle($localeGroupNeedle);
            
            $query = $query->where('group', $pointer->group);
            
            if ($pointer->locale)
                $query = $query->where('locale', $pointer->locale);

            if ($pointer->needle)
                $query = $query->where('needle', $pointer->needle);
        }
        return $query;
    }
    
    protected function parseLocaleGroupNeedle($string){
        $parts = explode('.', $string);
        $obj = new \stdClass();
        $obj->group = NULL;
        $obj->locale = NULL;
        $obj->needle = NULL;
        
        if (sizeof($parts) === 1){
            
            $obj->group = $parts[0];
            
        } else if (sizeof($parts) === 2 || !in_array($parts[0], $this->localizer->getAvailable())){
            
            $obj->group = $parts[0];
            array_shift($parts);
            $obj->needle = implode('.', $parts);
            
        } else {
            
            $obj->locale = $parts[0];
            $obj->group = $parts[1];
            unset($parts[0], $parts[1]);
            $obj->needle = implode('.', $parts);
        }
                        
        return $obj;
    }

}
