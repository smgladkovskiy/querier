<?php namespace SMGladkovskiy\Querier;

use Illuminate\Foundation\Application;
use InvalidArgumentException;

class DefaultQueryBus implements QueryBus {

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var QueryTranslator
     */
    protected $queryTranslator;

    /**
     * List of optional decorators for query bus.
     *
     * @var array
     */
    protected $decorators = [];

    /**
     * @param Application     $app
     * @param QueryTranslator $queryTranslator
     */
    function __construct(Application $app, QueryTranslator $queryTranslator)
    {
        $this->app             = $app;
        $this->queryTranslator = $queryTranslator;
    }

    /**
     * Decorate the query bus with any executable actions.
     *
     * @param  string $className
     *
     * @return mixed
     */
    public function decorate($className)
    {
        $this->decorators[] = $className;
    }

    /**
     * Execute the query
     *
     * @param $query
     *
     * @return mixed
     */
    public function execute($query)
    {
        $this->executeDecorators($query);

        $handler = $this->queryTranslator->toQueryHandler($query);

        return $this->app->make($handler)->handle($query);
    }

    /**
     * Execute all registered decorators
     *
     * @param  object $query
     *
     * @return null
     */
    protected function executeDecorators($query)
    {
        foreach($this->decorators as $className)
        {
            $instance = $this->app->make($className);

            if(!$instance instanceof QueryBus)
            {
                $message = 'The class to decorate must be an implementation of SMGladkovskiy\Querier\QueryBus';

                throw new InvalidArgumentException($message);
            }

            $instance->execute($query);
        }
    }
}