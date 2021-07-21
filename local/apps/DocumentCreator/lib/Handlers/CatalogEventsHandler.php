<?php

namespace InfoExpert\DocumentCreator\Handlers;

use \Bitrix\Main\Event;
use \Bitrix\Main\Entity\EventResult;
use \Bitrix\Main\Loader;
use \InfoExpert\DocumentCreator\Creators\DocxDocumentCreator;
use \InfoExpert\DocumentCreator\Helpers\SettingsHelper;
use \InfoExpert\DocumentCreator\Helpers\LogsHelper;
use \InfoExpert\DocumentCreator\Entities\Product;
use \InfoExpert\DocumentCreator\Entities\ProductItem;
use \InfoExpert\DocumentCreator\Entities\ProductSet;
use \InfoExpert\DocumentCreator\Entities\ProductItemFactory;
use \InfoExpert\DocumentCreator\Entities\ProductSetFactory;

class CatalogEventsHandler
{
    public static function OnPriceUpdate(Event $event): EventResult
    {
        $eventResult = new EventResult();

        $eventParameters = $event->getParameters();
        $productId = $eventParameters['fields']['PRODUCT_ID'];
        $newProductPrice = $eventParameters['fields']['PRICE'];

        $priceOfferId = $eventParameters['object']->get('ID');
        $priceOffer = \CPrice::GetByID($priceOfferId);
        $productPrice = $priceOffer['PRICE'];

        if (empty($productId)) {
            $productId = $priceOffer['PRODUCT_ID'];
        }

        if (Loader::includeModule('catalog') && !empty($productId) && !empty($newProductPrice) && floatval($newProductPrice) !== floatval($productPrice)) {
            $arProduct = \CCatalogProduct::GetByID($productId);

            $settingsHelper = SettingsHelper::getInstance();
            $doPdfTransform = $settingsHelper->getDoPdfTransform();

            switch ($arProduct['TYPE']) {
                case '1': // Цена изменена у простого товара
                    $productItem = ProductItemFactory::createProduct($productId, $newProductPrice);
                    if (isset($productItem)) {
                        $docxDocumentCreator = new DocxDocumentCreator($productItem);
                        $docxDocumentCreator->createDocument($doPdfTransform);
                        $productItem->saveProduct();
                    }

                    $productSetGenerator = ProductSetFactory::createProductSets($productId, $newProductPrice);
                    foreach ($productSetGenerator as $productSet) {
                        $docxDocumentCreator = new DocxDocumentCreator($productSet);
                        $docxDocumentCreator->createDocument($doPdfTransform);
                        $productSet->saveProduct();
                    }

                    break;
                case '2': // Цена изменена у комплекта
                    $productSet = ProductSetFactory::createProductSet($productId, $newProductPrice);

                    if (isset($productSet)) {
                        $docxDocumentCreator = new DocxDocumentCreator($productSet);
                        $docxDocumentCreator->createDocument($doPdfTransform);
                        $productSet->saveProduct();
                    }

                    break;
            }
        } else {
            $errorMessage = 'Не получен идентификатор товара';
            LogsHelper::addLog(__FILE__, $errorMessage, 'errors.txt');
        }

        return $eventResult;
    }
}

?>