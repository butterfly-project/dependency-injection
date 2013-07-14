<?php

namespace Syringe;

use Syringe\Exception\BuildObjectException;
use Syringe\Exception\BuildServiceException;
use Syringe\Exception\UndefinedParameterException;
use Syringe\Exception\UndefinedServiceException;
use Syringe\Exception\UndefinedTagException;
use Syringe\Keeper;

/**
 * Container
 * @todo Aliasses - Алиасы для сервисов
 * @todo Триггеры - Вызов метода до или после метода.
 * @todo Syntetic Service - Опеределение сервиса во время работы
 * @todo Наследование - Наследование конфигураций
 * @todo Аннотации - изучить возможности использования
 * @todo Private Field Injection - внедрение зависимости в не публичное свойство
 *
 * Container services
 * @todo Lazy Load Proxy - Ленивая загрузка сервиса
 * @todo Debug times - Отследивание таймингов вызова
 * @todo Debug Dependency Statistics - Количество зависимостей у сервисов. Количество ссылок на сервис. Количество запросов на использование контейнера.
 *
 * Container building
 * @todo phar archive - Phar консольное приложение сборки контейнера
 * @todo Composer integration - определение зависимости на уровне контейнера
 *
 * Container integration
 * @todo Sf2 integration
 * @todo Yii integration
 * @todo Sf1 integration
 */
class Container
{
    const SCOPE_SINGLETON = 'singleton';
    const SCOPE_FACTORY   = 'factory';
    const SCOPE_PROTOTYPE = 'prototype';

    const SERVICE_CONTAINER_ID = 'service_container';

    /**
     * @var array
     */
    protected $requiredSections = ['parameters', 'services', 'tags'];

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var Keeper\AbstractKeeper[]
     */
    protected $builders;

    /**
     * @param array $configuration
     * @throws \InvalidArgumentException if not required sections
     */
    public function __construct(array $configuration)
    {
        $noSections = array_diff($this->requiredSections, array_keys($configuration));
        if (!empty($noSections)) {
            throw new \InvalidArgumentException(sprintf("Sections '%s' is required", implode(', ', $noSections)));
        }

        $this->configuration = $configuration;

        $this->init();
    }

    protected function init()
    {
        $serviceBuilder = new ServiceBuilder(new ObjectBuilder(), $this);

        $this->builders = [
            self::SCOPE_SINGLETON => new Keeper\Singleton($serviceBuilder),
            self::SCOPE_PROTOTYPE => new Keeper\Prototype($serviceBuilder),
            self::SCOPE_FACTORY   => new Keeper\Factory($serviceBuilder),
        ];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasParameter($id)
    {
        return array_key_exists(strtolower($id), $this->configuration['parameters']);
    }

    /**
     * @param string $id
     * @return mixed
     * @throws UndefinedParameterException if parameter is not found
     */
    public function getParameter($id)
    {
        $id = strtolower($id);

        if (!$this->hasParameter($id)) {
            throw new UndefinedParameterException(sprintf("Parameter '%s' is not found", $id));
        }

        return $this->configuration['parameters'][$id];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return array_key_exists(strtolower($id), $this->configuration['services']);
    }

    /**
     * @param string $id
     * @return Object
     * @throws UndefinedServiceException if service is not found
     * @throws BuildServiceException if scope is not found
     * @throws BuildServiceException if fail to build service
     */
    public function get($id)
    {
        $id = strtolower($id);

        if (self::SERVICE_CONTAINER_ID == $id) {
            return $this;
        }

        if (!$this->has($id)) {
            throw new UndefinedServiceException(sprintf("Service '%s' is not found", $id));
        }

        $serviceConfiguration = $this->configuration['services'][$id];

        $scope = isset($serviceConfiguration['scope']) ? $serviceConfiguration['scope'] : self::SCOPE_SINGLETON;

        try {
            return $this->getBuilder($scope)->buildObject($id, $serviceConfiguration);
        } catch (BuildObjectException $e) {
            throw new BuildServiceException(sprintf("Failed to build service '%s': %s", $id, $e->getMessage()));
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasTag($name)
    {
        return array_key_exists(strtolower($name), $this->configuration['tags']);
    }

    /**
     * @return array
     */
    public function getTagsList()
    {
        return array_keys($this->configuration['tags']);
    }

    /**
     * @param string $name
     * @return Object[]
     * @throws Exception\UndefinedTagException if tag is not found
     */
    public function getServicesByTag($name)
    {
        $name = strtolower($name);

        if (!$this->hasTag($name)) {
            throw new UndefinedTagException(sprintf("Tag '%s' is not found", $name));
        }

        $services = [];

        foreach ($this->configuration['tags'][$name] as $serviceId) {
            $services[] = $this->get(substr($serviceId, 1));
        }

        return $services;
    }


    /**
     * @param string $scope
     * @return Keeper\AbstractKeeper
     * @throws BuildServiceException if scope is not found
     */
    protected function getBuilder($scope)
    {
        if (!isset($this->builders[$scope])) {
            throw new BuildServiceException(sprintf("Scope '%s' is not registered", $scope));
        }

        return $this->builders[$scope];
    }
}
