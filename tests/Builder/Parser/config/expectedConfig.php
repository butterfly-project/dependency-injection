<?php

return array (
    'parameters' =>
        array (
            'parameter.one' => 'value1',
            'parameter.two' => 'value2',
            'parameter.tree' => 'value3',
        ),
    'interfaces' =>
        array (
            'Me\\IFooAware' => 'foo',
        ),
    'services' =>
        array (
            'foo' =>
                array (
                    'class' => 'Me\\Foo',
                    'tags' =>
                        array (
                            0 => 'foobars',
                        ),
                ),
            'bar' =>
                array (
                    'class' => 'Me\\Bar',
                    'tags' =>
                        array (
                            0 => 'foobars',
                        ),
                ),
            'factory' =>
                array (
                    'class' => 'Me\\Factory',
                    'arguments' =>
                        array (
                            0 => '%parameter.one%',
                        ),
                    'calls' =>
                        array (
                            0 =>
                                array (
                                    0 => 'setParameterTwo',
                                    1 =>
                                        array (
                                            0 => '%parameter.two%',
                                        ),
                                ),
                            1 =>
                                array (
                                    0 => 'setParameterThree',
                                    1 =>
                                        array (
                                            0 => '%parameter.tree%',
                                        ),
                                ),
                        ),
                    'properties' =>
                        array (
                            'foo' => '@foo',
                            'foobars' => '#foobars',
                        ),
                    'alias' => 'factory.alias',
                ),
            'from_factory' =>
                array (
                    'factoryMethod' =>
                        array (
                            0 => '@factory',
                            1 => 'create',
                        ),
                    'scope' => 'prototype',
                ),
            'from_static_factory' =>
                array (
                    'factoryStaticMethod' =>
                        array (
                            0 => 'Me\\Factory',
                            1 => 'create',
                        ),
                    'preTriggers' =>
                        array (
                            0 =>
                                array (
                                    'service' => '@bar',
                                    'method' => 'beforeCreate',
                                    'arguments' =>
                                        array (
                                            0 => 'value1',
                                        ),
                                ),
                        ),
                    'postTriggers' =>
                        array (
                            0 =>
                                array (
                                    'service' => '@bar',
                                    'method' => 'afterCreate',
                                    'arguments' =>
                                        array (
                                            0 => 'value2',
                                        ),
                                ),
                        ),
                ),
        ),
);
