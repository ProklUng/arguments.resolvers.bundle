<?php

namespace Prokl\ArgumentResolversBundle\Tests\Cases;

use Prokl\ArgumentResolversBundle\Services\ArgumentResolver\RequestParamsResolver;
use Prokl\ArgumentResolversBundle\Services\Interfaces\RequestQueryInterface;
use Prokl\ArgumentResolversBundle\Tests\Fixtures\QueryController;
use Prokl\TestingTools\Base\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Class RequestQueryResolverTest
 * @package Tests\Resolvers
 * @coversDefaultClass RequestParamsResolver
 *
 * @since 02.12.2020
 */
class RequestQueryResolverTest extends BaseTestCase
{
    /**
     * @var RequestParamsResolver $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obTestObject = new RequestParamsResolver();
    }

    /**
    * support(). Контроллер пришел строкой.
    *
    * @return void
    */
    public function testSupportControllerString() : void
    {
        $result = $this->obTestObject->supports(
            $this->getRequest(),
            $this->getMetadata('param')
        );

        $this->assertTrue(
            $result,
            'Класс с RequestQueryInterface не прошел проверку ресолвером.'
        );
    }

    /**
     * support(). Контроллер пришел объектом.
     *
     * @return void
     */
    public function testSupportControllerObject() : void
    {
        $result = $this->obTestObject->supports(
            $this->getRequest([new QueryController(), 'action']),
            $this->getMetadata('param')
        );

        $this->assertTrue(
            $result,
            'Класс с RequestQueryInterface не прошел проверку ресолвером.'
        );
    }

    /**
     * support(). Конфликт с аттрибутом.
     *
     * @return void
     */
    public function testSupportControllerConflictWithAttributes() : void
    {
        $request = $this->getRequest([new QueryController(), 'action']);
        $request->attributes->set(
            'param', 3
        );

        $result = $this->obTestObject->supports(
            $request,
            $this->getMetadata('param')
        );

        $this->assertFalse(
            $result,
            'Класс с RequestQueryInterface, но с атрибутом почему-то прошел проверку ресолвером.'
        );
    }

    /**
     * support(). Не подлежащий обработке контроллер.
     *
     * @return void
     */
    public function testUnsupportController() : void
    {
        $result = $this->obTestObject->supports(
            $this->getRequest([new class extends AbstractController {}, 'action']),
            $this->getMetadata('param')
        );

        $this->assertFalse(
            $result,
            'Не подлежащий обработке контроллер прошел проверку ресолвером.'
        );
    }

    /**
     * support(). Invoke.
     *
     * @return void
     */
    public function testInvokeController() : void
    {
        $class = new class extends AbstractController implements RequestQueryInterface {
            public function __invoke (Request $request, int $param)
            {
                return new Response('OK');
            }
        };

        $result = $this->obTestObject->supports(
            $this->getRequest(get_class($class)),
            $this->getMetadata('param')
        );

        $this->assertTrue(
            $result,
            'Подлежащий обработке invoke контроллер прошел проверку ресолвером.'
        );
    }

    /**
     * support(). Invoke.
     *
     * @return void
     */
    public function testInvokeObjectController() : void
    {
        $class = new class extends AbstractController implements RequestQueryInterface {
            public function __invoke (Request $request, int $param)
            {
                return new Response('OK');
            }
        };

        $result = $this->obTestObject->supports(
            $this->getRequest($class),
            $this->getMetadata('param')
        );

        $this->assertTrue(
            $result,
            'Подлежащий обработке invoke контроллер прошел проверку ресолвером.'
        );
    }

    /**
     * resolve().
     */
    public function testResolveValid() : void
    {
        $testValue = 123;

        $request = $this->getRequest();
        $request->query->add(['param' => $testValue]);

        $result = $this->obTestObject->resolve(
            $request,
            $this->getMetadata('param')
        );

        foreach ($result as $item) {
            if (!is_object($item)) {
                $this->assertSame(
                    $testValue,
                    $item,
                    'Значение не обработано ресолвером.'
                );
            }
        }
    }

    /**
     * Получить Request.
     *
     * @param mixed $controller Контроллер.
     *
     * @return Request
     */
    private function getRequest($controller = 'Prokl\ArgumentResolversBundle\Tests\Fixtures\QueryController::action') : Request
    {
        $request = new Request();
        $request->attributes->set(
          '_controller',
            $controller
        );

        return $request;
    }

    /**
     * Получить ArgumentMetadata.
     *
     * @param string $name Название параметра.
     *
     * @return ArgumentMetadata
     */
    private function getMetadata(string $name) : ArgumentMetadata
    {
        return new ArgumentMetadata(
          $name,
          null,
          false,
          false,
          false
        );
    }
}
