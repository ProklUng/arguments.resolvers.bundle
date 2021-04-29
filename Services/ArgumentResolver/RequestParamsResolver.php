<?php

namespace Prokl\ArgumentResolversBundle\Services\ArgumentResolver;

use Prokl\ArgumentResolversBundle\Services\Interfaces\RequestQueryInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Class RequestParamsResolver
 * Gets the request query parameter of the same name as the argument name.
 * @package Prokl\ArgumentResolversBundle\Services\ArgumentResolver
 *
 * @since 01.12.2020
 */
class RequestParamsResolver implements ArgumentValueResolverInterface
{
    /** @var string MARKABLE_INTERFACE Интерфейс по умолчанию. */
    private const MARKABLE_INTERFACE = RequestQueryInterface::class;

    /**
     * @var string $markableInterface Интерфейс, помечающий контроллер - надо пытаться
     * преобразовывать параметры.
     */
    private $markableInterface;

    /**
     * RequestParamsResolver constructor.
     *
     * @param string $markableInterface Markable interface.
     */
    public function __construct(string $markableInterface = self::MARKABLE_INTERFACE)
    {
        $this->markableInterface = $markableInterface;
    }

    /**
    * {@inheritdoc}
    */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        // Проверить на конфликт параметра с атрибутами контроллера.
        // Если атрибут есть, то контроллер не подлежит обработке.
        if ($request->attributes->get($argument->getName())) {
            return false;
        }

        /**
         * @var string|object $controller
         * @psalm-var class-string|object $controller
         */
        $controller = $request->attributes->get('_controller');

        if (is_string($controller)) {
            if (strpos($controller, '::') !== false) {
                $controller = explode('::', $controller, 2);
            } else {
                // Invoked controller.
                try {
                    new ReflectionMethod($controller, '__invoke');
                    $controller = [$controller, '__invoke'];
                } catch (ReflectionException $e) {
                    return false;
                }
            }
        }

        if (is_array($controller)
            &&
            $this->isImplementedMarkableInterface($controller[0])) {
            return true;
        }

        // Invokable controller.
        if (is_object($controller)) {
            return $this->isImplementedMarkableInterface($controller);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $data = $this->getDataRequest($request);

        $getParam = $data[$argument->getName()];

        if ($getParam) {
            yield $getParam;
        }

        yield $request;
    }

    /**
     * Данные запроса в зависимости от типа (GET, POST).
     *
     * @param Request $request Request.
     *
     * @return array
     */
    private function getDataRequest(Request $request) : array
    {
        // Тип запроса.
        $typeRequest = $request->getMethod();

        return ($typeRequest !== 'GET') ?
            $request->request->all()
            :
            $request->query->all();
    }

    /**
     * Контроллер помечен ли интерфейсом для обработки.
     *
     * @param object|string|array $controller Контроллер с методом.
     *
     * @return boolean
     */
    private function isImplementedMarkableInterface($controller) : bool
    {
        if (is_string($controller) && !class_exists($controller)) {
            return false;
        }

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        try {
            /** @psalm-var object $class */
            $class = new ReflectionClass($controller);

            return $class->implementsInterface($this->markableInterface);
        } catch (ReflectionException $e) {
        }

        return false;
    }
}
