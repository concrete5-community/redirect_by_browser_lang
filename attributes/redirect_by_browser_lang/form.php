<?php

use Concrete\Package\RedirectByBrowserLang\Entity\RedirectValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Attribute\View $view
 * @var Concrete\Package\RedirectByBrowserLang\Attribute\RedirectByBrowserLang\Controller $controller
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Entity\Attribute\Key\PageKey $key
 * @var Concrete\Package\RedirectByBrowserLang\Entity\RedirectValue $value
 * @var array|null $reportLink
 */

?>
<div>

    <div class="checkbox">
        <label>
            <?= $form->checkbox($view->field('redirectIfEditable'), '1', $value->isRedirectIfEditable()) ?>
            <?= t('Redirect even logged-in users that can edit the page?') ?>
        </label>
    </div>

    <div class="checkbox">
        <label>
            <?= $form->checkbox($view->field('redirectUnmapped'), '1', $value->isRedirectUnmapped()) ?>
            <?= t('Redirect to locale home pages if the current page is not mapped?') ?>
        </label>
    </div>

    <div class="checkbox">
        <label>
            <?= $form->checkbox($view->field('forwardQueryString'), '1', $value->isForwardQueryString()) ?>
            <?= t('Include querystring parameters in the redirection?') ?>
        </label>
    </div>

    <div class="checkbox">
        <label>
            <?= $form->checkbox($view->field('redirectRequestsWithBody'), '1', $value->isRedirectRequestsWithBody()) ?>
            <?= t('Redirect requests with body (for example, POST requests)?') ?>
        </label>
    </div>

    <div class="checkbox">
        <label>
            <?= $form->checkbox($view->field('redirectIfUnaccessible'), '1', $value->isRedirectIfUnaccessible()) ?>
            <?= t('Redirect even if the destination page is not accessible by the site visitor?') ?>
        </label>
    </div>

    <div class="checkbox">
        <?= $form->label($view->field('redirectByBrowsingState'), t('Redirect by browsing state')) ?>
        <?= $form->select(
            $view->field('redirectByBrowsingState'),
            [
                RedirectValue::BROWSINGSTATE_ANY => t("it doesn't matter"),
                RedirectValue::BROWSINGSTATE_FIRST_WEBSITEPAGE => t('redirect only if this is the first page visited on the website'),
                RedirectValue::BROWSINGSTATE_ONCE_PER_PAGE => t('redirect this page only the first time'),
            ],
            $value->getRedirectByBrowsingState()
        ) ?>
    </div>

    <?php
    if ($reportLink !== null) {
        ?>
        <div class="small text-muted">
            <?= t(
                'You can configure the redirected pages in the %s dashboard page',
                sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    h($reportLink[1]),
                    h($reportLink[0])
                )
            ) ?>
        </div>
        <?php
    }
    ?>

</div>
