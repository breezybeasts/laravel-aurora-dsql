# Laravel Aurora DSQL
Laravel Driver Support for Amazon Aurora DSQL 

> This package is in early development and not recommended for production yet. Contribute to make awesome!

## Installation

```shell
composer require breezybeasts/laravel-aurora-dsql
```

You can publish the config file with:
```shell
php artisan vendor:publish --tag="laravel-aurora-dsql-config"
```

This is the contents of the published config file:
```php
return [
    'migrations' => [
        'id' => 'uuid', // uuid, ulid, id
        'retries' => 5, // Handles OCC conflicts 
    ],
];


```

🤝
## Usage
Add the `aurora_dsql` driver and specify a region on your database config. The `aurora_dsql` driver trys to stay true to the default `pgsql` driver as possible.

### Database Config

```php
'pgsql' => [
    'driver' => 'aurora_dsql', // specified driver
    'region' => 'us-east-2', // region is required
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'postgres'),
    'username' => env('DB_USERNAME', 'admin'),
    'password' => "" // not used, auto generated,
    'charset' => env('DB_CHARSET', 'utf8'),
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => 'prefer',
],
```

### AWS Credentials
Ensure your system is configured with proper AWS credentials.

### Creating Async Indexes
DSQL supports creating standard indexes, but only on table with no data. If the table has data the index must be created with an [ASYNC command](https://docs.aws.amazon.com/aurora-dsql/latest/userguide/working-with-create-index-async.html). 
This package provides an `asyncIndex` command to support creating DSQL compatible indexes.

```php
use BreezyBeasts\AuroraDsql\Schema\Blueprint; // custom blueprint

Schema::table('users', function (Blueprint $table) {
    $table->asyncIndex(['email'])->unique());
});
```

### Migrations
One of the more major adjustments with DSQL is no serial support and running only one DDL operation per transaction. Laravel gives us full control over this in our own migration however this excluded the migrations table.
This package overrides the default migration behavior to use `ulid` instead of serial id's. 

The best way to work around the DDL limit is to split out migrations into separate files. Laravel should handle this just fine. The _other_ option is run migration outside of a transaction. This can be achieved by setting `$withTransactions` to false.

```php
return new class extends Migration
{

    public $withinTransaction = false;
    
    public function up(): void
    {
        // ...
    }
    
}

```

### Primary Keys
In DSQL a primary key cannot be added after the table has been created. 
This package will inline primary key designation when creating a table.

```php
 Schema::create('users', function (Blueprint $table) {
    $table->ulid('id')->primary(); // ✅ supported on create
});
```

```php
Schema::table('users', function (Blueprint $table) {
    $table->ulid('id')->primary(); // 💣💥 not supported on alter
});
```


### Dropping columns
At this time DSQL does not support dropping columns. A workaround is to migrate the table or ignore the column.

**Hide the attribute**
```php
<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Movie extends Model
{
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = ['score'];
}
```


**Create the table**
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public $withinTransaction = false;
    
    public function up(): void
    {
        // Step 1: Rename the existing table
        Schema::rename('movies', 'movies_old');

        // Step 2: Recreate the table without the unwanted column
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('score');
            // Exclude the column you want to drop (e.g., 'genre')
        });

        // Step 3: Copy the data to the new table
        DB::statement('INSERT INTO movies (id, title, score) SELECT id, title, score FROM movies_old');

        // Step 4: Drop the old table
        Schema::dropIfExists('movies_old');
    }

    public function down(): void
    {
        // Reverse the process if needed
        Schema::rename('movies', 'movies_new');

        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('score');
            $table->string('genre'); // Re-add the dropped column
        });

        DB::statement('INSERT INTO movies (id, title, score, genre) SELECT id, title, score, NULL AS genre FROM movies_new');

        Schema::dropIfExists('movies_new');
    }
};
```


## Models
Because Aurora DSQL does not support serial and DSQL overrides the id() blueprint to use a UUID or ULID
your models will most likely need to include the trait `HasUuids` or `HasUlids`.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
//use Illuminate\Database\Eloquent\Concerns\HasUlids;

class User extends Authenticatable
{ 
    use HasUuids; // or HasUlids
    
    // ....
    
}

```

It is entirely possible you don't want this if your application still wants to maintain an id based on an integer value.



Compromises/Issue
- prepared statements are globally disabled




