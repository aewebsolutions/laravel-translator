<?php

if (!function_exists('tt')) {

    /**
     * @see \Translator\TranslatorRepository::text()
     * Shortcut for Translator::text().
     * 
     * @param string $groupDotNeedle E.g., "blog.title" or 'es.blog.title'
     * @param string $replaceRules E.g., ['name' => 'John']
     * @param string $locale Locale's code (e.g., "fr"), or "all" for all locales.
     * @param bool $orDefault If there is not text for locale, return default locale
     * @return mix String or array
     */
    function tt($localeGroupNeedle, $replacements = false, $orDefault = true){
        return call_user_func_array([app('Translator\TranslatorRepository'), 'text'], func_get_args());
    }
}

if (!function_exists('routes')) {

    /**
     * @see \Translator\URL::routes()
     * Get an object with URLs to a named route for all locales available.
     *
     * @param  string  $name Syntaxis: locale.routename.
     * @param  mixed   $parameters
     * @param  bool  $absolute
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    function routes($name, $parameters = [], $absolute = true){
        return call_user_func_array([app('url'), 'routes'], func_get_args());
    }
}

