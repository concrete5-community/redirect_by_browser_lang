<?php

/**
 * @var Concrete\Core\Attribute\View $view
 * @var Concrete\Package\RedirectByBrowserLang\Attribute\RedirectByBrowserLang\Controller $controller
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Package\RedirectByBrowserLang\Entity\RedirectSettings $settings
 */

?>
<fieldset class="ccm-attribute">
    <legend><?= t('Redirect Options') ?></legend>
    <div class="form-group">
        <?= $form->label('excludedQueryStringParams', t('Parameters to be excluded when forwarding the querystring')) ?>
        <?= $form->textarea('excludedQueryStringParams', implode("\n", $settings->getExcludedQueryStringParams())) ?>
        <div class="small text-muted">
            <?= t('Write every parameter in its own line')?>
        </div>
    </div>
</fieldset>
