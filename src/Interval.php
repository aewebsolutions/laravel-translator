<?php

namespace Translator;

class Interval
{
    /**
     * Invervals have this form: [ [ [0,2] , text] , [ [3,null] , text] ]
     * 
     * @var array 
     */
    public $intervals;
    
    /**
     * Decode a string into an interval. Items must be separated by pipe.
     * Intervals must be first in item. E.g.:
     * '{0} Nothing | [1, 3] One to Three | [4, inf] Four or more'.
     * Also, two items with no {} or [] are equal to '{1} One | [2, inf] Plural'
     * 
     * @param String $str
     */
    public function decodeStr($str){
        $parts = explode('|' , $str);
        preg_match("/^\s*[{\[]\d+\s*(?:,\s*[\d\w]+)?[}\]]/", $parts[0], $match);

        //if there is no {1} or [4, 10] in first part
        if(!count($match)){
            if(count($parts) > 1){
                $this->intervals[] = [ 1, $parts[0] ];
                $this->intervals[] = [ [2, NULL] , $parts[1] ];
            }else{
                $this->intervals[] = [ 1 , $parts[0] ];
            }
        }else{
            foreach($parts as $part):
                preg_match("/^\s*{(\d+)}\s*(.*)/", $part, $match);
                if(count($match) === 3){
                    $this->intervals[] = [ $match[1] , $match[2] ];
                    continue;
                }
                preg_match("/^\s*\[(\d+)\s*,\s*([\d\w]+)\]\s*(.*)/", $part, $match);
                if(count($match) === 4){
                    $this->intervals[] = [ [$match[1], (is_numeric($match[2])? $match[2] : NULL) ] , $match[3] ];
                    continue;
                }
            endforeach;
        }
    }
    
    /**
     * Serch for an item inside intrvals.
     * 
     * @param number $count 
     * @return type
     */
    public function search($count){
        foreach($this->intervals as $interval){
            $key = $interval[0];
            $val = $interval[1];
            
            if(!is_array($key)){
                if($key == $count)
                   return $val;
            }else{
                
                if($key[1]){
                    if($key[0] <= $count && $key[1] >= $count)
                        return $val;
                }else{
                    if($key[0] <= $count)
                        return $val;
                }
            }
        }
    }
}
