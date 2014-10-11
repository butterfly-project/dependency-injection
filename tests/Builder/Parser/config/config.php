<?php

return array(
    'parameters' => array(
        'parameter.one'  => 'value1',
        'parameter.two'  => 'value2',
        'parameter.tree' => 'value3',
    ),
    'interfaces' => array(
        'Me\IFooAware' => 'foo',
    ),
    'services'   => array(
        'foo'                 => array(
            'class' => 'Me\Foo',
            'tags'  => array('foobars'),
        ),
        'bar'                 => array(
            'class' => 'Me\Bar',
            'tags'  => array('foobars'),
        ),

        'factory'             => array(
            'class'      => 'Me\Factory',
            'arguments'  => array('%parameter.one%'),
            'calls'      => array(
                array('setParameterTwo', array('%parameter.two%')),
                array('setParameterThree', array('%parameter.tree%')),
            ),
            'properties' => array(
                'foo'     => '@foo',
                'foobars' => '#foobars',
            ),
            'alias'      => 'factory.alias',
        ),

        'from_factory'        => array(
            'factoryMethod' => array('@factory', 'create'),
            'scope'         => 'prototype',
        ),

        'from_static_factory' => array(
            'factoryStaticMethod' => array('Me\Factory', 'create'),
            'preTriggers'         => array(
                array(
                    'service'   => '@bar',
                    'method'    => 'beforeCreate',
                    'arguments' => array('value1'),
                ),
            ),
            'postTriggers'         => array(
                array(
                    'service'   => '@bar',
                    'method'    => 'afterCreate',
                    'arguments' => array('value2'),
                ),
            ),
        ),
    ),
);