<?php

namespace InfoExpert\DocumentCreator\Entities;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \InfoExpert\DocumentCreator\Entities\Product;

trait ProductFactoryTrait
{
    private static function fillIblockElementData(Product $product, float $productPrice): ?Product
    {
        if (Loader::includeModule('iblock')) {
            $productId = $product->getProductId();
            if ($iBElement = \CIBlockElement::GetByID($productId)->GetNextElement()) {
                $iBElementFields = $iBElement->GetFields();
                $product->setProductPreviewText($iBElementFields['PREVIEW_TEXT']);
                $product->setProductFullText($iBElementFields['DETAIL_TEXT']);
                $product->setProductName($iBElementFields['NAME']);
                $product->setProductPrice($productPrice);

                $arTemplateDocxProperty = $iBElement->GetProperty('TEMPLATE_DOCX');
                if (!empty($arTemplateDocxProperty['VALUE'])) {
                    $documentRoot = Application::getDocumentRoot();
                    $templateDocxRelativePath = \CFile::GetPath($arTemplateDocxProperty['VALUE']);
                    $templateDocxPath = $documentRoot . $templateDocxRelativePath;
                    if (is_file($templateDocxPath)) {
                        $product->setTemplateDocxPath($templateDocxPath);
                    }
                }
            } else {
                $product = null;
            }
        } else {
            $product = null;
        }

        return $product;
    }
}

?>