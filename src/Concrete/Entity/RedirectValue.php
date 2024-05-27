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
    const BROWSINGSTATE_ANY = 0;

    const BROWSINGSTATE_FIRST_WEBSITEPAGE = 1;

    const BROWSINGSTATE_ONCE_PER_PAGE = 2;

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

    /**
     * Should we redirect requests only if the page is the first visited one?
     *
     * @Doctrine\ORM\Mapping\Column(type="integer", nullable=false, options={"default": 0, "unsigned": true})
     *
     * @var int
     */
    protected $redirectByBrowsingState;

    public function __construct()
    {
        $this->redirectIfEditable = false;
        $this->redirectUnmapped = false;
        $this->forwardQueryString = false;
        $this->redirectRequestsWithBody = false;
        $this->redirectIfUnaccessible = false;
        $this->redirectByBrowsingState = self::BROWSINGSTATE_ANY;
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

    /**
     * @return int
     */
    public function getRedirectByBrowsingState()
    {
        return $this->redirectByBrowsingState;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setRedirectByBrowsingState($value)
    {
        $this->redirectByBrowsingState = (int) $value;

        return $this;
    }

    #[\ReturnTypeWillChange]
    public function __toString()
    {
        return t('Redirect visitors accordingly to the language of the browser.');
    }
}
