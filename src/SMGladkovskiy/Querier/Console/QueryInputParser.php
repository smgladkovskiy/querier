<?php namespace SMGladkovskiy\Querier\Console;

class QueryInputParser {

    public function parse($path, $properties)
    {
        $segments  = explode('\\', str_replace('/', '\\', $path));
        $name      = array_pop($segments);
        $namespace = implode('\\', $segments);

        $properties = $this->parseProperties($properties);

        return new QueryInput($name, $namespace, $properties);
    }

    private function parseProperties($properties)
    {
        return preg_split('/ ?, ?/', $properties, null, PREG_SPLIT_NO_EMPTY);
    }
}
