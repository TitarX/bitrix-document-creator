<?php

namespace InfoExpert\DocumentCreator;

spl_autoload_register(
    function ($className) {
        $classPath = __DIR__ . '/lib/';

        $className = preg_replace('/^\\\\/', '', $className);
        $className = preg_replace('/^InfoExpert\\\\DocumentCreator\\\\/', '', $className);

        $arClassPath = explode('\\', $className);
        $classPath .= implode(DIRECTORY_SEPARATOR, $arClassPath);
        $classPath .= '.php';

        if (file_exists($classPath)) {
            include_once $classPath;
        }
    }
);

use \Bitrix\Main\Application;
use \Bitrix\Main\Loader;
use \Bitrix\Main\EventManager;
use \InfoExpert\DocumentCreator\Helpers\SettingsHelper;

$documentRoot = Application::getDocumentRoot();

$settingsHelper = SettingsHelper::getInstance();
// Директория приложения
$settingsHelper->setAppDirPath(__DIR__);
// Директория документов
$settingsHelper->setDocsDirPath("{$documentRoot}/upload/documentcreator");
// Директория логов
$settingsHelper->setLogsDirPath(__DIR__ . '/logs');
// Первый коэффициент для расчёта цены комплекта
$settingsHelper->setSetPriceCoefficient1(1.2);
// Второй коэффициент для расчёта цены комплекта
$settingsHelper->setSetPriceCoefficient2(1.8);
// Нужно ли конвертировать сформированный документ в PDF?
$settingsHelper->setDoPdfTransform(true);
// Нужно ли удалять старые фалы документов и опустевшие директории?
$settingsHelper->setDoOldFilesDelete(true);

/*
* 'Метка шаблона' => 'Строковое значение, или строка с меткой значения (имя метода доступа)'
*
* Пример:
* 'MyCompanyBankDetailRqBankName' => 'Инфо-Эксперт',
* 'ProductsProductPriceRaw~WZ=Y' => 'getProductPrice',
* 'ProductsProductName' => 'getProductName'
*
* Доступные метки значений:
* 'getProductPrice' - Цена продукта
* 'getProductPriceCoefficient1' - Цена продукта на коэффициент 1
* 'getProductPriceCoefficient2' - Цена продукта на коэффициент 2
* 'getProductName' - Название продукта
*/
// Подстановочные значения для простого продукта
$settingsHelper->setArProductItemWildcardValues(
    array(
        'MyCompanyBankDetailRqBankName' => 'Инфо-Эксперт',
        'ProductsProductPriceRaw' => 'getProductPrice',
        'ProductsProductName' => 'getProductName',
        'ProductPreviewText' => 'getProductPreviewText',
        'ProductFullText' => 'getProductFullText'
    )
);
// Подстановочные значения для комплекта
$settingsHelper->setArProductSetWildcardValues(
    array(
        'MyCompanyBankDetailRqBankName' => 'Инфо-Эксперт',
        'ProductsProductPriceRaw' => 'getProductPrice',
        'ProductsProductPriceRawC1' => 'getProductPriceCoefficient1',
        'ProductsProductPriceRawC2' => 'getProductPriceCoefficient2',
        'ProductsProductName' => 'getProductName',
        'ProductPreviewText' => 'getProductPreviewText',
        'ProductFullText' => 'getProductFullText'
    )
);

if (Loader::includeModule('catalog')) {
    EventManager::getInstance()->addEventHandler('catalog', '\Bitrix\Catalog\Price::OnUpdate', ['\InfoExpert\DocumentCreator\Handlers\CatalogEventsHandler', 'OnPriceUpdate']);
}
?>