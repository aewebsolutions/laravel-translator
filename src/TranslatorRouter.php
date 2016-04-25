<?php
namespace Translator;

use Closure;
use Translator\Localizer;
use Illuminate\Routing\Router;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Routing\Route;

class TranslatorRouter extends Router
{
    /**
     *
     * @var \Translator\Localizer 
     */
    private $localizer;
    
    /**
     *
     * @var \Illuminate\Routing\Router 
     */
    private $router;
    
    /**
     *
     * @var \Illuminate\Http\Request
     */
    private $request;
    
    private $locale;
    
    /**
     * Create a new TranslatorRouter instance.
     *
     * @param  \Translator\Localizer  $localizer
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @param  \Illuminate\Container\Container  $container
     * @return void
     */
    public function __construct(Localizer $localizer, Dispatcher $events, Container $container = null) {
        parent::__construct($events, $container);
        $this->localizer = $localizer;
    }

    
    /**
     * Create a new Route object.
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  mixed   $action
     * @return \Illuminate\Routing\Route
     */
    protected function newRoute($methods, $uri, $action)
    {
        $parse = $this->parseLocaleUriAction($uri, $action);
        return (new Route($methods, $parse['uri'] , $parse['action']))->setContainer($this->container);
    }
    
    protected function parseLocaleUriAction($uri, $action)
    {
        if(!is_array($action))
            $action = ['uses' => $action];       
        $action['base_localization'] = $uri;
        
        $action['locales'] = array_merge(
                $this->getLastGroupLocales(),
                $this->getActionLocales($action));
        
        $isAvailable = ( in_array($this->localizer->getLocale(), $action['locales']) || 
                         in_array('all', $action['locales']));
        
        if( $isAvailable ){
            if(!$this->localizer->isMain() || $this->localizer->isPrefixingMain())
                $uri =  array_search($this->localizer->getLocale(), $this->localizer->getAvailable ()). '/'. $uri;
     
        }else {
            // If current locale is not available for this route, uri must be
            // hidden for this request.
            $uri = md5($uri);
        }

        return compact('uri', 'action');
    }
    
    protected function getLastGroupLocales()
    {
        if (!empty($this->groupStack)) {
            $last = end($this->groupStack);

            return isset($last['locales']) ? (array) $last['locales'] : [];
        }

        return [];
    } 
    
    protected function getActionLocales($action)
    {
        if (is_array($action) && isset($action['locales'])) {
            return (array) $action['locales'];
        }

        return [];
    } 
    
    
    
}
