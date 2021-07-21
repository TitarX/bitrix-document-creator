<?php

namespace InfoExpert\DocumentCreator\Entities;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\IO\File;
use \InfoExpert\DocumentCreator\Helpers\SettingsHelper;

abstract class Product
{
    private $productId = null;
    private $productName = null;
    private $productPrice = null;
    private $productPreviewText = null;
    private $productFullText = null;
    private $wildcardValues = null;
    private $templateDocxPath = null;
    private $documentDocxPath = null;
    private $documentPdfPath = null;
    private $documentCreateDate = null;
    private $documentCreateAttemptDate = null;
    private $documentCreateAttemptStatus = null;

    public function __construct(int $productId)
    {
        $this->productId = $productId;
        $this->documentCreateAttemptDate = time();
        $this->documentCreateAttemptStatus = false;
    }

    // Идентификатор элемента
    public function getProductId(): int
    {
        return $this->productId;
    }

    // Название товара
    public function getProductName(): string
    {
        return $this->productName;
    }

    public function setProductName(string $value): void
    {
        $this->productName = $value;
    }

    // Цена товара
    public function getProductPrice(): float
    {
        return $this->productPrice;
    }

    public function setProductPrice(float $value): void
    {
        $this->productPrice = $value;
    }

    // Описание товара анонс
    public function getProductPreviewText(): string
    {
        return $this->productPreviewText;
    }

    public function setProductPreviewText(string $value): void
    {
        $this->productPreviewText = $value;
    }

    // Описание товара детальное
    public function getProductFullText(): string
    {
        return $this->productFullText;
    }

    public function setProductFullText(string $value): void
    {
        $this->productFullText = $value;
    }

    // Массив подстановочных значений для генерации документа
    public function getWildcardValues(): array
    {
        return $this->wildcardValues;
    }

    public function setWildcardValues(array $value): void
    {
        $this->wildcardValues = $value;
    }

    // Путь к файлу шаблона
    public function getTemplateDocxPath(): string
    {
        return $this->templateDocxPath;
    }

    public function setTemplateDocxPath(string $value): void
    {
        $this->templateDocxPath = $value;
    }

    // Путь к сформированному файлу DOCX-документа
    public function getDocumentDocxPath(): string
    {
        return $this->documentDocxPath;
    }

    public function setDocumentDocxPath(string $value): void
    {
        $this->documentDocxPath = $value;
    }

    // Путь к сформированному файлу PDF-документа
    public function getDocumentPdfPath(): string
    {
        return $this->documentPdfPath;
    }

    public function setDocumentPdfPath(string $value): void
    {
        $this->documentPdfPath = $value;
    }

    // Дата формирования документа
    public function getDocumentCreateDate(): int
    {
        return $this->documentCreateDate;
    }

    public function setDocumentCreateDate(int $value): void
    {
        $this->documentCreateDate = $value;
    }

    // Дата последней попытки формирования документа
    public function getDocumentCreateAttemptDate(): int
    {
        return $this->documentCreateAttemptDate;
    }

    public function setDocumentCreateAttemptDate(int $value): void
    {
        $this->documentCreateAttemptDate = $value;
    }

    // Статус последней попытки формирования документа
    public function getDocumentCreateAttemptStatus(): bool
    {
        return $this->documentCreateAttemptStatus;
    }

    public function setDocumentCreateAttemptStatus(bool $value): void
    {
        $this->documentCreateAttemptStatus = $value;
    }

    private function deleteFile(string $filePath): void
    {
        $file = new File($filePath);
        if ($file->isExists()) {
            $directory = $file->getDirectory();
            $file->delete();
            if ($directory->isExists()) {
                $directoryChildrens = $directory->getChildren();
                if (empty($directoryChildrens)) {
                    $directory->delete();
                }
            }
        }
    }

    private function deleteFileByPropertyValue(string $propertyCode): void
    {
        $productId = $this->getProductId();

        if (Loader::includeModule('iblock')) {
            $result = \CIBlockElement::GetByID($productId);
            if ($element = $result->GetNextElement()) {
                $documentRoot = Application::getDocumentRoot();
                $property = $element->GetProperty($propertyCode);
                $propertyValue = $property['VALUE'];
                $filePath = $documentRoot . $propertyValue;
                $this->deleteFile($filePath);
            }
        }
    }

