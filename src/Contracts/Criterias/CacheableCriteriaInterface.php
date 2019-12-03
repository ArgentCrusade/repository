<?php

declare(strict_types=1);

namespace ArgentCrusade\Repository\Contracts\Criterias;

interface CacheableCriteriaInterface extends CriteriaInterface
{
    /**
     * Get cache hash for the current criteria.
     *
     * @return array
     */
    public function getCacheHash(): string;
}
