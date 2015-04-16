<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Parts;

use \Symfony\Component\Form\FormBuilder;

trait SymfonyForms {

    abstract public function getView();

    /**
     * @return FormBuilder
     */
    public function createFormBuilder()
    {
        return $this->getView()->getFormFactory()->createBuilder();
    }

}