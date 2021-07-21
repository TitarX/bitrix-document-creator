<?php

namespace InfoExpert\DocumentCreator\Entities;

use \Bitrix\Main\Loader;
use \InfoExpert\DocumentCreator\Entities\Product;
use \InfoExpert\DocumentCreator\Entities\ProductSet;
use \InfoExpert\DocumentCreator\Helpers\SettingsHelper;

class ProductSetFactory
{
    use ProductFactoryTrait;

    public static function createProductSets(int $productId, float $productPrice): \Generator
    {
        if (Loader::includeModule('catalog')) {
            $arProductSetsQuery = \CCatalogProductSet::getList(
                array(),
                array(
                    'ITEM_ID' => $productId,
                    'TYPE' => \CCatalogProductSet::TYPE_SET
                ),
                false,
                false,
                array(
                    'SET_ID', // ID комплекта
                    'OWNER_ID', // ID элемента комплекта
                    'ITEM_ID' // ID данного элемента товара в комплекте
                )
            );

            while ($arSet = $arProductSetsQuery->Fetch()) { // Комплекты
                if (!empty($arSet['OWNER_ID'])) {
                    $productSetPrice = 0;

                    // Состав комплекта, суммирование цен >>>
                    $arAllSetsByProduct = \CCatalogProductSet::getAllSetsByProduct($arSet['OWNER_ID'], \CCatalogProductSet::TYPE_SET);

                    if (!empty($arAllSetsByProduct) && is_array($arAllSetsByProduct)) {
                        $arSetComposition = array_shift($arAllSetsByProduct);
                        foreach ($arSetComposition['ITEMS'] as $arSetItem) {
                            $setItemPrice = 0;
                            if ($arSetItem['ITEM_ID'] !== $productId) {
                                $arSetItemPrice = \CPrice::getBasePrice($arSetItem['ITEM_ID']);
                                $setItemPrice = $arSetItemPrice['PRICE'];
                            } else {
                                $setItemPrice = $productPrice;
                            }

                            if (!empty($setItemPrice) && !empty($arSetItem['QUANTITY'])) {
                                $productSetPrice += intval($setItemPrice) * intval($arSetItem['QUANTITY']);
                            } else {
                                $productSetPrice = null;
                                break;
                            }
                        }
                    }
                    // <<< Состав комплекта, суммирование цен

                    if (!empty($productSetPrice) && $productSetPrice > $productPrice) {
                        // Обновляем цену комплекта
                        \CPrice::SetBasePrice($arSet['OWNER_ID'], $productSetPrice, 'RUB');

                        $productSet = self::prepareProductSet($arSet['OWNER_ID'], $productSetPrice);

                        if (isset($productSet)) {
                            yield $productSet;
                        }
                    }
                }
            }
        }
    }

    public static function createProductSet(int $productSetId, float $productSetPrice): ?ProductSet
    {
        return self::prepareProductSet($productSetId, $productSetPrice);
    }

    private static function prepareProductSet(string $productSetId, float $productSetPrice): ?ProductSet
    {
        $productSet = new ProductSet($productSetId);
        $productSet = self::fillIblockElementData($productSet, $productSetPrice);

        if (isset($productSet)) {
            $settingsHelper = SettingsHelper::getInstance();
            $arProductSetWildcardValues = $settingsHelper->getArProductSetWildcardValues();
            if (is_array($arProductSetWildcardValues)) {
                $productSet->setWildcardValues($arProductSetWildcardValues);

                $priceCoefficient1 = $settingsHelper->getSetPriceCoefficient1();
                $productSetPriceCoefficient1 = $productSetPrice * $priceCoefficient1;
                $productSet->setProductPriceCoefficient1($productSetPriceCoefficient1);

                $priceCoefficient2 = $settingsHelper->getSetPriceCoefficient2();
                $productSetPriceCoefficient2 = $productSetPrice * $priceCoefficient2;
                $productSet->setProductPriceCoefficient2($productSetPriceCoefficient2);
            }
        }

        return $productSet;
    }
}

?>