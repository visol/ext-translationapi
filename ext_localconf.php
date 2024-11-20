<?php

use Sinso\Translationapi\Controller\XliffController;

defined('TYPO3') || die();

(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Translationapi',
        'API',
        [
            XliffController::class => 'export',
        ],
        [
            XliffController::class => 'export',
        ],
    );
})();
