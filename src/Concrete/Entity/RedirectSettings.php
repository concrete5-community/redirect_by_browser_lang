<?php

namespace Concrete\Package\RedirectByBrowserLang\Entity;

use Concrete\Core\Entity\Attribute\Key\Settings\Settings;

defined('C5_EXECUTE') or die('Access denied.');

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="atRedirectByBrowserLangSettings")
 */
class RedirectSettings extends Settings
{
    /**
     * List of excluded querystring parameters.
     *
     * @Doctrine\ORM\Mapping\Column(type="json_array", nullable=false)
     *
     * @var string[]
     */
    protected $excludedQueryStringParams;

    public function __construct()
    {
        $this->excludedQueryStringParams = [];
    }

    /**
     * @return string[]
     */
    public function getExcludedQueryStringParams()
    {
        return $this->excludedQueryStringParams;
    }

    /**
     * @param string[] $value
     *
     * @return $this
     */
    public function setExcludedQueryStringParams(array $value)
    {
        $this->excludedQueryStringParams = $value;

        return $this;
    }
}
