<?php namespace SMGladkovskiy\Querier;

interface QueryHandler {

    /**
     * Handle the query
     *
     * @param $query
     * @return mixed
     */
    public function handle($query);

}