<?php

namespace DeltaCore;

interface InterfaceView
{
    const DEFAULT_TEMPLATE = 'default';

    public function setTemplate($name);

    public function setArrayTemplates($template);

    public function assign($name, $value);

    public function assignArray(array $array);

    public function render($template = null, $params = []);

}