<?php
namespace InfoExpert\DocumentCreator\Transformers;

use \Bitrix\Main\Loader;
use \Bitrix\Transformer\DocumentTransformer;
use \InfoExpert\DocumentCreator\Helpers\SettingsHelper;
use \InfoExpert\DocumentCreator\Helpers\LogsHelper;
use \InfoExpert\DocumentCreator\Callbacks\PdfDocumentTransformerCallback;
use \InfoExpert\DocumentCreator\Entities\Product;

class PdfDocumentTransformer
{
    private $product = null;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function transformDocument($docFileBasePath) : void
    {
        if(Loader::includeModule('transformer')) {
            $params = array(
            'product' => $this->product,
            'doc_file_base_path' => $docFileBasePath
            );

            $documentDocxPath = $this->product->getDocumentDocxPath();
            $formats = array('pdf');
            $module = array('documentgenerator');
            $callback = array('\InfoExpert\DocumentCreator\Callbacks\PdfDocumentTransformerCallback');
            $documentTransformer = new DocumentTransformer();
            $result = $documentTransformer->transform($documentDocxPath, $formats, $module, $callback, $params);

            if($result->isSuccess()) {
                $foundFile = new \Bitrix\Transformer\File($documentDocxPath);
                $publicPath = $foundFile->getPublicPath();
                $command = \Bitrix\Transformer\Command::getByFile($publicPath);
                if(!empty($command)) {
                    $error = $command->getError();
                    if(!empty($error)) {
                        $errorMessage = 'Command error: ' . $error->getMessage();
                        LogsHelper::addLog(__FILE__, $errorMessage, 'errors.txt');
                    }
                }
            }
            else {
                $errors = $result->getErrors();
                foreach($errors as $error) {
                    $errorMessage = 'Transform error: ' . $error->getMessage();
                    LogsHelper::addLog(__FILE__, $errorMessage, 'errors.txt');
                }
            }
        }
    }
}
?>