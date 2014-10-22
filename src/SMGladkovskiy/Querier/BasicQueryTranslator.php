<?php namespace SMGladkovskiy\Querier;

class BasicQueryTranslator implements QueryTranslator {

    /**
     * Translate a query to its handler counterpart
     *
     * @param $query
     *
     * @return mixed
     * @throws HandlerNotRegisteredException
     */
    public function toQueryHandler($query)
    {
        $queryClass = get_class($query);
        $handler    = substr_replace($queryClass, 'QueryHandler', strrpos($queryClass, 'Query'));

        if(!class_exists($handler))
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
     *
     * @return mixed
     */
    public function toValidator($query)
    {
        $queryClass = get_class($query);

        return substr_replace($queryClass, 'Validator', strrpos($queryClass, 'Query'));
    }
}