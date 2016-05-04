<?php

namespace Translator\Schema;
use Illuminate\Database\Schema\Blueprint as LaravelBlueprint;

class Blueprint extends LaravelBlueprint{
     /**
     * Create a new timestamp column on the table.
     *
     * @param  string|array  $columns
     * @param  string|array  $locales
     * @return \Illuminate\Support\Fluent
     */
    public function localize($columns, $locales = NULL)
    {
        $columns = (array) $columns;
        $locales = is_null($locales) ? \App::make('\Translator\Localizer')->getAvailable() : (array) $locales;
        $addedColumns = $this->getColumns();
        $toLocalize = [];
        
        foreach($addedColumns as $column){
            if(in_array($column['name'], $columns)){
                $toLocalize[] = $column;
            }
        }
        
        foreach($toLocalize as $column){
            foreach($locales as $locale){
                $clone = clone $column;
                $clone['name'] .= '_' . $locale;
                $this->columns[] = $clone;
            }
            $this->removeColumn($column['name']);
        }

    }
}
