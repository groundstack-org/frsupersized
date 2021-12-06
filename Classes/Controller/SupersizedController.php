<?php
namespace FFREWER\Frsupersized\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2012-2015 Frank Frewer <info@frankfrewer.de>, medienbüro // FRANK FREWER
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Controller for the Supersized object
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

class SupersizedController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var string
	 */
	private $extKey = 'frsupersized';

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * Initializes the current action
	 *
	 * @return void
	 */
	protected function initializeAction() {
		// resolve relative extension path in $this->settings from 'EXT:'
		// array_walk_recursive($this->settings, array($this, 'resolveRelativeExtPath'));
	}

	/**
	 * Action that displays a supersized background (slideshow & banner optional)
	 *
	 * @return void
	 */
 	public function indexAction() {
		// check and rebuild the settings array
		$this->rebuildSettings();
		// create a Fluid instance for header data rendering
		// $renderer = $this->getRenderer('Headerdata');
		// assign the data to it
		// $renderer->assign('settings', $this->settings);
		// and do the rendering
		// $headerData = $renderer->render();
		// add the header data
		// $this->response->addAdditionalHeaderData($headerData);
		$supersized = $this->settings;
		// assign it to the standard fluid template
		$this->view->assign('supersized', $supersized);

		$data = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
		$this->view->assign('data', $data->getContentObject()->data);
	}

	/**
	 * Function that checks and rebuilds the settings array
	 *
	 * @return void
	 */
	public function rebuildSettings() {
		//make sure that the settings have the required format
		// section fullbackground
		$this->settings['fullbackground']['minWidth'] = intval($this->settings['fullbackground']['minWidth']);
		$this->settings['fullbackground']['minHeight'] = intval($this->settings['fullbackground']['minHeight']);
		$this->settings['fullbackground']['verticalCenter'] = intval($this->settings['fullbackground']['verticalCenter']) > 0 ? 1 : 0;
		$this->settings['fullbackground']['horizontalCenter'] = intval($this->settings['fullbackground']['horizontalCenter']) > 0 ? 1 : 0;
		$this->settings['fullbackground']['fitAlways'] = intval($this->settings['fullbackground']['fitAlways']) > 0 ? 1 : 0;
		$this->settings['fullbackground']['fitPortrait'] = intval($this->settings['fullbackground']['fitPortrait']) > 0 ? 1 : 0;
		$this->settings['fullbackground']['fitLandscape'] = intval($this->settings['fullbackground']['fitLandscape']) > 0 ? 1 : 0;
		// section slideshow
		$this->settings['slideshow']['slideshow'] = intval($this->settings['slideshow']['slideshow']) > 0 ? 1 : 0;
		$this->settings['slideshow']['autoplay'] = intval($this->settings['slideshow']['autoplay']) > 0 ? 1 : 0;
		$this->settings['slideshow']['startSlide'] = intval($this->settings['slideshow']['startSlide']) > 0 ? intval($this->settings['slideshow']['startSlide']) : 1;
		$this->settings['slideshow']['stopLoop'] = intval($this->settings['slideshow']['stopLoop']) > 0 ? 1 : 0;
		$this->settings['slideshow']['random'] = intval($this->settings['slideshow']['random']) > 0 ? 1 : 0;
		$this->settings['slideshow']['transitionSpeed'] = intval($this->settings['slideshow']['transitionSpeed']);
		$this->settings['slideshow']['slideInterval'] = intval($this->settings['slideshow']['slideInterval']);
		$this->settings['slideshow']['newWindow'] = intval($this->settings['slideshow']['newWindow']) > 0 ? 1 : 0;
		$this->settings['slideshow']['keyboardNav'] = intval($this->settings['slideshow']['keyboardNav']) > 0 ? 1 : 0;
		$this->settings['slideshow']['performance'] = intval($this->settings['slideshow']['performance']) > 0 ? intval($this->settings['slideshow']['performance']) : 0;
		$this->settings['slideshow']['imageProtect'] = intval($this->settings['slideshow']['imageProtect']) > 0 ? 1 : 0;
		$this->settings['slideshow']['pauseHover'] = intval($this->settings['slideshow']['pauseHover']) > 0 ? 1 : 0;
		$this->settings['slideshow']['thumbLinks'] = intval($this->settings['slideshow']['thumbLinks']) > 0 ? 1 : 0;
		$this->settings['slideshow']['thumbnailNavigation'] = intval($this->settings['slideshow']['thumbnailNavigation']) > 0 ? 1 : 0;
		$this->settings['slideshow']['navigation'] = intval($this->settings['slideshow']['navigation']) > 0 ? 1 : 0;
		$this->settings['slideshow']['controlBar'] = intval($this->settings['slideshow']['controlBar']) > 0 ? 1 : 0;
		$this->settings['slideshow']['slideCounter'] = intval($this->settings['slideshow']['slideCounter']) > 0 ? 1 : 0;
		$this->settings['slideshow']['slideCaptions'] = intval($this->settings['slideshow']['slideCaptions']) > 0 ? 1 : 0;
		$this->settings['slideshow']['themeShutterProgressBar'] = intval($this->settings['slideshow']['themeShutterProgressBar']) > 0 ? 1 : 0;
		$this->settings['slideshow']['themeShutterMouseScrub'] = intval($this->settings['slideshow']['themeShutterMouseScrub']) > 0 ? 1 : 0;
		// section banner
		$this->settings['banner']['banner'] = intval($this->settings['banner']['banner']) > 0 ? 1 : 0;
		// get all slides (the file names)
		// Allow newlines as delimiter as well
		$this->settings['general']['slidesArray'] = GeneralUtility::trimExplode(
			',',
			str_replace(["\n", "\r"], ',', $this->settings['general']['slides']),
			true
		);
		// check if slideshow or not
		if ($this->settings['slideshow']['slideshow'] == 1) {
			$slideCaptions = explode( chr(10), $this->settings['general']['slideCaptions'] );
			$slideUrl = explode( chr(10), $this->settings['general']['slideUrl'] );
			$this->getThumbnails();
			$this->getImageDimensions();
			foreach ($this->settings['general']['slidesArray'] as $key => $value) {
				// complete the values for javascript array 'slides'
				$this->settings['general']['slideValues'][$key]['image'] = trim($value);
				$this->settings['general']['slideValues'][$key]['title'] = trim($slideCaptions[$key]);
				$this->settings['general']['slideValues'][$key]['url'] = trim($slideUrl[$key]);
				$this->settings['general']['slideValues'][$key]['comma'] = ($key < count($this->settings['general']['slidesArray'])-1 ? ',' : '');
			}
		} else {
			// no slideshow: get only the first slide even if there are more images
			if (is_array($this->settings['general']['slidesArray'])) {
				$firstSlide = trim($this->settings['general']['slidesArray'][0]);
			}
			// get the values for javascript array 'slides'
			$this->settings['general']['slideValues'][0] = array('image' => $firstSlide);
		}
        //
        // Get FAL files
        if(isset($this->settings['general']['images']) && (int)$this->settings['general']['images'] > 0) {
            $content = $this->configurationManager->getContentObject()->data;
            $fileRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\FileRepository::class);
            $this->settings['general']['images'] = $fileRepository->findByRelation(
                'tt_content',
                'supersized_image',
                $content['uid']
            );
        }
	}

	/**
	 * This function creates  - if not exist - thumbnails from included images and put file names into an array in $this->settings
	 *
	 * @return void
	 */
	protected function getThumbnails() {
		// instanciate the thumbCreator
		$thumbCreator = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Imaging\\GifBuilder');
		$thumbCreator->absPrefix = Environment::getPublicPath() . '/';
		// width 150 & height 108 are the default values of the Supersized plugin
		$widthParam = strval( intval($this->settings['slideshow']['thumbWidth']) > 0 ? intval($this->settings['slideshow']['thumbWidth']) : 150 ) . ( intval($this->settings['slideshow']['thumbWidthCrop']) > 0 ? 'c' : '');
		$heightParam = strval( intval($this->settings['slideshow']['thumbHeight']) > 0 ? intval($this->settings['slideshow']['thumbHeight']) : 108 ) . ( intval($this->settings['slideshow']['thumbHeightCrop']) > 0 ? 'c' : '');
		foreach ($this->settings['general']['slidesArray'] as $key => $value) {
			$value = trim($value);
			// create cropped thumbnail - it will only be created if it doesn't exists
			$tempFileInfo[$key] = $thumbCreator->imageMagickConvert('uploads/tx_frsupersized/' . $value, '', $widthParam, $heightParam, $imParams, '', $options, TRUE);
			// strip PATH_site from file path - get relative url
			$tempFileInfo[$key]['thumb'] = str_replace(Environment::getPublicPath() . '/', "", $tempFileInfo[$key][3]);
			// get the values for javascript array 'slides'
			$this->settings['general']['slideValues'][$key] = array(
				'thumb' => $tempFileInfo[$key]['thumb'],
			);
		}
	}

	/**
	 * This function put the image dimensions into an array in $this->settings
	 *
	 * @return void
	 */
	protected function getImageDimensions() {
		foreach ($this->settings['general']['slidesArray'] as $key => $value) {
			$imagePath = Environment::getPublicPath() . '/uploads/tx_frsupersized/' . trim($value);
			$imageInfo = getimagesize($imagePath);
			// get the values for javascript array 'slides'
			$this->settings['general']['slideValues'][$key]['width'] = $imageInfo[0];
			$this->settings['general']['slideValues'][$key]['height'] = $imageInfo[1];
		}
	}

	/**
	 * Injects the Configuration Manager and is initializing the framework settings
	 * Function is used to override the merge of settings via TS & flexforms
	 * from: http://forge.typo3.org/projects/typo3v4-mvc/wiki/How_to_control_override_of_TS-Flexform_configuration / modified by Frank Frewer
	 *
	 * @param ConfigurationManagerInterface An instance of the Configuration Manager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$tsSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'frsupersized', 'frsupersized_pi1');
		$originalSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
		// start override 'overrideFlexformSettingsIfEmpty'
		if (isset($tsSettings['settings']['overrideFlexformSettingsIfEmpty'])) {
			$overrideSettings = GeneralUtility::trimExplode(',', $tsSettings['settings']['overrideFlexformSettingsIfEmpty'], TRUE);
			foreach($overrideSettings as $keyPath) {
				// very dirty hack to work with settings like foo, foo.bar, foo.bar.baz
				// TODO: make it independent from the depth of $keyPath
				$keyPathArray = GeneralUtility::trimExplode('.', $keyPath);
				if (count($keyPathArray) == 1) {
					// if flexform setting is empty and value is available in TS
					if ((!isset($originalSettings[$keyPathArray[0]]) || empty($originalSettings[$keyPathArray[0]]))
							&& isset($tsSettings['settings'][$keyPathArray[0]])){
						$originalSettings[$keyPathArray[0]] = $tsSettings['settings'][$keyPathArray[0]];
					}
				} elseif (count($keyPathArray) == 2) {
					// if flexform setting is empty and value is available in TS
					if ((!isset($originalSettings[$keyPathArray[0]][$keyPathArray[1]]) || empty($originalSettings[$keyPathArray[0]][$keyPathArray[1]]))
							&& isset($tsSettings['settings'][$keyPathArray[0]][$keyPathArray[1]])){
						$originalSettings[$keyPathArray[0]][$keyPathArray[1]] = $tsSettings['settings'][$keyPathArray[0]][$keyPathArray[1]];
					}
				} elseif (count($keyPathArray) == 3) {
					// if flexform setting is empty and value is available in TS
					if ((!isset($originalSettings[$keyPathArray[0]][$keyPathArray[1]][$keyPathArray[2]]) || empty($originalSettings[$keyPathArray[0]][$keyPathArray[1]][$keyPathArray[2]]))
							&& isset($tsSettings['settings'][$keyPathArray[0]][$keyPathArray[1]][$keyPathArray[2]])){
						$originalSettings[$keyPathArray[0]][$keyPathArray[1]][$keyPathArray[2]] = $tsSettings['settings'][$keyPathArray[0]][$keyPathArray[1]][$keyPathArray[2]];
					}
				}
			}
		}
		// start override 'overrideFlexformSettingsFromTS'
		if (isset($tsSettings['settings']['overrideFlexformSettingsFromTS'])) {
			$overrideSettings = GeneralUtility::trimExplode(',', $tsSettings['settings']['overrideFlexformSettingsFromTS'], TRUE);
			foreach($overrideSettings as $keyPath) {
				// very dirty hack to work with settings like foo, foo.bar, foo.bar.baz
				// TODO: make it independent from the depth of $keyPath
				$keyPathArray = GeneralUtility::trimExplode('.', $keyPath);
				if (count($keyPathArray) == 1) {
					// if flexform setting is 'fromTS' and value is available in TS
					if ($originalSettings[$keyPathArray[0]] == 'fromTS' && isset($tsSettings['settings'][$keyPathArray[0]])){
						$originalSettings[$keyPathArray[0]] = $tsSettings['settings'][$keyPathArray[0]];
					}
				} elseif (count($keyPathArray) == 2) {
					// if flexform setting is 'fromTS' and value is available in TS
					if ($originalSettings[$keyPathArray[0]][$keyPathArray[1]] == 'fromTS' && isset($tsSettings['settings'][$keyPathArray[0]][$keyPathArray[1]])){
						$originalSettings[$keyPathArray[0]][$keyPathArray[1]] = $tsSettings['settings'][$keyPathArray[0]][$keyPathArray[1]];
					}
				} elseif (count($keyPathArray) == 3) {
					// if flexform setting is 'fromTS' and value is available in TS
					if ($originalSettings[$keyPathArray[0]][$keyPathArray[1]][$keyPathArray[2]] == 'fromTS' && isset($tsSettings['settings'][$keyPathArray[0]][$keyPathArray[1]][$keyPathArray[2]])){
						$originalSettings[$keyPathArray[0]][$keyPathArray[1]][$keyPathArray[2]] = $tsSettings['settings'][$keyPathArray[0]][$keyPathArray[1]][$keyPathArray[2]];
					}
				}
			}
		}
		$this->settings = $originalSettings;
	}

	/**
	 * Help function for function array_walk_recursive to resolve relative extension path in $item from 'EXT:'
	 *
	 * @param string $item The value of a key/value pair from an array. $item is referenced: The changed value will be write back to the array.
	 * @param void $key The key of a key/value pair from an array
	 */
	public function resolveRelativeExtPath(&$item, $key) {
		if (substr($item,0,4) == 'EXT:') {
			$item = '/' . str_replace(Environment::getPublicPath() . '/', '', GeneralUtility::getFileAbsFileName($item));;
		}
	}

	/**
	 * This function creates another stand-alone instance of the Fluid view to render e.g. a header data template
	 *
	 * @param string $templateName the name of the template to use
	 * @param string $format the file type of the template to use
	 * @return \TYPO3\CMS\Fluid\View\StandaloneView  the Fluid instance
	 */
	protected function getRenderer($templateName = 'Default', $format = 'html') {
		$fluidViewInstance = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$fluidViewInstance->setFormat($format);
		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$templateRootPath = GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
		$templatePathAndFilename = $templateRootPath . $this->request->getControllerName() . '/' . $templateName . '.' . $format;
		$fluidViewInstance->setTemplatePathAndFilename($templatePathAndFilename);
		$fluidViewInstance->assign('settings', $this->settings);
		return $fluidViewInstance;
	}
}
