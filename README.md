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

## Properties

All calls from the division endpoints, returns a `WeblaborMx\World\Entities\Division` object or array.

You can check the properties by inspecting the class constructor:

```php
public function __construct(
    public readonly int $id,
    public readonly ?string $name = null,
    public readonly ?string $country = null,
    public readonly ?string $a1code = null,
    public readonly ?string $level = null,
    public readonly ?int $population = null,
    public readonly ?float $lat = null,
    public readonly ?float $long = null,
    public readonly ?string $timezone = null,
    public readonly ?int $parent_id = null,
) {
}
```

### Getting a division

```php
use WeblaborMx\World\Entities\Division;

$division = Division::get(3531011); // Static way

/** @var Division|null **/
$parent = $division->parent();
/** @var Division[] **/
$children = $division->children();

```

### Obtaining all countries

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


## Working with Laravel

To maintain the library lightweight, no dependency was added. However, you can find a Division casting class, that although it doesn't implements the contract from Laravel, it should work as any other cast.

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