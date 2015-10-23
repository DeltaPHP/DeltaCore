<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;


use Pimple\Container;

class DI extends Container
{
    public function lazyGet($id)
    {
        return function() use ($id) {
            return $this[$id];
        };
    }
}
