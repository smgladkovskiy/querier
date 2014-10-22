<?php namespace SMGladkovskiy\Querier;

interface QueryTranslator {

    /**
     * Translate a query to its handler counterpart
     *
     * @param $query
     *
     * @return mixed
     * @throws Exception
     */
    public function toQueryHandler($query);

    /**
     * Translate a command to its validator counterpart
     *
     * @param $query
     *
     * @return mixed
     */
    public function toValidator($query);
}