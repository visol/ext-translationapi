<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Sinso.' . $_EXTKEY,
        'API',
        [
            'Xliff' => 'export',
        ],
        [
            'Xliff' => 'export',
        ]
    );
};

$boot($_EXTKEY);
unset($boot);
