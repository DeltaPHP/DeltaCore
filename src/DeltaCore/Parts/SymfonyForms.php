<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Parts;

trait SymfonyForms {

    /**
     * @return \DeltaCore\View\TwigView
     */
    abstract public function getView();

    public function createSfFormBuilder($name = null, array $options = array(), $data = null)
    {
        $type = 'form';
        if ($name) {
            return $this->getView()->getFormFactory()->createNamedBuilder($name, $type, $data, $options);
        } else {
            return $this->getView()->getFormFactory()->createBuilder($type, $data, $options);
        }
    }

    public function entityToSfSelect($array, $id = "id", $name = "name")
    {
        $id = ucfirst($id);
        $name = ucfirst($name);
        $items = [];
        foreach ($array as $item) {
            $items[$item->{"get" . $id}()] = $item->{"get" . $name}();
        }
        uksort($items, function ($a, $b) {
            $a = (integer)$a;
            $b = (integer)$b;
            return $a === $b ? 0 : $a < $b ? -1 : 1;
        });
        return $items;
    }
}
