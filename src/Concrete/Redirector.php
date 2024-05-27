<?php
namespace Concrete\Package\RedirectByBrowserLang;

use Concrete\Core\Http\Request;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Session\SessionValidator;
use Concrete\Core\Url\Resolver\PageUrlResolver;
use Concrete\Core\User\User;
use Concrete\Package\RedirectByBrowserLang\Controller as MyPackageController;
use Concrete\Package\RedirectByBrowserLang\Entity\RedirectSettings;
use Concrete\Package\RedirectByBrowserLang\Entity\RedirectValue;
use Symfony\Component\HttpFoundation\Response;

defined('C5_EXECUTE') or die('Access denied.');

class Redirector
{
    /**
     * @var \Concrete\Core\User\User
     */
    private $user;

    /**
     * @var \Concrete\Core\Http\Request
     */
    private $request;

    /**
     * @var \Concrete\Core\Url\Resolver\PageUrlResolver
     */
    private $urlResolver;

    /**
     * @var \Concrete\Core\Http\ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var \Concrete\Core\Session\SessionValidator
     */
    private $sessionValidator;

    public function __construct(
        User $user,
        Request $request,
        PageUrlResolver $urlResolver,
        ResponseFactoryInterface $responseFactory,
        SessionValidator $sessionValidator
    )
    {
        $this->user = $user;
        $this->request = $request;
        $this->urlResolver = $urlResolver;
        $this->responseFactory = $responseFactory;
        $this->sessionValidator = $sessionValidator;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function process(Page $page, RedirectSettings $settings, RedirectValue $options)
    {
        if (!$this->checkIfCanBeRedirected($page, $options)) {
            return null;
        }
        $pageSection = $this->getSectionByPage($page);
        if ($pageSection === null) {
            return null;
        }
        $isRequestingHomePage = $page->getCollectionID() == $pageSection->getCollectionID();
        foreach ($this->getSectionsByBrowserLanguage() as $sectionByBrowserLanguage) {
            if ($sectionByBrowserLanguage->getCollectionID() == $pageSection->getCollectionID()) {
                return null;
            }
            $pageIDByBrowserLanguage = $sectionByBrowserLanguage->getTranslatedPageID($page);
            if (!$pageIDByBrowserLanguage) {
                continue;
            }
            if (!$isRequestingHomePage) {
                if ($pageIDByBrowserLanguage == $sectionByBrowserLanguage->getCollectionID() && !$options->isRedirectUnmapped()) {
                    continue;
                }
            }
            $pageByBrowserLanguage = Page::getByID($pageIDByBrowserLanguage);
            if (!$pageByBrowserLanguage || $pageByBrowserLanguage->isError()) {
                continue;
            }
            if (!$options->isRedirectIfUnaccessible()) {
                $checker = new Checker($pageByBrowserLanguage);
                if (!$checker->canViewPage()) {
                    continue;
                }
            }
            $url = $this->buildUrl($pageByBrowserLanguage, $settings, $options);
            $response = $this->responseFactory->redirect($url, Response::HTTP_FOUND);
            $response->prepare($this->request);

            return $response;
        }

        return null;
    }

    /**
     * @return bool
     */
    private function checkIfCanBeRedirected(Page $page, RedirectValue $options)
    {
        switch ($options->getRedirectByBrowsingState()) {
            case RedirectValue::BROWSINGSTATE_FIRST_WEBSITEPAGE:
                $session = $this->sessionValidator->getActiveSession(false);
                if ($session && $session->get(MyPackageController::SESSIONKEY_FIRSTPAGEDISPLAYED)) {
                    return false;
                }
                break;
            case RedirectValue::BROWSINGSTATE_ONCE_PER_PAGE:
                $session = $this->sessionValidator->getActiveSession(true);
                $already = $session->get(MyPackageController::SESSIONKEY_REDIRECTEDPAGES, []);
                $cID = (int) $page->getCollectionID();
                if (in_array($cID, $already, true)) {
                    return false;
                }
                $already[] = $cID;
                $session->set(MyPackageController::SESSIONKEY_REDIRECTEDPAGES, $already);
                break;
        }
        if ($this->user->isRegistered()) {
            $checker = new Checker($page);
            if ($checker->canEditPageContents()) {
                return false;
            }
        }
        if (!$options->isRedirectRequestsWithBody()) {
            switch ($this->request->getMethod()) {
                case 'GET':
                case 'HEAD':
                case 'TRACE':
                    break;
                default:
                    return false;
            }
            if ($this->request->request->all() !== []) {
                return false;
            }
            if ($this->request->files->all() !== []) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return \Concrete\Core\Multilingual\Page\Section\Section|null
     */
    private function getSectionByPage(Page $page)
    {
        $section = Section::getBySectionOfSite($page);

        return !$section || $section->isError() ? null : $section;
    }

    /**
     * @return \Generator<\Concrete\Core\Multilingual\Page\Section\Section>
     */
    private function getSectionsByBrowserLanguage()
    {
        foreach ($this->request->getLanguages() as $language) {
            $section = Section::getByLocaleOrLanguage($language);
            if ($section && !$section->isError()) {
                yield $section;
            }
        }
    }

    /**
     * @return string
     */
    private function buildUrl(Page $page, RedirectSettings $settings, RedirectValue $options)
    {
        $url = (string) $this->urlResolver->resolve([$page]);
        if ($options->isForwardQueryString()) {
            $params = [];
            $excludedParams = $settings->getExcludedQueryStringParams();
            foreach ($this->request->query->all() as $key => $value) {
                if (!in_array($key, $excludedParams, true)) {
                    $params[$key] = $value;
                }
            }
            if ($params !== []) {
                $url = rtrim($url, '&?');
                $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
            }
        }

        return $url;
    }
}
