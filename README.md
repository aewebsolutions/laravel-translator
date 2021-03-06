# Laravel Translator
Laravel Translator is the most complete and easiest to use Laravel manager for dealing with multiple locales and translations.

Extending Laravel's Router and URLGenerator allows us to deal with multiple locales in Laravel's way. And the Translator Repository allows us to deal with translations for those locales.

It is worth to say that, if we want, we can reserve a **non-prefixed URL for our application's main locale**. Thus, from all supported locales, we would have a clearer URL only for our main locale (www.site.com/apple) and prefixed ones for the rest of them (e.g., www.site.com/fr/apple).

To know more about the developer, visit www.aesolucionesweb.com.ar



## Code Examples
```PHP
//Set a route
Route::get('apple', [
  'locales' => ['en', 'es'],
  'as' => 'apple_path',
  'uses' => 'fruitsController@apple' 
]);

//Get a URL from a route name
route('es.apple_path');

//Get a translated text
echo tt('fruits.apple'); 

//Get a title for current locale
$article = App\Article::find(1);
echo $article->title;
```

## Compatibility
Laravel 5.*

## Features
- Routes and urls manager.
- Complete repository for translate your whole application.
- Eloquent extension for translate attributes inside your models.
- Schema support.
- Cache optimization.

## Installation

### Adding repository files to your proyect.
The best way to install Laravel Translator is with Composer. To install the most 
recent version, run the following command.
```
composer require aewebsolutions/laravel-translator
```


### Integration 
Then, you must add two lines in `config/app.php`. First, add a new provider to `$providers` array:
```
Translator\TranslatorServiceProvider::class,
```
Second, for a proper access to TranslatorRespository, you must add a new facade to `$aliases` array: 
```
'Translator' => Translator\Facades\TranslatorFacade::class,
```
Optionally, if you wish to use our Schema support for an easily translatable tables creation,
you need to replace Schema facade. So, comment or remove Laravel's and add the new one:
```
//'Schema'    => Illuminate\Support\Facades\Schema::class,
'Schema'    => Translator\Facades\Schema::class,
```


### Publishing
Some files need be copied from vendor directory.  Just run the following command:
```
php artisan vendor:publish --provider="Translator\TranslatorServiceProvider"
```
This command will publish next files:
- `app\Translation.php`: an Eloquent model to deal with _translations_ table,
- `database\migrations\2016_01_01_000000_create_translations_table.php`: a migration file that will create _translations_ table,
- `config/translation.php`: a configuration file.


### Migration
A _translations_ table must be created in database.  Running Artisan's `migrate` 
command would be enough. But if you need to add extra columns to _translations_ table,  
you may do it before running command. Just add them to Schema::up in migration file. 
Also, you may need to add them too to `App\Translation::$fillable`array property.

Then, run:
```
php artisan migrate
```


### Extending Router
Add a new line to `App\Http\Kernel` in order to extend Laravel's Router.
```PHP
class Kernel extends HttpKernel
{
    use \Translator\Traits\KernelRouterExtender;
    //etc.
}
```


## Configuration
Configuration settings can be found in `config/translator.php` with plenty information.For basic usage, you must add all locales supported by application in `$locales_available` array. E.g., if application supports _en_, _fr_ and _es_ locales, array will look like this:
```PHP
'locales_available' => [  'es', 'en' , 'fr' ],
```
Main locale takes its value from application's default locale (see `locale` in `config/app.php` file). So, do not forget to set it correctly. 


## Usage

### Routing

#### Basics
This router is an extension of Laravel's,  thus you will find original features exactly like you know them.

All working routes must have at least one locale available. 
 ```PHP
Route::get('apple', [
  'locales' => 'en',  
  'uses' => 'fruitsController@apple'
]);
```
You can associate, not just a single locale, but a group of them too. Also, if you need a route to be available for the whole group of supported locales, you can use the ´all´ keyword.
```PHP
Route::get('apple', ['locales' => ['en', 'es'] ,  'uses' => 'fruitsController@apple'  ]);
Route::get('peach', ['locales' => 'all' ,  'uses' => 'fruitsController@peach'  ]);
```
Taking defaults settings, next URIs will be available in application for routes above.
- /apple
- /es/apple
- /peach
- /es/peach
- /fr/peach

Requesting any of them, current locale will be set automatically.

It is worth to say that URIs are generated dynamically in order to optimize. Router does not have to lead with multiplied rules, but just with those that match with requested locale. Imagine 40 route rules and 10 languages; instead of an unnecessary route rule multiplication, you will have exactly what you need: 40 rules.


#### Groups
You can assign locales to a group, not just to a single route.
```PHP
Route::group([ 'locales' =>  'en' , 'prefix' => 'fruits' ], function(){

    Route::get('apple/{color}', ['locales' => 'es', 'as' =>  'apple_path',  'uses' => 'fruitsController@apple' ]);
    
    Route::get('peach/{color}', [ 'as' =>  'peach_path',  'uses' => 'fruitsController@peach' ]);
    
});
```


