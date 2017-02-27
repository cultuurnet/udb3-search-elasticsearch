<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

class IndexNames
{
    const UDB3_CORE_READ_ALIAS = 'udb3_core_read';
    const UDB3_CORE_WRITE_ALIAS = 'udb3_core_write';
    const UDB3_CORE_LATEST = 'udb3_core_v1';
    const UDB3_CORE_PREVIOUS = 'udb3_core_20161011000440';

    const GEOSHAPES_READ_ALIAS = 'geoshapes_read';
    const GEOSHAPES_WRITE_ALIAS = 'geoshapes_write';
    const GEOSHAPES_LATEST = 'geoshapes_v1';
    const GEOSHAPES_PREVIOUS = null;
}
