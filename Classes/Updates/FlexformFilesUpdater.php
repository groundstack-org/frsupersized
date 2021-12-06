<?php
declare(strict_types=1);

namespace FFREWER\Frsupersized\Updates;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\Search\FileSearchDemand;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\ConfirmableInterface;
use TYPO3\CMS\Install\Updates\Confirmation;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Migrate flexform files
 */
class FlexformFilesUpdater implements UpgradeWizardInterface, RepeatableInterface, ChattyInterface, ConfirmableInterface
{

    /**
     * @var Confirmation
     */
    protected $confirmation;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct()
    {
        $this->confirmation = new Confirmation(
            'This fr_supersized migration script is experimental!!! Ensure you have a backup of your database!!!',
            $this->getDescription(),
            false,
            'Yes, I understand!',
            '',
            true
        );
    }

    /**
     * @return string Unique identifier of this updater
     */
    public function getIdentifier(): string
    {
        return 'supersizedFlexformFiles';
    }

    /**
     * @return string Title of this updater
     */
    public function getTitle(): string
    {
        return 'Supersized flexform files migration';
    }

    /**
     * @return string Longer description of this updater
     */
    public function getDescription(): string
    {
        return 'Migrates the files in Supersized flexform to FAL relations.';
    }

    /**
     * Checks if an update is needed
     *
     * @return bool Whether an update is needed (TRUE) or not (FALSE)
     */
    public function updateNecessary(): bool
    {
        return true;
    }

