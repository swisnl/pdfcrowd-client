<?php

namespace Swis\PdfcrowdClient\Tests\Unit;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $spy
     * @param string                                             $option
     * @param mixed                                              $value
     *
     * @internal param string $string
     */
    protected function assertPostBodyIncludes($spy, string $option, $value)
    {
        /** @var \PHPUnit_Framework_MockObject_Invocation_Object $invocation */
        foreach ($spy->getInvocations() as $invocation) {

            if ($invocation->getMethodName() !== 'setBody') {
                continue;
            }

            $parameters = $invocation->getParameters();

            $this->assertArrayHasKey($option, $parameters[0], 'String '.$option.' not found in POST body');
            $this->assertEquals($parameters[0][$option], $value);
            return;
        }

        $this->fail('No setBody call found among spy\'s invocations');
    }

    /**
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $spy
     * @param string                                             $option
     *
     * @internal param string $string
     */
    protected function assertPostBodyDoesNotInclude($spy, string $option)
    {
        /** @var \PHPUnit_Framework_MockObject_Invocation_Object $invocation */
        foreach ($spy->getInvocations() as $invocation) {
            if ($invocation->getMethodName() !== 'setBody') {
                continue;
            }

            $parameters = $invocation->getParameters();

            $this->assertArrayNotHasKey($option, $parameters[0], 'String '.$option.' found in POST body, but it shouldn\'t be');
            return;
        }

        $this->fail('No setBody call found among spy\'s invocations');
    }
}
