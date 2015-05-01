<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype\Parts;

use DeltaUtils\StringUtils;

trait SeoFields
{
    protected $metaTitle;
    protected $metaDescription;
    protected $keywords;

    /**
     * @return mixed
     */
    public function getMetaDescription()
    {
        if (empty($this->metaDescription)) {
            $desc = $this->getDescription();
            if (!empty($desc)) {
                $this->metaDescription = StringUtils::cutStr($desc, 160);
            } else {
                $this->metaDescription = "Подробная информация  о {$this->getMetaTitle()}";
            }
        }
        return $this->metaDescription;
    }

    /**
     * @param mixed $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return mixed
     */
    public function getMetaTitle()
    {
        if (empty($this->metaTitle)) {
            $this->metaTitle = $this->getName();
        }
        return $this->metaTitle;
    }

    /**
     * @param mixed $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * @return mixed
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param mixed $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }
}
