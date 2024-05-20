<?php

namespace Concrete\Package\RedirectByBrowserLang\Entity;

use Concrete\Core\Entity\Attribute\Value\Value\AbstractValue;

defined('C5_EXECUTE') or die('Access denied.');

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="atRedirectByBrowserLang")
 */
class RedirectValue extends AbstractValue
{
    /**
     * Should we redirect even if the current user can edit the page?
     *
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $redirectIfEditable;

    /**
     * Redirect to locale roots if the current page is not mapped?
     *
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $redirectUnmapped;

    /**
     * Include querystring parameters in the redirection?
     *
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $forwardQueryString;

    /**
     * Should we redirect requests that (may) contain body (for example: POST requests)?
     *
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $redirectRequestsWithBody;

    /**
     * Should we redirect requests to pages thay are not accessiblethat (may) contain body (for example: POST requests)?
     *
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $redirectIfUnaccessible;

    public function __construct()
    {
        $this->redirectIfEditable = false;
        $this->redirectUnmapped = false;
        $this->forwardQueryString = false;
        $this->redirectRequestsWithBody = false;
        $this->redirectIfUnaccessible = false;
    }

    /**
     * @return bool
     */
    public function isRedirectIfEditable()
    {
        return $this->redirectIfEditable;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setRedirectIfEditable($value)
    {
        $this->redirectIfEditable = (bool) $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRedirectUnmapped()
    {
        return $this->redirectUnmapped;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setRedirectUnmapped($value)
    {
        $this->redirectUnmapped = (bool) $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isForwardQueryString()
    {
        return $this->forwardQueryString;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setForwardQueryString($value)
    {
        $this->forwardQueryString = (bool) $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRedirectRequestsWithBody()
    {
        return $this->redirectRequestsWithBody;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setRedirectRequestsWithBody($value)
    {
        $this->redirectRequestsWithBody = (bool) $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRedirectIfUnaccessible()
    {
        return $this->redirectIfUnaccessible;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setRedirectIfUnaccessible($value)
    {
        $this->redirectIfUnaccessible = (bool) $value;

        return $this;
    }

    #[\ReturnTypeWillChange]
    public function __toString()
    {
        return t('Redirect visitors accordingly to the language of the browser.');
    }
}
