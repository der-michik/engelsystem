<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Application;
use Engelsystem\Exceptions\Handler;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\ExceptionHandler;
use Engelsystem\Test\Unit\Middleware\Stub\ExceptionMiddlewareHandler;
use Engelsystem\Test\Unit\Middleware\Stub\ReturnResponseMiddlewareHandler;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExceptionHandlerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\ExceptionHandler::__construct
     * @covers \Engelsystem\Middleware\ExceptionHandler::process
     */
    public function testRegister()
    {
        /** @var MockObject|ContainerInterface $container */
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        /** @var MockObject|ResponseInterface $response */
        $response = $this->getMockBuilder(Response::class)->getMock();
        /** @var MockObject|Handler $errorHandler */
        $errorHandler = $this->getMockBuilder(Handler::class)->getMock();
        $returnResponseHandler = new ReturnResponseMiddlewareHandler($response);
        $throwExceptionHandler = new ExceptionMiddlewareHandler();

        Application::setInstance($container);

        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['error.handler'], ['psr7.response'])
            ->willReturnOnConsecutiveCalls($errorHandler, $response);

        $response->expects($this->once())
            ->method('withContent')
            ->willReturn($response);
        $response->expects($this->once())
            ->method('withStatus')
            ->with(500)
            ->willReturn($response);

        $handler = new ExceptionHandler($container);
        $return = $handler->process($request, $returnResponseHandler);
        $this->assertEquals($response, $return);

        $return = $handler->process($request, $throwExceptionHandler);
        $this->assertEquals($response, $return);
    }
}
