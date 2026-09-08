<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Contracts/ModuleInterface.php';
require_once dirname(__DIR__) . '/src/Contracts/ModuleMetadata.php';
require_once dirname(__DIR__) . '/src/Contracts/Response.php';
require_once dirname(__DIR__) . '/src/Core/ModuleRegistry.php';
require_once dirname(__DIR__) . '/src/Core/SchemaManager.php';
require_once dirname(__DIR__) . '/src/Core/SlugValidator.php';
require_once dirname(__DIR__) . '/src/Core/ReferenceImport.php';
require_once dirname(__DIR__) . '/src/Core/LocationTree.php';
require_once dirname(__DIR__) . '/src/Core/ServiceCatalog.php';
require_once dirname(__DIR__) . '/src/Core/VehicleCatalog.php';
require_once dirname(__DIR__) . '/src/Modules/CoreModule.php';
