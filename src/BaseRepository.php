<?php

namespace ArgentCrusade\Repository;

abstract class BaseRepository
{
    /** @var bool */
    private $booted = false;

    /**
     * Boot the repository & its traits.
     */
    public function boot()
    {
        $this->bootTraits();
    }

    /**
     * Boot the repository & its traits if not already booted.
     */
    public function bootIfNotBooted()
    {
        if (!$this->booted) {
            $this->boot();
        }

        $this->booted = true;
    }

    /**
     * Boot repository traits.
     */
    protected function bootTraits()
    {
        foreach (class_uses_recursive($this) as $trait) {
            if (method_exists($this, $method = 'boot'.class_basename($trait))) {
                call_user_func([$this, $method]);
            }
        }
    }
}
