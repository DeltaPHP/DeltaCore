<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype;


interface SeoFieldsInterface {
    /**
     * @return mixed
     */
    public function getMetaDescription();

    /**
     * @param mixed $metaDescription
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return mixed
     */
    public function getMetaTitle();

    /**
     * @param mixed $metaTitle
     */
    public function setMetaTitle($metaTitle);

    /**
     * @return mixed
     */
    public function getKeywords();

    /**
     * @param mixed $keywords
     */
    public function setKeywords($keywords);
}
