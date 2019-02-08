<?php

namespace ArgentCrusade\Repository\Tests\Fakes;

trait FakeTrait
{
    public $fakeTraitBooted = false;

    public function bootFakeTrait()
    {
        $this->fakeTraitBooted = true;
    }
}
