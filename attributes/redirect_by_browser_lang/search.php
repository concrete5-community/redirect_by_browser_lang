<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Attribute\View $view
 * @var Concrete\Package\RedirectByBrowserLang\Attribute\RedirectByBrowserLang\Controller $controller
 * @var Concrete\Core\Form\Service\Form $form
 * @var bool $enabled
 */

?>
<div class="form-group">
    <?= $form->select(
        $view->field('enabled'),
        [
            '0' => t('Show pages without the %s attribute', tc('AttributeKeyName', 'Redirecy by Browser Language')),
            '1' => t('Show pages with the %s attribute', tc('AttributeKeyName', 'Redirecy by Browser Language')),
        ],
        $enabled ? '1' : '0'
    ) ?>
</div>
