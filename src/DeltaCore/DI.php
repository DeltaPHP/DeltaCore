<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;


class DI extends \Pimple
{
    public function lazyGet($id)
    {
        return function() use ($id) {
            return $this[$id];
        };
    }
}
