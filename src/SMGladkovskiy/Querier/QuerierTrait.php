<?php namespace SMGladkovskiy\Querier;

use App;
use Input;
use InvalidArgumentException;
use ReflectionClass;

trait QuerierTrait {

    /**
     * Execute the query
     *
     * @param  string $query
     * @param  array  $input
     * @param  array  $decorators
     *
     * @return mixed
     */
    public function execute($query, array $input = null, $decorators = [])
    {
        $input = $input ?: Input::all();

        $query = $this->mapInputToQuery($query, $input);

        $bus = $this->getQueryBus();

        // If any decorators are passed, we'll
        // filter through and register them
        // with the QueryBus, so that they
        // are executed first.
        foreach($decorators as $decorator)
        {
            $bus->decorate($decorator);
        }

        return $bus->execute($query);
    }

    /**
     * Fetch the query bus
     *
     * @return mixed
     */
    public function getQueryBus()
    {
        return App::make('SMGladkovskiy\Querier\QueryBus');
    }

    /**
     * Map an array of input to a query's properties.
     * - Code courtesy of Taylor Otwell.
     *
     * @param  string $query
     * @param  array  $input
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    protected function mapInputToQuery($query, array $input)
    {
        $dependencies = [];

        $class = new ReflectionClass($query);

        foreach($class->getConstructor()->getParameters() as $parameter)
        {
            $name = $parameter->getName();

            if(array_key_exists($name, $input))
            {
                $dependencies[] = $input[ $name ];
            }
            elseif($parameter->isDefaultValueAvailable())
            {
                $dependencies[] = $parameter->getDefaultValue();
            }
            else
            {
                throw new InvalidArgumentException("Unable to map input to query: {$name}");
            }
        }

        return $class->newInstanceArgs($dependencies);
    }
}