<?php

namespace cl\util\traits;

use cl\core\CLDependency;

trait HttpClientIocHelper
{
    /**
     * @var \cl\contract\CLHttpClient
     */
    private $httpclient;

    /**
     * @param $httpclient
     */
    public function setHttpclient(\cl\contract\CLHttpClient $httpclient): void
    {
        $this->httpclient = $httpclient;
    }

    /**
     * @return \cl\contract\CLHttpClient
     */
    public function getHttpclient(): \cl\contract\CLHttpClient
    {
        return $this->httpclient;
    }

    /**
     * @return array with required dependencies
     */
    public function dependsOn(): array
    {
        return [CLDependency::new('httpclient')];
    }
}
