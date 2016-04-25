<?php
namespace Translator\Facades;
use Illuminate\Support\Facades\Facade;

class TranslatorFacade extends Facade
{
    protected static function getFacadeAccessor() { 
        return 'Translator\TranslatorRepository'; 
    }
}