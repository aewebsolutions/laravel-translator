<?php

namespace Translator;

use Closure;

trait TranslatorObserver
{
    /**
     * Register all listener. IF cache_auto_flush is turned on, after a group
     * is created, updated or deleted, cache will be flushed for this group.
     */
    private function registerEventListeners(){
        
        if($this->localizer->isAutoflushing() && $this->localizer->isCaching()){
            $translator = $this;
            $callback = function(array $locales, $group, $needle) use ($translator){
                $translator->cacheFlush($group);
            };
            $this->created($callback);
            $this->updated($callback);
            $this->deleted($callback);
        }
    }
    
    /**
     * Register a listener for created event.
     * Callback's params: array $locales, $group, $needle
     * 
     * @param Closure $callback 
     */
    public function created(Closure $callback){
        $this->event->listen('translator.created', $callback);
    }
    
    /**
     * Register a listener for updated event.
     * Callback's params: array $locales, $group, $needle
     * 
     * @param Closure $callback
     */
    public function updated(Closure $callback){
        $this->event->listen('translator.updated', $callback);
    }
    
    /**
     * Register a listener for deleted event.
     * Callback's params: array $locales, $group, $needle
     * 
     * @param Closure $callback
     */
    public function deleted(Closure $callback){
        $this->event->listen('translator.deleted', $callback);
    }
    
}