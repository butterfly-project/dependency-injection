<?php

return array(
    'services' => array(
        'container.configuration.builder' => array(
            'class' => 'Syringe\Component\DI\Builder\ContainerConfigurationBuilder',
            'arguments' => array('@builder', '@config_parser'),
        ),
        'builder' => array(
            'class' => 'Syringe\Component\DI\Builder\Builder',
            'calls' => array(
                array('setResolver', ['@resolver']),
                array('addServiceVisitors', ['#service_visitor']),
            ),
        ),
        'resolver' => array(
            'class' => 'Syringe\Component\DI\Builder\ParameterResolver\Resolver',
        ),
        'configuration_validator' => array(
            'class' => 'Syringe\Component\DI\Builder\ServiceVisitor\ConfigurationValidator',
            'arguments' => array(
                array('class', 'factoryMethod', 'factoryStaticMethod', 'scope', 'arguments', 'calls', 'properties', 'preTriggers', 'postTriggers', 'tags', 'alias', 'parent'),
                array('singleton', 'factory', 'prototype', 'synthetic'),
            ),
        ),
        'service_collector' => array(
            'class' => 'Syringe\Component\DI\Builder\ServiceCollector\ServiceCollector',
        ),
        'alias_collector' => array(
            'class' => 'Syringe\Component\DI\Builder\ServiceCollector\AliasCollector',
        ),
        'tag_collector' => array(
            'class' => 'Syringe\Component\DI\Builder\ServiceCollector\TagCollector',
        ),
        'config_parser' => array(
            'class' => 'Syringe\Component\DI\Builder\Parser\DelegatedParser',
            'arguments' => array('#parser'),
        ),
        'php_parser' => array(
            'class' => 'Syringe\Component\DI\Builder\Parser\PhpParser',
        ),
        'yaml_parser' => array(
            'class' => 'Syringe\Component\DI\Builder\Parser\Sf2YamlParser',
        ),
    ),
    'tags' => array(
        'service_visitor' => array(
            'configuration_validator',
            'service_collector',
            'alias_collector',
            'tag_collector',
        ),
        'parser' => array(
            'php_parser',
            'yaml_parser',
        ),
    ),
);
