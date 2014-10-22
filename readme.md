# Laravel Querier

This package gives you an easy way to leverage queries for Command-Query Separation Principle in your Laravel projects.

## Installation

Per usual, install Querier through Composer.

```js
"require": {
    "smgladkovskiy/querier": "~1.0"
}
```

Next, update `app/config/app.php` to include a reference to this package's service provider in the providers array.

```php
'providers' => [
    'SMGladkovskiy\Querier\QuerierServiceProvider'
]
```

## Usage

Easily, the most important piece of advice I can offer is to keep in mind that this approach isn't for everything. If you're building a simple CRUD app that does not have much business logic, then you likely don't need this. Still want to move ahead? Okay - onward!

### The Goal

Imagine that you're building an app for advertising job listings. Now, when an employer posts a new job listing, a number of things need to happen, right?
Well, don't put all that stuff into your controller! Instead, let's leverage querys, handlers, and domain events to clean up our code.

### The Controller

To begin, we can inject this package's `QuerierTrait` into your controller (or a BaseController, if you wish). This will give you a couple helper methods to manage the process of passing queries to the query bus.

```php
<?php

use SMGladkovskiy\Querier\QuerierTrait;

class JobsController extends \BaseController {

	use QuerierTrait;

	/**
	 * Publish the new job listing.
	 *
	 * @return Response
	 */
	public function store()
	{

	}

}
```

Good? Next, we'll represent this "instruction" (to post a job listing) as a query. This will be nothing more than a simple DTO.

```php
<?php

use SMGladkovskiy\Querier\QuerierTrait;
use Acme\Jobs\VacanciesListQuery;

class JobsController extends \BaseController {

	use QuerierTrait;

	/**
	 * Post the new job listing.
	 *
	 * @return Response
	 */
	public function store()
	{
        $this->executeQuery(VacanciesListQuery::class);

		return Redirect::home();
	}
```

Notice how we are representing the user's instruction (or query) as a readable class: `VacanciesListQuery`. The `execute` method will expect the query's class path, as a string. Above, we're using the helpful `VacanciesListQuery::class` to fetch this. Alternatively, you could manually write out the path as a string.

### The Query DTO

Pretty simply, huh? We make a query to represent the instruction, and then we throw that query into a query bus.
Here's what that query might look like:

```php
<?php namespace Acme\Jobs;

class VacanciesListQuery {

    public $title;

    public $description;

    public function __construct($title, $description)
    {
        $this->title = $title;
        $this->description = $description;
    }

}
```

> When you call the `execute` method on the `QuerierTrait`, it will automatically map the data from `Input::all()` to your query. You won't need to worry about doing that manually.

So what exactly does the query bus do? Think of it as a simple utility that will translate this query into an associated handler class that will, well, handle the query! In this case, that means delegating as needed to post the new job listing.

By default, the query bus will do a quick search and replace on the name of the query class to figure out which handler class to resolve out of the IoC container. As such:

- VacanciesListQuery => VacanciesListQueryHandler
- VacanciesInArchiveQuery => VacanciesInArchiveQueryHandler

Make sense? Good. Keep in mind, though, that if you prefer a different naming convention, you can override the defaults. See below.

### Decorating the Query Bus

There may be times when you want to decorate the query bus to first perform some kind of action...maybe you need to first sanitize some data. Well, that's easy. First, create a class that implements the `SMGladkovskiy\Querier\QueryBus` contract...

```php
<?php namespace Acme\Jobs;

use SMGladkovskiy\Querier\QueryBus;

class JobSanitizer implements QueryBus {

    public function executeQuery($query)
    {
       // sanitize the job data
    }

}
```

...and now reference this class, when you execute the query in your controller.

```php
$this->executeQuery(VacanciesListQuery::class, null, [
    'JobSanitizer'
]);
```

And that's it! Now, you have a hook to sanitize the query/data before it's passed on to the handler class. On that note...

### The Handler Class

Let's create our first handler class now:

```php
<?php namespace Acme\Jobs;

use SMGladkovskiy\Querier\QueryHandler;
use SMGladkovskiy\Querier\Events\DispatchableTrait;

class VacanciesListQueryHandler implements QueryHandler {

    use DispatchableTrait;

    public function handle($query)
    {
        $job = Job::post($query->title, $query->description);

        $this->dispatchEventsFor($job);

        return $job;
    }

}
```

