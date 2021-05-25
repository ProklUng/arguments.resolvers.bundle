<?php

namespace Prokl\ArgumentResolversBundle\Tests\Fixtures;

use Prokl\ArgumentResolversBundle\Services\Interfaces\RequestQueryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class QueryController
 * Пример контроллера, помеченного RequestQueryInterface - по возможности
 * преобразовывать GET параметры в агрументы контроллера.
 * @package Prokl\ArgumentResolversBundle\Tests\Fixtures
 *
 * @since 30.11.2020
 */
class QueryController extends AbstractController implements RequestQueryInterface
{
    public function action(Request $request, int $param): Response
    {
        return new Response('OK');
    }
}
