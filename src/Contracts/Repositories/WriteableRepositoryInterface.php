<?php

namespace ArgentCrusade\Repository\Contracts\Repositories;

interface WriteableRepositoryInterface
{
    /**
     * Create new record with the given attributes.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes = []);

    /**
     * Update record with the given ID with the given attributes.
     *
     * @param array            $attributes
     * @param int|string|mixed $id
     *
     * @return mixed
     */
    public function update(array $attributes, $id);

    /**
     * Delete record by the given ID.
     *
     * @param int|string|mixed $id
     *
     * @return bool
     */
    public function delete($id);
}
