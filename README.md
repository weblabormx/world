# Weblabor World

This is a PHP library to work with the World API.

## Requirements

- PHP 8.1 and later.

## Packagist

Install via packagist using:

```
composer require weblabormx/world
```

Don't forget to include the bindings:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

## Initialize credentials

You can choose to work with the library using the `World` static class like this:

```php
use WeblaborMx\World\World;

// World::setApiBase("https://world.weblabor/api"); # Optionally set an alternative base
World::init(apiKey: "YOUR_API_KEY");

$client = World::getClient();

// Do stuff...

```

Or by instantiating a client object:

```php
use WeblaborMx\World\Client;

$client = new Client(
    apiKey: 'YOUR_API_KEY',
    // apiBase: 'https://world.weblabor/api',
);

// Do stuff...

```

**The static way is recommended.**

## Divisions

The API works by getting the different levels of divisions of the world. Yoy may normally know them as countries, states and cities, but other nations call them different and have more than 3 levels.

So, the world is divided in different ways depending each different sovereign nation. 

We normally call, the sovereign nations, the upmost level of division, countries. The children of the countries, states. And the children of those states, cities. This is the way the API is used, by traversing the different levels of divisions. A division has `children()` and `parent()` unless it's the upmost or downmost level of division.


### Properties

All calls from the division endpoints, returns a `WeblaborMx\World\Entities\Division` object or array.

You can check the properties by inspecting the class constructor:

```php
public function __construct(
    public int $id,
    public ?string $name = null,
    public ?string $country = null,
    public ?string $a1code = null,
    public ?string $level = null,
    public ?int $population = null,
    public ?float $lat = null,
    public ?float $long = null,
    public ?string $timezone = null,
    public ?int $parent_id = null,
) {
}
```

## Endpoints

Check all endpoints in the [documentation](https://world.weblabor.mx/docs).

### Obtaining all countries

You probably want to start fetching divisions by their upmost level.

```php
use WeblaborMx\World\World;
use WeblaborMx\World\Entities\Division;

$client = World::getClient();

/** @var Division[] **/
$countries = $client->makeCall('/countries');

foreach ($countries as $division) {
    echo $division->name . \PHP_EOL;
}

```

### Get country by code

You can search a country by its [ISO-3166](https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes) code.

```php
use WeblaborMx\World\World;
use WeblaborMx\World\Entities\Division;

$client = World::getClient();

$code = "MX";

/** @var Division|null **/
$country = $client->makeCall("/country/{$code}");
```


### Getting a specific division

To get a specific division, you pass its previously obtained ID.

```php
use WeblaborMx\World\Entities\Division;

$id = 3531011; // Probably want to obtain it from the DB

/** @var Division|null **/
$division = Division::get($id);
```

### Obtaining all children

```php
/** @var Division[] **/
$children = $division->children();
```

### Obtaining parent

```php
/** @var Division|null **/
$parent = $division->parent();
```


## Working with Laravel

To maintain the library lightweight, no dependency was added. However, you can find a Division casting class, that although it doesn't implements the contract from Laravel, it should work as any other cast.

With the cast you can save any ID obtained in a model, and automatically obtain a `Division` when accesing the property again.

```php
use Illuminate\Database\Eloquent\Model;
use WeblaborMx\World\Casts\DivisionCast;

class Company extends Model
{
    protected $casts = [
        'country' => DivisionCast::class,
        'state' => DivisionCast::class,
    ];

    // ...
}
```

### Registering client

You probably want to initialize the client through a service provider like `AppServiceProvider`.

```php
public function boot(): void
{
    // World::setApiBase(config('services.weblabor.world.endpoint'));
    World::init(apiKey: config('services.weblabor.world.token'));
}
```

Then setup the `services.php` configuration.

```php
return [
    // ...
    'weblabor' => [
        'world' => [
            'endpoint' => env('WEBLABOR_WORLD_ENDPOINT', 'https://world.weblabor.mx/api'),
            'token' => env('WEBLABOR_WORLD_TOKEN'),
        ]
    ]
]
```

And the credentials in `.env`.

```
WEBLABOR_WORLD_ENDPOINT=
WEBLABOR_WORLD_TOKEN=
```