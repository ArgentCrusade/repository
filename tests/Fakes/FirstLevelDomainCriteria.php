<?php

namespace ArgentCrusade\Repository\Tests\Fakes;

use ArgentCrusade\Repository\Contracts\Criterias\CriteriaInterface;

class FirstLevelDomainCriteria implements CriteriaInterface
{
    protected $domain;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    public function apply($model)
    {
        return $model->where('name', 'like', '%'.$this->domain);
    }
}
