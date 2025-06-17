<?php
namespace Concrete\Package\RedirectByBrowserLang;

use Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface;
use Concrete\Core\Database\EntityManager\Provider\StandardPackageProvider;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Event;
use Concrete\Core\Page\Page;
use Concrete\Package\RedirectByBrowserLang\Entity\RedirectValue;

defined('C5_EXECUTE') or die('Access denied.');

class Controller extends Package implements ProviderAggregateInterface
{
    const SESSIONKEY_FIRSTPAGEDISPLAYED = 'ccm-redirect_by_browser_lang-firstpagedisplayed';
    const SESSIONKEY_REDIRECTEDPAGES = 'ccm-redirect_by_browser_lang-redirectedPages';

    /**
     * The package handle.
     *
     * @var string
     */
    protected $pkgHandle = 'redirect_by_browser_lang';

    /**
     * The package version.
     *
     * @var string
     */
    protected $pkgVersion = '1.1.3';

    /**
     * The minimum concrete5/ConcreteCMS version.
     *
     * @var string
     */
    protected $appVersionRequired = '8.5.10';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageName()
     */
    public function getPackageName()
    {
        return t('Redirect by browser language');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageDescription()
     */
    public function getPackageDescription()
    {
        return t('Redirect visitors accordingly to the language of the browser.');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::install()
     */
    public function install()
    {
        parent::install();
        $this->installContentFile('config/install.xml');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::upgrade()
     */
    public function upgrade()
    {
        parent::upgrade();
        $this->installContentFile('config/install.xml');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface::getEntityManagerProvider()
     */
    public function getEntityManagerProvider()
    {
        return new StandardPackageProvider($this->app, $this, [
            'src/Concrete/Entity' => 'Concrete\\Package\\RedirectByBrowserLang\\Entity',
        ]);
    }

    public function on_start()
    {
        $director = $this->app->make('director');
        $director->addListener('on_page_view', function(Event $event) {
            $page = $event->getPageObject();
            if ($page instanceof Page) {
                $response = $this->handlePage($page);
                if ($response !== null) {
                    $response->send();
                    exit();
                }
            }
        });
        $director->addListener('on_page_output', function() {
            $session = $this->app->make('session');
            if (!$session->get(self::SESSIONKEY_FIRSTPAGEDISPLAYED)) {
                $session->set(self::SESSIONKEY_FIRSTPAGEDISPLAYED, true);
            }
        });
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    private function handlePage(Page $page)
    {
        if ($page->isError() || $page->isSystemPage() || $page->isGeneratedCollection() || $page->isEditMode()) {
            return null;
        }
        $pageVersion = $page->getVersionObject();
        if (!$pageVersion || $pageVersion->isError()) {
            return null;
        }
        $attribute = $pageVersion->getAttributeValue('redirect_by_browser_lang');
        if (!$attribute) {
            return null;
        }
        $value = $attribute->getValueObject();
        if (!$value instanceof RedirectValue) {
            return null;
        }
        $settings = $attribute->getController()->getAttributeKeySettings();

        return $this->app->make(Redirector::class)->process($page, $settings, $value);
    }
}
