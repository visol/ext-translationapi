<?php

use Sinso\Translationapi\Controller\XliffController;

defined('TYPO3_MODE') || die();

(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Sinso.translationapi',
        'API',
        [
            XliffController::class => 'export',
        ],
        [
            XliffController::class => 'export',
        ],
    );
})();
