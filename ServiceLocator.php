<?php

declare(strict_types=1);

namespace FpDbTest;

use Exception;

class ServiceLocator
{
    private array $loaded = [];

    public function __construct(
        private array $config,
        private array $afterCreateServiceHooks
    ) {
    }

    /**
     * @template T
     * @param class-string<T> $serviceName
     * @return T
     * @throws Exception
     */
    public function get(string $serviceName): object
    {
        if ($this->loaded[$serviceName] ?? null) {
            return $this->loaded[$serviceName];
        }

        $this->loaded[$serviceName] = $this->newInstance($serviceName);

        if (is_callable($this->afterCreateServiceHooks[$serviceName] ?? null)) {
            $this->afterCreateServiceHooks[$serviceName]($this);
        }

        return $this->loaded[$serviceName];
    }

    /**
     * @template T
     * @param class-string<T> $serviceName
     * @return T
     * @throws Exception
     */
    private function newInstance(string $serviceName): object
    {
        if (!is_callable($this->config[$serviceName] ?? null)) {
            throw new Exception(
                sprintf(
                    'Service declaration not found for %s',
                    $serviceName
                )
            );
        }

        return $this->config[$serviceName]($this);
    }
}
