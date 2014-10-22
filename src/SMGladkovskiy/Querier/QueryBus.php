<?php namespace SMGladkovskiy\Querier;

interface QueryBus {

    /**
     * Execute a query
     *
     * @param $query
     *
     * @return mixed
     */
    public function execute($query);
}