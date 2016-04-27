# Laravel Translator
Laravel Translator is the most complete and easiest to use Laravel manager for dealing with multiple locales and translations.

Extending Laravel's Router and URLGenerator allows us to deal with multiple locales in Laravel's way. And the Translator Repository allows us to deal with translations for those locales.

It is worth to say that, if we want, we can maintain a **non-prefixed URL for our application's main locale**. So, we can get a clearer URL for our main locale (www.site.com/apple), and prefixed ones for all another supported locales (e.g., www.site.com/fr/apple).

To know more about developer, visite www.aesolucionesweb.com.ar

## Code Examples
```PHP
//Set a route
Route::get('apple', [
  'locales' => ['en', 'es'],
  'as' => 'apple_path'
  'uses' => 'fruitsController@apple' 
]);

//Get a URL from a route name
route('es.apple_path');

//Get a translated text
tt('fruits.apple'); 
```



## Compatibility
Laravel 5.*
## Installation
### Adding repository files to your proyect.
The best way to install Laravel Translator is with Composer. To install the most recent version, run the following command.
```
composer require aewebsolutions/laravel-translator
```
### Integration 
Then, you must add two lines in `config/app.php`. First, add a new provider to `$providers` array:
```
Translator\TranslatorServiceProvider::class,
```
Second, add the facade for RespositoryTranslator to `$aliases` array: 
```
‘Translator‘=> Translator\Facades\TranslatorFacade::class,
```
### Publishing
Some files need be copied from vendor directory.  Just run the following command:
```
php artisan vendor:publish --provider="Translator\TranslatorServiceProvider"
```
This command will create a `App\Translation` model file, a migration file and the `config/translation.php` file.
### Migration
A _translations_ table must be created in database.  Running Artisan's `migrate` command it will be enough. But If you like to add new columns to _ translations_ table,  you can do it before that, modifying  `2016_01_01_000000_create_translations_table.php` migration file. If you add new columns, yoy may need to add them too to `App\Translation::$fillable`  property.

Last, run:
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
Many configuration settings can be found in `config/translator.php`. There, you will find plenty information about each of them. For a basic usage, you must add all locales supported by application in `$locales_available` array. E.g., if application supports _en_, _fr_ and _es_ locales, array will look like this:
´´´PHP
'locales_available' => [  'es', 'en' , 'fr' ],
´´´
Main locale takes its value from application‘s default locale (see `locale` in `config/app.php` file). So, do not forget to set it correctly. 


## Usage

### Routing
#### Basics
This router is an extension of Laravel's one,  thus you will find original features exactly like you know them.

All working routes must have at least one locale available. 
 ```PHP
Route::get('apple', ['locales' => 'en',  'uses' => 'fruitsController@apple' ]);
```
You can associate, not just a single locale, but a group of them too. Also, if a route is available for all application‘s supported locales, you can use the ´all´ keyword.
```PHP
Route::get('apple', ['locales' => ['en', 'es'] ,  'uses' => 'fruitsController@apple'  ]);
```
Now, with default settings, next URIs will be available. Requesting them, application will set current locale each time automatically:
- /apple
- /es/apple

It is worth to say that URIs will be generated dynamically in order to optimize application. Router does not have to lead with multiplied rules, but just with those that match with requested locale. Imagine 40 route rules and 10 languages; instead of an unnecessary route rule multiplication (400 rules it is no cleaver to have), you will have exactly your 40 rules. 

#### Groups
You can assign locales to a group, not just to a single route.
```PHP
Route::group([ 'locales' =>  'en' , 'prefix' => 'fruits' ], function(){
    Route::get('apple/{color}', ['locales' => 'es', 'as' =>  'apple_path',  'uses' => 'fruitsController@apple' ]);
    Route::get('peach/{color}', [ 'as' =>  'peach_path',  'uses' => 'fruitsController@peach' ]);
});
```

### URL
To get a relative or absolute URL from a route name for the current locale, as usually, use either Laravel's helper route or `URL::route`:
```PHP
    route('apple_path', ['color' => 'red']);
```
If you need another locale, use dot notation.
```PHP
    route('es.apple_path', ['color' => 'red'] );
```
Also, you can get all URLs for all locales. Call either `Route::routes` method or `routes` helper:
```PHP
$url = routes('apple_path', ['color' => 'red'] );
echo $url->es;
echo $url->en;
```
Laravel ‘s `URL::current` has been modified. Now, you can pass as an optional parameter a locale.
```PHP
URL::current('es');
```

### Translations
Translations could be managed directly by ´App\Translation´ Eloquent model. But, you *should use the provided repository* in order to guarantee stability. `Translator` facade, probably, is all you need. 

#### Getting
You can get a translated text using `Translator::text` method or, better, the `tt`helper. This works like Laravel's `trans`. The `tt` method accepts a locale (optionally), a group name and a key name (needle) as its first argument. Let's assume that current locale is 'en':
 ```PHP
echo tt('fruits.apple');  // apple
echo tt('es.fruits.apple'); // manzana
```
Sometimes a text may have no translation for a available locale. By default, main locale would be shown. You can avoid this turning false its third argument.
```PHP
echo tt('fr.fruits.apple'); // apple
echo tt('fr.fruits.apple', [], false); // NULL
```
You can make *replacements* too, just like ´trans´ do:
```PHP
echo tt('messages.welcome');  // Hi, :name.
echo tt('messages.welcome', ['name' => 'John']);  // Hi, John.
```
Pluralization also works like Laravel do. Call `Translator::choice` method like you usually call Laravel's `trans_choice`.  You can add further arguments.
```PHP
echo Translator::choice('en.fruits.apple', 5, ['color' => 'red'], false);  // red apples.
echo tt('en.fruits.apple');  // :color apple|apples.
```
Last, If you need to get all texts for a group.needle, use `Transalor::texts`
```PHP
$texts = Transalor::texts('fruits.apple');
echo $texts->en; // apple
echo $texts->es; // manzana
echo $texts->fr; // NULL
```
#### Creating
Creating a text for an specific locale:
```PHP
Translator::create('es.fruits.peach', 'durazno');
```
Creating multiple locales at the same time:
```PHP
Translator::create(‘fruits.peach‘, [
  'es' => 'durazno', 
  'en' => 'peach',
  'fr' => 'pêche'
]);
```
Inserting with extra attributes. Important: extra columns attributes should have been added to translations table and should have been included in `App\Translation::$fillable` array property.
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
Updating a text for a specific locale or a group of them, with or without extra attributes:
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
Groups and needle are sensitive attributes, this is, they cannot be updated lightly. In short, there cannot be duplicates for a locale.group.needle key. So, `Translator::update` method does not allow you to change this attributes. Instead, you have to use `Translator::updateGroupNeedle`.
```PHP
// Updating the whole group:
Translator::updateGroupNeedle('fruits', 'juicy_fruits');

// Updating a needle:
Translator::updateGroupNeedle('fruits.peach', 'fruits.yellow_peach');

//Updating a single group.needle:
Translator::updateGroupNeedle('fruits.peach', 'juicy_fruits.peach');
```
#### Deleting
You can delete a whole group, a `group.needle` or a specific locale for a `group.needle`:
```PHP
Translator::delete('fruits'); 
Translator::delete('fruits.apple');
Translator::delete('es.fruits.apple');
```

## Methods
[Coming soon]

## License



