# Laravel Aurora DSQL
Laravel Driver Support for Amazon Aurora DSQL 

> This package is in early development and not recommended for production yet. Contribute to make awesome!

## Installation

```shell
composer require breezybeasts/laravel-aurora-dsql
```

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
    'options'   => array(
        PDO::ATTR_PERSISTENT => true,
    ),
],
```

### AWS Credentials
Ensure your system is configured with proper AWS credentials.

### Creating Async Indexes
DSQL only supports creating indexes with an [ASYNC command](https://docs.aws.amazon.com/aurora-dsql/latest/userguide/working-with-create-index-async.html). 
This package provides an `asyncIndex` command to support creating DSQL compatible indexes.

```php
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





