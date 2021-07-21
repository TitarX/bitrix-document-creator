<?php

use \Bitrix\Main\Application;

$documentRoot = Application::getDocumentRoot();
$originalInitPhpPath = "{$documentRoot}/bitrix/php_interface/init.php";
if (file_exists($originalInitPhpPath)) {
    include_once $originalInitPhpPath;
}

require_once "{$documentRoot}/local/apps/DocumentCreator/index.php";