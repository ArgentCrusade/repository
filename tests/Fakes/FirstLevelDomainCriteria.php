<?php

namespace ArgentCrusade\Repository\Tests\Fakes;

use ArgentCrusade\Repository\Contracts\Criterias\CacheableCriteriaInterface;

class FirstLevelDomainCriteria implements CacheableCriteriaInterface
{
    protected $domain;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Get cache hash for the current criteria.
     *
     * @return array
     */
    public function getCacheHash(): string
    {
        return 'name-'.$this->domain;
    }

    public function apply($model)
    {
        return $model->where('name', 'like', '%'.$this->domain);
    }
}
