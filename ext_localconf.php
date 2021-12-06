<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'FFREWER.frsupersized',
    'Pi1',
    ['Supersized' => 'index',],
    ['Supersized' => '',]
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1586260063] = [
    'nodeName' => 'SelectOrCheckbox',
    'priority' => '70',
    'class' => \FFREWER\Frsupersized\Form\Element\SelectOrCheckbox::class
];


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['supersizedFlexformFiles']
    = \FFREWER\Frsupersized\Updates\FlexformFilesUpdater::class;