For this demo, our handler is fairly simple. In real-life, more would be going on here. Notice that `dispatchEventsFor` method? This will handle the process of firing all queued events for your entity. This way, other parts of your app may listen
for when a job has been published, and respond accordingly.

### Validation

This package also includes a validation trigger automatically. As an example, when you throw a query into the query bus, it will also determine whether an associated validator object exists. If it does,
it will call a `validate` method on this class. If it doesn't exist, it'll simply continue on. So, this gives you a nice hook to perform validation before executing the query and firing domain events.
The convention is:

- VacanciesListQuery => VacanciesListValidator

So, simply create that class, and include a `validate` method, which we'll receive the `VacanciesListQuery` object. Then, perform your validation however you normally do. I recommend that, for failed validation, you throw an exception - perhaps `ValidationFailedException`. This way, either within your controller - or even `global.php` - you can handle failed validation appropriately (probably by linking back to the form and notifying the user).

## Overriding Paths

By default, this package makes some assumptions about your file structure. As demonstrated above:

- Path/To/VacanciesListQuery => Path/To/VacanciesListQueryHandler
- Path/To/VacanciesListQuery => Path/To/VacanciesListValidator

Perhaps you had something different in mind. No problem! Just create your own query translator class that implements the `SMGladkovskiy\Querier\QueryTranslator` interface. This interface includes two methods:

- `toQueryHandler`
- `toValidator`

Maybe you want to place your validators within a `Validators/` directory. Okay:

```php
<?php namespace Acme\Core;

use SMGladkovskiy\Querier\QueryTranslator;

class MyQueryTranslator implements QueryTranslator {

    /**
     * Translate a query to its handler counterpart
     *
     * @param $query
     * @return mixed
     * @throws HandlerNotRegisteredException
     */
    public function toQueryHandler($query)
    {
        $handler = str_replace('Query', 'Handler', get_class($query));

        if ( ! class_exists($handler))
        {
            $message = "Query handler [$handler] does not exist.";

            throw new HandlerNotRegisteredException($message);
        }

        return $handler;
    }

    /**
     * Translate a query to its validator counterpart
     *
     * @param $query
     * @return mixed
     */
    public function toValidator($query)
    {
        $segments = explode('\\', get_class($query));

        array_splice($segments, -1, false, 'Validators');

        return str_replace('Query', 'Validator', implode('\\', $segments));
    }

}
```

Now, a `Path/To/MyGreatQuery` will look for a `Path/To/Validators/MyGreatValidator` class instead.

> It might be useful to copy and paste the `SMGladkovskiy\Querier\BasicQueryTranslator` class, and then modify as needed.

The only remaining step is to update the binding in the IoC container.

```php
// We want to use our own custom translator class
App::bind(
    'SMGladkovskiy\Querier\QueryTranslator',
    'Acme\Core\MyQueryTranslator'
);
```

Done!

## File Generation

You'll likely find yourself manually creating lots and lots of queries and handler classes. Instead, use the Artisan command that is included with this package!
Simply run:

```bash
php artisan querier:generate Acme/Bar/UsersQuery
```

This will generate both `UsersQuery` and a `UsersQueryHandler` classes. By default, it will look for that "Acme" directory within "app/". If your base domain directory is somewhere else, pass the `--base="src"`.

#### The Query

```php
<?php namespace Acme\Bar;

class UsersQuery {

    /**
     * Constructor
     */
    public function __construct()
    {
    }

}
```

#### The Handler

```php
<?php namespace Acme\Bar;

use SMGladkovskiy\Querier\QueryHandler;

class UsersQueryHandler implements QueryHandler {

    /**
     * Handle the command.
     *
     * @param object $command
     * @return void
     */
    public function handle($command)
    {

    }

}
```

Or, if you also want boilerplate for the properties, you can do that as well.

```bash
php artisan querier:generate Acme/Bar/UsersQuery --properties="first, last"
```

When you add the `--properties` flag, the handle class will remain the same, however, the command, itself, will be scaffolded, like so:

```php
<?php namespace Acme\Bar;

class UsersQuery {

    /**
     * @var string
     */
    public $first;

    /**
     * @var string
     */
    public $last;

    /**
     * Constructor
     *
     * @param string first
     * @param string last
     */
    public function __construct($first, $last)
    {
        $this->first = $first;
        $this->last = $last;
    }

}
```

Nifty, ay? That'll save you a lot of time, so remember to use it.

> When calling this command, use forward slashes for your class path: `Acme/Bar/MyQuery`. If you'd rather use backslashes, you'll need to wrap it in quotes.

## That Does It!