    /**
     * @return string[] All new fields and tables must exist
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }

    /**
     * Performs the database update.
     *
     * @return bool Whether it worked (TRUE) or not (FALSE)
     */
    public function executeUpdate(): bool
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        /** @var StorageRepository $storageRepository */
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        /** @var ResourceStorage $resourceStorage */
        $resourceStorage = $storageRepository->findByIdentifier(1);
        //
        // Get all supersized content elements
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->select('*');
        $queryBuilder->from('tt_content')->where(
            $queryBuilder->expr()->eq('CType', '"list"')
        )->andWhere(
            $queryBuilder->expr()->eq('list_type', '"frsupersized_pi1"')
        )->setMaxResults(1000)->orderBy('uid');
        //
        // Fetch and progress related plugins
        $records = $queryBuilder->execute()->fetchAll();
        $filesFoundAll = 0;
        $multipleFoundFiles = [];
        foreach ($records as $record) {
            $flexFormArray = GeneralUtility::xml2array($record['pi_flexform']);
            //
            // Continue if record is already precessed!
            if ((int)$flexFormArray['data']['sDEF']['lDEF']['settings.general.images']['vDEF'] > 0) {
                $this->output->writeln('Ignore record with uid:' . $record['uid'] . '!');
                continue;
            }
            //
            $notFoundFiles = [];
            $fileReferencesToCreate = [];
            //
            $files = [];
            if (isset($flexFormArray['data']['sDEF']['lDEF']['settings.general.slides']['vDEF'])) {
                $slides = $flexFormArray['data']['sDEF']['lDEF']['settings.general.slides']['vDEF'];
                $slides = str_replace(',', "\n", $slides);
                $slides = GeneralUtility::trimExplode("\n", $slides, true);
                foreach ($slides as $key => $value) {
                    $files[$key]['filename'] = $value;
                }
                //unset($flexFormArray['data']['sDEF']['lDEF']['settings.general.slides']);
            }
            if (isset($flexFormArray['data']['sDEF']['lDEF']['settings.general.slideCaptions']['vDEF'])) {
                $captions = $flexFormArray['data']['sDEF']['lDEF']['settings.general.slideCaptions']['vDEF'];
                $captions = str_replace(',', "\n", $captions);
                $captions = GeneralUtility::trimExplode("\n", $captions, true);
                foreach ($captions as $key => $value) {
                    $files[$key]['caption'] = $value;
                }
                //unset($flexFormArray['data']['sDEF']['lDEF']['settings.general.slideCaptions']);
            }
            if (isset($flexFormArray['data']['sDEF']['lDEF']['settings.general.slideUrl']['vDEF'])) {
                $urls = $flexFormArray['data']['sDEF']['lDEF']['settings.general.slideUrl']['vDEF'];
                $urls = str_replace(',', "\n", $urls);
                $urls = GeneralUtility::trimExplode("\n", $urls, true);
                foreach ($urls as $key => $value) {
                    $files[$key]['url'] = $value;
                }
                //unset($flexFormArray['data']['sDEF']['lDEF']['settings.general.slideUrl']);
            }
            //
            // Write file references
            foreach ($files as $fileSort => $fileFlex) {
                $filesFoundAll++;
                $sysFileUid = 0;
                $searchDemand = FileSearchDemand::createForSearchTerm($fileFlex['filename'])
                    ->withRecursive()
                    ->withMaxResults(10);
                $foundFiles = $resourceStorage->searchFiles($searchDemand);
                //
                // Check if the files is just one time available!
                // Otherwise we're not able to create the right relation!
                if (count($foundFiles) === 0) {
                    $notFoundFiles[] = '[' . $fileFlex['filename'] . ', pid:' . $record['pid'] . ', uid:' . $record['uid'] . ']';
                    //
                    // copy from uploads into fileadmin
                    //$basepath = '/...../htdocs/typo3-2021/';
                    //$source = $basepath . 'uploads/tx_frsupersized/' . $fileFlex['filename'];
                    //$target = $basepath . 'fileadmin/images/tx_frsupersized/' . $fileFlex['filename'];
                    //copy($source, $target);
                } else if (count($foundFiles) > 1) {
                    //
                    // Check if the found files are existing files
                    $foundMultiple = false;
                    $useFile = null;
                    /** @var File $foundFile */
                    foreach ($foundFiles as $foundFile) {
                        //
                        // If there are multiple existing sys_file records, but only one have an existing file
                        // we are able to identify the target file!
                        if ($foundFile->exists() && $useFile === null) {
                            $useFile = $foundFile;
                        } else if ($foundFile->exists()) {
                            $foundMultiple = true;
                        }
                    }
                    //
                    // If files have differ identifiers, display a message that we use the first one
                    if ($foundMultiple) {
                        $multipleFoundFiles[] = '[' . $fileFlex['filename'] . ', pid:' . $record['pid'] . ', uid:' . $record['uid'] . ', count:' . count($foundFiles) . ']';
                    }
                    $sysFileUid = $useFile->getUid();
                } else {

                    $sysFileUid = $foundFiles->current()->getUid();
                }
                //
                // Prepare file reference
                if ($sysFileUid > 0) {
                    $fileReferencesToCreate[] = [
                        'tablenames' => 'tt_content',
                        'fieldname' => 'supersized_image',
                        'sorting_foreign' => $fileSort + 1,
                        'table_local' => 'sys_file',
                        'uid_local' => $sysFileUid,
                        'uid_foreign' => $record['uid'],
                        'pid' => $record['pid'],
                        'tstamp' => time(),
                        'crdate' => time()
                    ];
                }
            }
            if (count($notFoundFiles) > 0) {
                $this->output->writeln(
                    'Following files not found in fileadmin, please create them: ' .
                    implode(', ', $notFoundFiles) .
                    ' ATTENTION: You might need to load the related folder in filelist if you upload the files by using FTP/SSH!'
                );
                return false;
            }
            //
            // Create required file references
            foreach ($fileReferencesToCreate as $ttContentUid => $fileReference) {
                $this->insertFileReference($fileReference);
            }
            //
            // Set new flex form information about the amount of file references
            $flexFormArray['data']['sDEF']['lDEF']['settings.general.images']['vDEF'] = count($files);
            $this->updateFlexform($flexFormArray, $record['uid']);
        }
        //
        // Display messages
        if (count($multipleFoundFiles) > 0) {
            $this->output->writeln(
                'Following files are duplicated in fileadmin and a relation could not be resolved. ' .
                'Note that we are using the first found files for the following relations: ' .
                implode(', ', $multipleFoundFiles)
            );
        }
        $this->output->writeln('Migration has found ' . $filesFoundAll . ' files!');
        return true;
    }

    /**
     * @param array $fields
     */
    protected function insertFileReference(array $fields)
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder->insert('sys_file_reference')->values($fields)->execute();
    }

    /**
     * @param array $flexform
     * @param int $uid tt_content uid
     */
    protected function updateFlexform(array $flexform, int $uid)
    {
        /** @var FlexFormTools $flexFormTools */
        $flexFormTools = GeneralUtility::makeInstance(FlexFormTools::class);
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->update('tt_content')
            ->set('pi_flexform', $flexFormTools->flexArray2Xml($flexform))
            ->where($queryBuilder->expr()->eq('uid', $uid))
            ->execute();
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * Return a confirmation message instance
     *
     * @return Confirmation
     */
    public function getConfirmation(): Confirmation
    {
        return $this->confirmation;
    }

}