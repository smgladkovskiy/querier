<?php namespace SMGladkovskiy\Querier\Console;

class QueryInput {

    public $name;

    public $namespace;

    public $properties = [];

    public function __construct($name, $namespace, $properties)
    {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->properties = $properties;
    }

    public function arguments()
    {
        return implode(', ', array_map(function($property)
        {
            return '$' . $property;
        }, $this->properties));
    }

}