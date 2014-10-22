<?php namespace SMGladkovskiy\Querier;

interface QueryBus {

    /**
     * Execute a command
     *
     * @param $query
     * @return mixed
     */
    public function execute($query);

}