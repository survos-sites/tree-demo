<?php

// @todo: https://pen-y-fan.github.io/2021/05/23/Standard-setup-for-PHP-projects-2021/

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src/',
    ]);
    $parameters->set(Option::PHP_VERSION_FEATURES,  \Rector\Core\ValueObject\PhpVersion::PHP_80);


    // Define what rule sets will be applied
    $containerConfigurator->import(SetList::PHP_80);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    $parameters = $containerConfigurator->parameters();
    $parameters->set(
        Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
        __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml'
    );

    $parameters->set(Option::BOOTSTRAP_FILES, [
        __DIR__ . '/vendor/autoload.php',
    ]);

//    $services->set(AddParamTypeDeclarationRector::class);

    // endregion

//    $services->set(AddParamTypeDeclarationRector::class)
//        ->call('configure', [[
//            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => ValueObjectInliner::inline([
//                new AddParamTypeDeclaration('SomeClass', 'process', 0, new StringType()),
//            ]),
//        ]]);
//
    $containerConfigurator->import(SymfonySetList::SYMFONY_54);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);

    // register a single rule
//    $services->set(\Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector::class);
//    $services->set(\PhpCsFixer\Fixer\FunctionNotation\PhpdocToParamTypeFixer::class);
//    $services->set(\PHPStan\Type\Symfony\ParameterDynamicReturnTypeExtension::class);
//     $services->set(TypehintR::class);
};
