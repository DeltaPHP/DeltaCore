<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;


interface AdminControllerInterface
{
    public function formAction();
    public function saveAction();
    public function rmAction();
}