### URL
To get a relative or absolute URL from a route name for the current locale, as usually, call either Laravel's `route` or `URL::route` methods:
```PHP
    route('apple_path', ['color' => 'red']);
```
If you ask for another locale, use dot notation. For getting a fallback locale's route, pass a locale (or set true) as the four argument.
```PHP
    route('es.apple_path', ['color' => 'red'] );
    route('es.apple_path', ['color' => 'red'], true, 'en' );
```
Also, you can get all URLs for all supported locales. Call either `Route::routes` or `routes` new methods:
```PHP
$url = routes('apple_path', ['color' => 'red'] );
echo $url->es;
echo $url->en;
```
Laravel's `URL::current` has been modified. Now, you can pass as an optional first argument a locale.
```PHP
URL::current('es');
```


### Translator Repository
Laravel Translator helps you to translate your application easily, dealing with translations from database.
Translations could be managed directly by `App\Translation` Eloquent model. But, you **should use the provided repository** in order to guarantee stability. `Translator` facade, probably, is all you need. 

#### Getting
You can get a translated text using `Translator::text` method or, better, the `tt`helper. This works like Laravel's `trans`. The `tt` method accepts a locale (optionally), a group name and a needle as its first argument, using dot notation: **locale.group.needle**. Let's assume that current locale is 'en':
 ```PHP
echo tt('fruits.apple');  // output: apple
echo tt('es.fruits.apple'); // manzana
```
Sometimes a text may have no translation for a locale available; so, main locale is shown. Turning `false` its third argument you can avoid this behavior.
```PHP
echo tt('fr.fruits.apple'); // apple
echo tt('fr.fruits.apple', [], false); // NULL
```
As `trans` do, you can make **replacements**:
```PHP
echo tt('messages.welcome');  //output: Hi, :name.
echo tt('messages.welcome', ['name' => 'John']);  //output: Hi, John.
```
**Pluralization**. `Translator::choice` works like Laravel's `trans_choice` (see Laravel Documentation), but with further arguments.
```PHP
echo Translator::choice('en.fruits.apple', 5, ['color' => 'red'], false);  // red apples.
echo tt('en.fruits.apple');  // :color apple|apples.
```
Last, If you need to get all texts from a group.needle, use `Transalor::texts`
```PHP
$texts = Transalor::texts('fruits.apple');
echo $texts->en; // apple
echo $texts->es; // manzana
echo $texts->fr; // NULL
```


#### Creating
You can create a text for an specific locale:
```PHP
Translator::create('es.fruits.peach', 'durazno');
```
Or create multiple locales at the same time:
```PHP
Translator::create(‘fruits.peach‘, [
  'es' => 'durazno', 
  'en' => 'peach',
  'fr' => 'pêche'
]);
```
Also, you can add extra attributes. Of corse, extra columns attributes should have been added to _translations_ table and should have been included in `App\Translation::$fillable` array property.
```PHP
Translator::create('fruits.peach', [
  'es' => 'durazno',  
  'en' => 'peach',
  'fr' => 'pêche'
 ], [
  'type' => 'infotext',
  'description' => 'Prunus persica‘s fruit'
]);
```


#### Updating
##### Updating a text 
Update a text for a specific locale or a group of them, with or without extra attributes:
```PHP
Translator::update('es.fruits.peach', 'melocotón');
Translator::update('fruits.peach', [
  'es' => 'melocotón',
  'en' => 'peach'
],[
  'type' => 'information'
]);
```

##### Updating a text‘s group name or needle 
Groups and needle are sensitive attributes, this is, they cannot be updated lightly without making a mess. In short, there cannot be duplicates for a locale.group.needle. So, even if you try, `Translator::update` method will not allow you to change this attributes. Instead, you must use `Translator::updateGroupNeedle`.
```PHP
// Change the whole group name:
Translator::updateGroupNeedle('fruits', 'juicy_fruits');

// Change the needle, but not the group:
Translator::updateGroupNeedle('fruits.peach', 'fruits.yellow_peach');

//Change a single group.needle:
Translator::updateGroupNeedle('fruits.peach', 'juicy_fruits.peach');
```


#### Deleting
Deleting is also easy with provided respository:
```PHP
//Delete the whole group
Translator::delete('fruits'); 

//Delete the group.needle for all locales
Translator::delete('fruits.apple');

//Delete a group.needle for a specific locale
Translator::delete('es.fruits.apple');
```

### Translatable Models
Laravel Translator includes, not only `Translator` repository, but also an Eloquent extension to manage multiple languajes directly inside your Models. 
Suppose you need an `Article` model. Would it be truly helpful if you were able to get properties like this:

```PHP
$article = App\Article::find(1);
echo $article->title; 
// output would be 'My title' if locale were 'en', 
// but 'Mi título' if locale were 'es';
```

