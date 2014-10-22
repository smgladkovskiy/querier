<?php namespace SMGladkovskiy\Querier;

use Illuminate\Foundation\Application;
use InvalidArgumentException;

class ValidationQueryBus implements QueryBus {

    /**
     * @var QueryBus
     */
    protected $bus;

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

    function __construct(QueryBus $bus, Application $app, QueryTranslator $queryTranslator)
    {
        $this->bus             = $bus;
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
     * Execute a command with validation.
     *
     * @param $query
     *
     * @return mixed
     */
    public function execute($query)
    {
        // If a validator is "registered," we will
        // first trigger it, before moving forward.
        $this->validateQuery($query);

        // Next, we'll execute any registered decorators.
        $this->executeDecorators($query);

        // And finally pass through to the handler class.
        return $this->bus->execute($query);
    }

    /**
     * If appropriate, validate query data.
     *
     * @param $query
     */
    protected function validateQuery($query)
    {
        $validator = $this->queryTranslator->toValidator($query);

        if(class_exists($validator))
        {
            $this->app->make($validator)->validate($query);
        }
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
                $message = 'The class to decorate must be an implementation of SMGladkovskiy\Querier\CommandBus';

                throw new InvalidArgumentException($message);
            }

            $instance->execute($query);
        }
    }
}