    public function applyWildcardValues(): void
    {
        $arWildcardValues = array();
        $thisWildcardValues = $this->getWildcardValues();
        foreach ($thisWildcardValues as $wildcardIndex => $wildcardValue) {
            if (is_string($wildcardValue)) {
                if (method_exists($this, $wildcardValue)) {
                    $arWildcardValues[$wildcardIndex] = $this->$wildcardValue();
                } else {
                    $arWildcardValues[$wildcardIndex] = $wildcardValue;
                }
            }

            $this->setWildcardValues($arWildcardValues);
        }
    }

    abstract public function saveProduct(): void;

    protected function saveProductDoc(): void
    {
        if (Loader::includeModule('iblock')) {
            $productId = $this->getProductId();

            $documentCreateDate = $this->getDocumentCreateDate();
            $documentCreateAttemptDate = $this->getDocumentCreateAttemptDate();
            $documentCreateAttemptStatus = $this->getDocumentCreateAttemptStatus();
            $documentDocxPath = $this->getDocumentDocxPath();

            $arValues = array();
            if (!empty($documentCreateDate)) {
                $dateTime = new \DateTime();
                $dateTime->setTimestamp($documentCreateDate);
                $arValues['DOCUMENT_CREATE_DATE'] = $dateTime->format('d.m.Y H:i:s');
            }
            if (!empty($documentCreateAttemptDate)) {
                $dateTime = new \DateTime();
                $dateTime->setTimestamp($documentCreateAttemptDate);
                $arValues['DOCUMENT_CREATE_ATTEMPT_DATE'] = $dateTime->format('d.m.Y H:i:s');
            }
            if (!empty($documentCreateAttemptStatus)) {
                if (isset($documentCreateAttemptStatus)) {
                    if ($documentCreateAttemptStatus === true) {
                        $arValues['DOCUMENT_CREATE_ATTEMPT_STATUS'] = 'Success';
                    } else {
                        if ($documentCreateAttemptStatus === false) {
                            $arValues['DOCUMENT_CREATE_ATTEMPT_STATUS'] = 'Fail';
                        }
                    }
                }
            }
            if (!empty($documentDocxPath)) {
                $settingsHelper = SettingsHelper::getInstance();

                // if($settingsHelper->getDoOldFilesDelete()) {
                //     $this->deleteFileByPropertyValue('DOCUMENT_DOCX');
                // }

                $documentRoot = Application::getDocumentRoot();
                $siteDocumentDocxPath = str_replace($documentRoot, '', $documentDocxPath);
                $arFile = \CFile::MakeFileArray($siteDocumentDocxPath);
                $arValues['DOCUMENT_DOCX'] = $arFile;
            }

            if (!empty($arValues)) {
                \CIBlockElement::SetPropertyValuesEx($productId, false, $arValues);
            }

            // if(!empty($documentDocxPath)) {
            //     $this->deleteFile($documentDocxPath);
            // }
        }
    }

    public function saveProductPdf(): void
    {
        if (Loader::includeModule('iblock')) {
            $productId = $this->getProductId();

            $documentPdfPath = $this->getDocumentPdfPath();

            $arValues = array();
            if (!empty($documentPdfPath)) {
                $settingsHelper = SettingsHelper::getInstance();

                // if($settingsHelper->getDoOldFilesDelete()) {
                //     $this->deleteFileByPropertyValue('DOCUMENT_PDF');
                // }

                $documentRoot = Application::getDocumentRoot();
                $siteDocumentPdfPath = str_replace($documentRoot, '', $documentPdfPath);

                $arFile = \CFile::MakeFileArray($siteDocumentPdfPath);
                $arValues['DOCUMENT_PDF'] = $arFile;
            }

            if (!empty($arValues)) {
                \CIBlockElement::SetPropertyValuesEx($productId, false, $arValues);
            }

            // if(!empty($documentPdfPath)) {
            //     $this->deleteFile($documentPdfPath);
            // }
        }
    }
}

?>