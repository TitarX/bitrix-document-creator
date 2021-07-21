<?php

namespace InfoExpert\DocumentCreator\Creators;

use \Bitrix\Main\Loader;
use \Bitrix\Main\IO\File;
use \Bitrix\DocumentGenerator\Body\Docx;
use \InfoExpert\DocumentCreator\Transformers\PdfDocumentTransformer;
use \InfoExpert\DocumentCreator\Helpers\SettingsHelper;
use \InfoExpert\DocumentCreator\Helpers\LogsHelper;
use \InfoExpert\DocumentCreator\Entities\Product;


class DocxDocumentCreator
{
    private $product = null;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    private function prepareDocumentCreation(): ?string
    {
        $settingsHelper = SettingsHelper::getInstance();
        $docsDirPath = $settingsHelper->getDocsDirPath();
        $currentDocsDirName = date('Y_m_d', time());
        $currentDocsDirPath = "{$docsDirPath}/{$currentDocsDirName}";
        if (!file_exists($currentDocsDirPath)) {
            $mkdirResult = mkdir($currentDocsDirPath, 0777, true);
            if ($mkdirResult) {
                return $currentDocsDirPath;
            } else {
                return null;
            }
        } else {
            return $currentDocsDirPath;
        }
    }

    public function createDocument(bool $doPdfTransform): void
    {
        $currentDocsDirPath = $this->prepareDocumentCreation();
        if (isset($currentDocsDirPath) && Loader::includeModule('documentgenerator')) {
            $this->product->applyWildcardValues();

            $templateDocxFile = new File($this->product->getTemplateDocxPath());
            $templateDocxFileContents = $templateDocxFile->getContents();
            $docx = new Docx($templateDocxFileContents);
            $docx->normalizeContent();
            $docx->setValues($this->product->getWildcardValues());
            $docxProcessResult = $docx->process();

            if ($docxProcessResult->isSuccess()) {
                $dateTime = new \DateTime('now');
                $docFileBaseName = $dateTime->format('Y_m_d_H_i_s_u');
                $docFileBasePath = "{$currentDocsDirPath}/{$docFileBaseName}";
                $docFilePath = "{$docFileBasePath}.docx";

                $docContent = $docx->getContent();
                $filePutContentsResult = file_put_contents($docFilePath, $docContent);
                if ($filePutContentsResult) {
                    $this->product->setDocumentDocxPath($docFilePath);
                    $this->product->setDocumentCreateDate(time());
                    $this->product->setDocumentCreateAttemptStatus(true);

                    // Конвертация в PDF >>>
                    if ($doPdfTransform) {
                        $pdfDocumentTransformer = new PdfDocumentTransformer($this->product);
                        $pdfDocumentTransformer->transformDocument($docFileBasePath);
                    }
                    // <<< Конвертация в PDF
                }
            } else {
                $arErrors = $docxProcessResult->getErrors();
                foreach ($arErrors as $error) {
                    LogsHelper::addLog(__FILE__, $error->getMessage(), 'errors.txt');
                }
            }
        }
    }
}

?>