<?php

namespace Prokl\ArgumentResolversBundle\Services\ArgumentResolver;

use Prokl\ArgumentResolversBundle\Services\Supply\ResolveParamsFromContainer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Class ArrayRecursiveArgumentResolver
 *
 * @package Prokl\ArgumentResolversBundle\Services\ArgumentResolver
 *
 * @since 28.10.2020
 */
class ArrayRecursiveArgumentResolver implements ArgumentValueResolverInterface
{
    /**
     * @var ResolveParamsFromContainer $resolveParamsFromContainer Ресолвер параметров и сервисов из контейнера.
     */
    private $resolveParamsFromContainer;

    /**
     * ArrayRecursiveArgumentResolver constructor.
     *
     * @param ResolveParamsFromContainer $resolveParamsFromContainer Ресолвер параметров и сервисов из контейнера.
     */
    public function __construct(
        ResolveParamsFromContainer $resolveParamsFromContainer
    ) {
        $this->resolveParamsFromContainer = $resolveParamsFromContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === 'array' && $request->attributes->has($argument->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $values = $request->attributes->get($argument->getName());
        $result = $values;

        if (!$values && $argument->hasDefaultValue()) {
              $defaultValues = $argument->getDefaultValue();
              $result = $this->resolveParamsInArrayRecursively($defaultValues);
              $request->attributes->set($argument->getName(), $result);
        }

        yield $result;
    }

    /**
     * Массив со значениями по умолчанию обработать рекурсивно. Попутно разрешить
     * сервисы из контейнера. Но игнорить классы как параметры.
     *
     * @param array $array
     *
     * @return array
     *
     * @since 28.10.2020
     */
    private function resolveParamsInArrayRecursively(array $array) : array
    {
        $result = [];

        foreach ($array as $param => $argItem) {
            if (is_array($argItem)) {
                $result[$param] = $this->resolveParamsInArrayRecursively($argItem);
                continue;
            }

            if (is_string($argItem)) {
                // Ресолвинг всего чего можно из контейнера.
                $resolvedFromContainer = $this->resolveParamsFromContainer->resolve($argItem);
                $argItem = $resolvedFromContainer ?? $argItem;
            }

            $result[$param] = $argItem;
        }

        return $result;
    }
}
