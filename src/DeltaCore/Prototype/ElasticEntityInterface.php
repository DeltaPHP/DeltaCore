<?php
/**
 * Created by PhpStorm.
 * User: orbisnull
 * Date: 03.06.2015
 * Time: 2:49
 */

namespace DeltaCore\Prototype;


interface ElasticEntityInterface
{
    public function getId();
    public function toElastic();
    public function getElasticOptions();

}