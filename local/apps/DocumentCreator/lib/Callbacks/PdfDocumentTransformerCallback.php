<?php

namespace InfoExpert\DocumentCreator\Callbacks;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Transformer\InterfaceCallback;
use \InfoExpert\DocumentCreator\Helpers\LogsHelper;
use \InfoExpert\DocumentCreator\Entities\Product;

Loader::includeModule('transformer');

class PdfDocumentTransformerCallback implements InterfaceCallback
{
    public static function call($status, $command, $params, $result = array())
    {
        if (isset($params['fileId']) && $params['fileId'] > 0) {
            FileTransformer::clearInfoCache($params['fileId']);
        }

        if ($status === 1000) {
            $errorMessage = 'Произошла ошибка во время конвертации DOCX в PDF';
            LogsHelper::addLog(__FILE__, $errorMessage, 'errors.txt');

            return $errorMessage;
        }

        $docFileBasePath = $params['doc_file_base_path'];
        $pdfFilePath = "{$docFileBasePath}.pdf";
        $copyResult = copy($result['files']['pdf'], $pdfFilePath);
        if ($copyResult) {
            $product = $params['product'];
            $product->setDocumentPdfPath($pdfFilePath);
            $product->saveProductPdf();
        }

        return true;
    }
}

?>