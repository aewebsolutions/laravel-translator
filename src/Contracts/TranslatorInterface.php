<?php

namespace Translator\Contracts;

interface TranslatorInterface
{
    public function text($localeGroupNeedle, $replacements, $orDefault);
    
    public function texts($localeGroupNeedle, $replacements);
    
    public function choice($localeGroupNeedle, $count, $replacements, $orDefault);
    
    public function get($localeGroupNeedle);
    
    public function paginate($count, $localeGroupNeedle);
    
    public function getGroup($name);
    
    public function getLocale($locale, $group);
    
    public function delete($localeGroupNeedle);
    
    public function create($localeGroupNeedle, $text, array $extra);
    
    public function update($localeGroupNeedle, $text, array $extra);
    
    public function updateGroupNeedle($groupNeedle, $newGroupNeedle);
    
    public function updated(\Closure $callback);
    
    public function created(\Closure $callback);
    
    public function deleted(\Closure $callback);

}