#### Creating a table
Translatable columns have a clear syntaxis: column_name_locale. In order to simplify creation, optionally you can use our Schema extension (rememeber include facade. See #Installation).
All you have to do is call `$table->localize(['column_name'])` to multiply `column_name` for each locale available in application. Also, you can pass an array of locales
as a second argument.
```PHP
Schema::create('articles', function ($table) {
    $table->increments('id');
    $table->text('body');
    $table->string('title');
    $table->timestamps();

    $table->localize([ 'title', 'body' ]);
});
```

#### The Model
Two things must be done: to extend your model to `Translator\Eloquent\Model` and to fill `$translatable` protected property.
```PHP
class Article extends Translator\Eloquent\Model
{
    protected $translatable = ['title', 'body'];

    protected $fillable = ['title', 'body'];

}
```
Now, you can **get** a translatable property like this:
```PHP
$article = App\Article::find(1);
echo $article->title; // output 'My Title'

$title = $article->trans('title');
echo $title->es; // output 'Mi título
```

To **update**, **insert** or **fill** a row for current locale, you are able to do it like you usually do.
But if you need to manage several locales at once, you can do it with an array, just like this:
```PHP
//Modify an article
$article = App\Article::find(1);
$article->title = [
    'es' => 'Mi título en español',
    'en' => 'My Title in English'
];
$article->save();

//Insert an article
$article = new App\Article;
$article->fill([
    'title' => [
        'es' => 'Mi título en español',
        'en' => 'My Title in English'
    ]
]);
$article->save();
```

## Cache
In order to reduce database queries, groups should be stored in cache. Just look inside `conf/translator.php` and make sure that `$cache` is set `TRUE`. Laravel Translation uses your application's cache settings.

If you are using (as you should) provided repository to create, update or delete translations, then **cache does not need to be flushed manually**. Just be sure that `cache_auto_flush` is set `TRUE` and cache will be automatically flushed only for compromised groups each time Translator's `created`, `updated` or `deleted` events are fired.


## Methods
### TranslatorRespository (Facade: Translator)

Return | Method 
--- | --- 
string | **text**($localeGroupNeedle, $replacements = [], $orDefault = true) <br> Get a text for a locale.group.needle.
object | **texts**($groupNeedle, $replacements = []) <br> Get all texts for a group.needle.
string | **choice**($localeGroupNeedle, $count = 1, $replacements = [], $orDefault = true) <br> Choice between two or more string options.
Collection | **getGroup**($name) <br> Get a Collection from a group name.
Collection | **getLocale**($locale = NULL, $group = NULL) <br>Get a Collection from a locale and, optionally, a group name.
void | **cacheFlush**($group = NULL) <br> Remove a group or all groups from cache.
Collection | **get**($localeGroupNeedle = NULL) <br> Get a Collection from a locale (optionally), a group name and a needle. By default, get current locale.
void | **delete**($localeGroupNeedle) <br>Delete a whole group or a group.needle for a specific locale or for all locales.
void | **create**($localeGroupNeedle, $texts, array $extra = []) <br> Insert a text for an specific locale or for all locales at once.
void | **update**($localeGroupNeedle, $texts, array $extra = []) <br> Edit a text for an specific locale or for all locales at once.
void | **updateGroupNeedle**($groupNeedle, $newGroupNeedle) <br> Edit the whole group name, edit the needle but not the group or edit a single group.needle row:
void | **created**(Closure $callback) <br>Register a listener for `created` event. Callback's params: array `$locales`, `$group`, `$needle`.
void | **updated**(Closure $callback) <br>Register a listener for `updated` event. Callback's params: array `$locales`, `$group`, `$needle`.
void | **deleted**(Closure $callback) <br>Register a listener for `deleted` event. Callback's params: array `$locales`, `$group`, `$needle`.

### TranslatorURL (Facade: URL)
TranslatorURL extends URLGenerator.

Return | Method 
--- | --- 
string | **route**($name, $parameters = [], $absolute = true, $default = NULL) <br> Get the URL to a named route and locale (current locale, by default). Use dot notation.
object | **routes**($name, $parameters = [], $absolute = true) <br> Get an object with all URLs for all locales to a named route.
string | **current**($locale = NULL, $absolute = true) <br> Get the current URL for current locale or for another supported locale.
array | **currentAll**($absolute = true) <br> Get all current URLs for all locales available.
bool | **hasRouteLocale**($route, $locale) <br> Verify if route has a locale.
bool | **hasLocale**($routename, $locale) <br> Verify if a route' name has a locale.
string | **localize**($uri, $locale, $absolute = true, $uriHasPrefix = true) <br> Generate an absolute or relative URL for a given URI and a locale.


## License
Laravel Translator is licensed under the [MIT License](http://opensource.org/licenses/MIT).

Copyright 2016 Ángel Espro



