<?php
namespace Concrete\Package\RedirectByBrowserLang\Attribute\RedirectByBrowserLang;

use Concrete\Core\Attribute\Controller as AttributeTypeController;
use Concrete\Core\Attribute\FontAwesomeIconFormatter;
use Concrete\Core\Attribute\Form\Control\View\GroupedView;
use Concrete\Core\Attribute\MulticolumnTextExportableAttributeInterface;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Form\Context\ContextInterface;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Page;
use Concrete\Core\Url\Resolver\PageUrlResolver;
use Concrete\Package\RedirectByBrowserLang\Entity\RedirectSettings;
use Concrete\Package\RedirectByBrowserLang\Entity\RedirectValue;
use SimpleXMLElement;
use Concrete\Core\Utility\Service\Xml;

defined('C5_EXECUTE') or die('Access denied.');

/**
 * @method \Concrete\Package\RedirectByBrowserLang\Entity\RedirectSettings getAttributeKeySettings()
 *
 */
class Controller extends AttributeTypeController implements MulticolumnTextExportableAttributeInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\AbstractController::$helpers
     */
    protected $helpers = [];

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::$searchIndexFieldDefinition
     */
    protected $searchIndexFieldDefinition = [
        'enabled' => [
            'type' => 'boolean',
            'options' => ['default' => '0', 'notnull' => false],
        ],
    ];

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::getIconFormatter()
     */
    public function getIconFormatter()
    {
        return new FontAwesomeIconFormatter('arrow-right');
    }

    /**
     * {@inheritDoc}
     *
     * @see \Concrete\Core\Attribute\Controller::getControlView()
     */
    public function getControlView(ContextInterface $context)
    {
        return new GroupedView($context, $this->getAttributeKey(), $this->getAttributeValue());
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::getAttributeKeySettingsClass()
     */
    public function getAttributeKeySettingsClass()
    {
        return RedirectSettings::class;
    }

    public function type_form()
    {
        $this->set('form', $this->app->make(Form::class));
        $this->set('settings', $this->getAttributeKeySettings());
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::validateKey()
     */
    public function validateKey($data = false)
    {
        $errors = $this->app->make('error');
        $excludedQueryStringParams = isset($data['excludedQueryStringParams']) ? $data['excludedQueryStringParams'] : [];
        if (is_string($data['excludedQueryStringParams'])) {
            $excludedQueryStringParams = preg_split('/[\r\n]+/', (string) $excludedQueryStringParams, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (!is_array($excludedQueryStringParams) || !in_array('cID', $excludedQueryStringParams, true)) {
            $errors->add(t('The list of query string parameters to be excluded must include at least %s.', 'cID'));
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::saveKey()
     */
    public function saveKey($data)
    {
        $settings = $this->getAttributeKeySettings();

        $excludedQueryStringParams = isset($data['excludedQueryStringParams']) ? $data['excludedQueryStringParams'] : [];
        if (is_string($data['excludedQueryStringParams'])) {
            $excludedQueryStringParams = preg_split('/[\r\n]/', (string) $excludedQueryStringParams, -1, PREG_SPLIT_NO_EMPTY);
        } elseif (!is_array($excludedQueryStringParams)) {
            $excludedQueryStringParams = [];
        }
        $settings->setExcludedQueryStringParams($excludedQueryStringParams);

        return $settings;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::importKey()
     */
    public function importKey(SimpleXMLElement $akey)
    {
        $settings = $this->getAttributeKeySettings();
        if (isset($akey->type)) {
            if (isset($akey->type->{'excluded-querystring-param'})) {
                $excludedQueryStringParams = [];
                foreach ($akey->type->{'excluded-querystring-param'} as $child) {
                    $value = (string) $child;
                    if ($value !== '' && !in_array($value, $excludedQueryStringParams, true)) {
                        $excludedQueryStringParams[] = $value;
                    }
                }
                $settings->setExcludedQueryStringParams($excludedQueryStringParams);
            }
        }

        return $settings;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::exportKey()
     *
     * @param \SimpleXMLElement $akey
     */
    public function exportKey($akey)
    {
        $settings = $this->getAttributeKeySettings();
        $xml = $this->app->make(Xml::class);
        $typeNode = $akey->addChild('type');
        foreach ($settings->getExcludedQueryStringParams() as $excludedQueryStringParam) {
            if (method_exists($xml, 'createChildElement')) {
                $xml->createChildElement($typeNode, 'excluded-querystring-param', $excludedQueryStringParam);
            } elseif (strpbrk($excludedQueryStringParam, "&<>") === false) {
                $typeNode->addChild('excluded-querystring-param', $excludedQueryStringParam);
            } else {
                $xml->createCDataNode($typeNode, 'excluded-querystring-param', $excludedQueryStringParam);
            }
        }

        return $akey;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::getAttributeValueClass()
     */
    public function getAttributeValueClass()
    {
        return RedirectValue::class;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::getAttributeValueObject()
     *
     * @return \Concrete\Package\RedirectByBrowserLang\Entity\RedirectValue|null
     */
    public function getAttributeValueObject()
    {
        return parent::getAttributeValueObject();
        $attributeValue = $this->getAttributeValue();
        if (!$attributeValue) {
            return null;
        }
        $genericValue = $attributeValue->getGenericValue();
        if (!$genericValue) {
            return null;
        }

        return $this->entityManager->find(RedirectValue::class, $genericValue);
    }

    public function form()
    {
        $value = $this->getAttributeValueObject();
        $this->set('form', $this->app->make(Form::class));
        $this->set('key', $this->attributeKey);
        $this->set('value', $value ?: new RedirectValue());
        $this->set('reportLink', $this->getReportLink());
    }

    /**
     * @param \Concrete\Core\Search\ItemList\Database\ItemList $list
     *
     * @return \Concrete\Core\Search\ItemList\Database\ItemList
     */
    public function searchForm($list)
    {
        $akHandle = $this->attributeKey->getAttributeKeyHandle();
        $query = $list->getQueryObject();
        if (filter_var($this->request('enabled'), FILTER_VALIDATE_BOOLEAN)) {
            $query->andWhere("ak_{$akHandle}_enabled = 1");
        } else {
            $query->andWhere("ak_{$akHandle}_enabled = 0 OR ak_{$akHandle}_enabled IS NULL");
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::createAttributeValueFromRequest()
     */
    public function createAttributeValueFromRequest()
    {
        return $this->createAttributeValue($this->post());
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::createAttributeValue()
     */
    public function createAttributeValue($data)
    {
        if ($data instanceof RedirectValue) {
            return clone $data;
        }
        $av = new RedirectValue();
        return $av
            ->setRedirectIfEditable(!empty($data['redirectIfEditable']))
            ->setRedirectUnmapped(!empty($data['redirectUnmapped']))
            ->setForwardQueryString(!empty($data['forwardQueryString']))
            ->setRedirectRequestsWithBody(!empty($data['redirectRequestsWithBody']))
            ->setRedirectIfUnaccessible(!empty($data['redirectIfUnaccessible']))
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::importValue()
     */
    public function importValue(SimpleXMLElement $akv)
    {
        if (!isset($akv->value)) {
            return null;
        }
        $av = new RedirectValue();
        return $av
            ->setRedirectIfEditable(filter_var($akv->value['redirectIfEditable'], FILTER_VALIDATE_BOOLEAN))
            ->setRedirectUnmapped(filter_var($akv->value['redirectUnmapped'], FILTER_VALIDATE_BOOLEAN))
            ->setForwardQueryString(filter_var($akv->value['forwardQueryString'], FILTER_VALIDATE_BOOLEAN))
            ->setRedirectRequestsWithBody(filter_var($akv->value['redirectRequestsWithBody'], FILTER_VALIDATE_BOOLEAN))
            ->setRedirectIfUnaccessible(filter_var($akv->value['redirectIfUnaccessible'], FILTER_VALIDATE_BOOLEAN))
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::exportValue()
     */
    public function exportValue(SimpleXMLElement $akn)
    {
        $value = $this->getAttributeValueObject();
        if ($value !== null) {
            $node = $akn->addChild('value');
            $node->addAttribute('redirectIfEditable', $value->isRedirectIfEditable() ? 'true' : 'false');
            $node->addAttribute('redirectUnmapped', $value->isRedirectUnmapped() ? 'true' : 'false');
            $node->addAttribute('forwardQueryString', $value->isForwardQueryString() ? 'true' : 'false');
            $node->addAttribute('redirectRequestsWithBody', $value->isRedirectRequestsWithBody() ? 'true' : 'false');
            $node->addAttribute('redirectIfUnaccessible', $value->isRedirectIfUnaccessible() ? 'true' : 'false');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\Controller::getSearchIndexValue()
     */
    public function getSearchIndexValue()
    {
        $value = $this->getAttributeValueObject();

        return [
            'enabled' => $value === null ? 0 : 1,
        ];
    }

    public function search()
    {
        $this->set('form', $this->app->make(Form::class));
        $rawEnabled = $this->request('enabled');
        $this->set('enabled', $rawEnabled === null || filter_var($rawEnabled, FILTER_VALIDATE_BOOLEAN));
        $v = $this->getView();
        $v->render();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\MulticolumnTextExportableAttributeInterface::getAttributeTextRepresentationHeaders()
     */
    public function getAttributeTextRepresentationHeaders()
    {
        return [
            'Redirect editors',
            'Redirect unmapped',
            'Forward queryString',
            'Redirect requests with body',
            'Redirect to unaccessible pages',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\MulticolumnTextExportableAttributeInterface::getAttributeValueTextRepresentation()
     */
    public function getAttributeValueTextRepresentation()
    {
        $value = $this->getAttributeValueObject();

        return [
            $value ? ($value->isRedirectIfEditable() ? '1' : '0') : '',
            $value ? ($value->isRedirectUnmapped() ? '1' : '0') : '',
            $value ? ($value->isForwardQueryString() ? '1' : '0') : '',
            $value ? ($value->isRedirectRequestsWithBody() ? '1' : '0') : '',
            $value ? ($value->isRedirectIfUnaccessible() ? '1' : '0') : '',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\MulticolumnTextExportableAttributeInterface::updateAttributeValueFromTextRepresentation()
     */
    public function updateAttributeValueFromTextRepresentation(array $textRepresentation, ErrorList $warnings)
    {
        $textRepresentation = array_map('trim', $textRepresentation);
        $value = $this->getAttributeValueObject();
        if ($value === null) {
            if (implode('', $textRepresentation) === '') {
                return null;
            }
            $value = new RedirectValue();
        }
        $value
            ->setRedirectIfEditable(filter_var(trim(array_shift($textRepresentation)), FILTER_VALIDATE_BOOLEAN))
            ->setRedirectUnmapped(filter_var(trim(array_shift($textRepresentation)), FILTER_VALIDATE_BOOLEAN))
            ->setForwardQueryString(filter_var(trim(array_shift($textRepresentation)), FILTER_VALIDATE_BOOLEAN))
            ->setRedirectRequestsWithBody(filter_var(trim(array_shift($textRepresentation)), FILTER_VALIDATE_BOOLEAN))
            ->setRedirectIfUnaccessible(filter_var(trim(array_shift($textRepresentation)), FILTER_VALIDATE_BOOLEAN))
        ;

        return $value;
    }

    /**
     * @return array|null
     */
    private function getReportLink()
    {
        $reportPage = Page::getByPath('/dashboard/system/multilingual/page_report');
        if (!$reportPage || $reportPage->isError()) {
            return null;
        }
        $name = $reportPage->getCollectionName();
        $url = (string) $this->app->make(PageUrlResolver::class)->resolve([$reportPage]);
        $currentPage = Page::getCurrentPage();
        if ($this->isReportLinkCurrentPage($currentPage)) {
            $currentSection = Section::getBySectionOfSite($currentPage);
            if ($currentSection && !$currentSection->isError()) {
                $params = [
                    'sectionID' => (string) $currentSection->getCollectionID(),
                    'keywords' => $currentPage->getCollectionName(),
                    'showAllPages' => '1',
                    'targets' => [],
                ];
                foreach (Section::getList($currentPage->getSite()) as $otherSection) {
                    if ($otherSection && !$otherSection->isError() && $otherSection->getCollectionID() != $currentSection->getCollectionID()) {
                        $otherSectionID = $otherSection->getCollectionID();
                        $params['targets'][$otherSectionID] = (string) $otherSectionID;
                    }
                }
                $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
            }
        }
        return [$name, $url];
    }

    /**
     * @param \Concrete\Core\Page\Page|false|null $page
     *
     * @return bool
     */
    private function isReportLinkCurrentPage($page)
    {
        return $page && !$page->isError() && !$page->isSystemPage() && !$page->isGeneratedCollection();
    }
}
