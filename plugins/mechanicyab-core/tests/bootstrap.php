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
require_once dirname(__DIR__) . '/src/Core/MechanicProfile.php';
require_once dirname(__DIR__) . '/src/Contracts/MechanicRepository.php';
require_once dirname(__DIR__) . '/src/Core/MechanicAuthorization.php';
require_once dirname(__DIR__) . '/src/Core/MechanicService.php';
require_once dirname(__DIR__) . '/src/Core/HoursService.php';
require_once dirname(__DIR__) . '/src/Core/VerificationService.php';
require_once dirname(__DIR__) . '/src/Core/MechanicPublicResource.php';
require_once dirname(__DIR__) . '/src/Contracts/MechanicSupportingRepository.php';
require_once dirname(__DIR__) . '/src/Core/MechanicOperationsService.php';
require_once dirname(__DIR__) . '/src/Contracts/SearchRequest.php';
require_once dirname(__DIR__) . '/src/Contracts/SearchProvider.php';
require_once dirname(__DIR__) . '/src/Contracts/MapAdapter.php';
require_once dirname(__DIR__) . '/src/Core/OrganicRanking.php';
require_once dirname(__DIR__) . '/src/Core/SearchService.php';
require_once dirname(__DIR__) . '/src/Core/OpenDirectionsAdapter.php';
require_once dirname(__DIR__) . '/src/Modules/SearchModule.php';
require_once dirname(__DIR__) . '/src/Modules/CoreModule.php';
