<?php

return array(
    'services' => array(
        'config_compiler' => array(
            'class' => 'Butterfly\Component\DI\Builder\ConfigCompiler',
            'arguments' => array('@builder'),
        ),
        'builder' => array(
            'class' => 'Butterfly\Component\DI\Builder\ContainerConfigBuilder',
            'calls' => array(
                array('setResolver', array('@resolver')),
                array('addServiceVisitors', array('#service_visitor')),
            ),
        ),
        'resolver' => array(
            'class' => 'Butterfly\Component\DI\Builder\ParameterResolver\Resolver',
        ),
        'configuration_validator' => array(
            'class' => 'Butterfly\Component\DI\Builder\ServiceVisitor\ConfigurationValidator',
            'arguments' => array(
                array('class', 'factoryMethod', 'factoryStaticMethod', 'scope', 'arguments', 'calls', 'properties', 'preTriggers', 'postTriggers', 'tags', 'alias', 'parent'),
                array('singleton', 'factory', 'prototype', 'synthetic'),
            ),
        ),
        'service_collector' => array(
            'class' => 'Butterfly\Component\DI\Builder\ServiceCollector\ServiceCollector',
        ),
        'alias_collector' => array(
            'class' => 'Butterfly\Component\DI\Builder\ServiceCollector\AliasCollector',
        ),
        'tag_collector' => array(
            'class' => 'Butterfly\Component\DI\Builder\ServiceCollector\TagCollector',
        ),
    ),
    'tags' => array(
        'service_visitor' => array(
            'configuration_validator',
            'service_collector',
            'alias_collector',
            'tag_collector',
        ),
    ),
);
