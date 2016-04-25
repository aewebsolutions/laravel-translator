# Laravel's Translator
The most complete and easiest to use Laravel's package for managing multiple locales and translations.

Not only installation process is simpler, but also usage and configuration. Extending Laravel's Router and URLGenerator allows us to deal with multiple locales in Laravel's way. And Translator Repository allows us to deal with translations for those locales.
It is great to set a route (or a group of routes) like this:

```PHP
Route::get('apple', [
  'locales' => ['en', 'es'],
  'as' => 'apple_path'
  'uses' => 'fuitsController@apple' 
]);
```
And, then, in your Blade templates, to get a translated text like this: `tt('fruits.apple')` or `tt('es.fruits.apple')`, or get an URL like this: `route('es.apple_path')`.

Also, it is worth to say that this package lets us to maintain, if we want, a **non-prefixed URL for our application's main locale**. So, we can get a clearer URL for our main locale (www.site.com/apple), and prefixed ones for all another supported locales (e.g., www.site.com/fr/apple).


## Installation

## Configuration


