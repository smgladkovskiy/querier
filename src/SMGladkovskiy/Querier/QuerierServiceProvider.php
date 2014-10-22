<?php namespace SMGladkovskiy\Querier;

use Illuminate\Support\ServiceProvider;

class QuerierServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerQueryTranslator();

        $this->registerQueryBus();

        $this->registerArtisanCommand();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['querier'];
    }

    /**
     * Register the query translator binding
     */
    protected function registerQueryTranslator()
    {
        $this->app->bind('SMGladkovskiy\Querier\QueryTranslator', 'SMGladkovskiy\Querier\BasicQueryTranslator');
    }

    /**
     * Register the desired query bus implementation
     */
    protected function registerQueryBus()
    {
        $this->app->bindShared('SMGladkovskiy\Querier\QueryBus', function ($app)
        {
            $default    = $app->make('SMGladkovskiy\Querier\DefaultQueryBus');
            $translator = $app->make('SMGladkovskiy\Querier\QueryTranslator');

            return new ValidationQueryBus($default, $app, $translator);
        });
    }

    /**
     * Register the Artisan query
     *
     * @return void
     */
    public function registerArtisanCommand()
    {
        $this->app->bindShared('querier.query.make', function ($app)
        {
            return $app->make('SMGladkovskiy\Querier\Console\QuerierGenerateQuery');
        });

        $this->commands('querier.query.make');
    }
}
