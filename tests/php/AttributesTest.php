<?php

namespace ipl\Tests\Html;

use Exception;
use RuntimeException;
use UnexpectedValueException;
use ipl\Html\Attribute;
use ipl\Html\Attributes;

class AttributesTest extends TestCase
{
    public function testGetterCallbackInGet()
    {
        $callback = function () {
            return new Attribute('callback', 'value from callback');
        };

        $attributes = (new Attributes())
            ->setCallback('callback', $callback);

        $this->assertSame($attributes->get('callback')->getValue(), 'value from callback');
    }

    public function testSetterCallbackInSet()
    {
        $element = new ElementWithCallbackAttributes();

        $attributes = $element->getAttributes();

        $attributes->set('name', 'name from test');

        $this->assertSame('name from test', $attributes->get('name')->getValue());
        $this->assertSame('name from test', $element->getName());
    }

    public function testSetterCallbackInAdd()
    {
        $element = new ElementWithCallbackAttributes();

        $attributes = $element->getAttributes();

        $attributes->add('name', 'name from test');

        $this->assertSame('name from test', $attributes->get('name')->getValue());
        $this->assertSame('name from test', $element->getName());
    }

    public function testSetterCallbackIsProxied()
    {
        $element = new ElementWithCallbackAttributes();

        $attributes = $element->getAttributes();

        $attributes->get('name')->setValue('name from test');

        $this->assertSame('name from test', $attributes->get('name')->getValue());
        $this->assertSame('name from test', $element->getName());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCantOverrideCallbacks()
    {
        $callback = function () {
            return new Attribute('callback', 'value from callback');
        };

        $attributes = (new Attributes())
            ->setCallback('callback', $callback);

        $attributes->set('callback', 'overridden');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetterCallbackRuntimeException()
    {
        $callback = function () {
            throw new Exception();
        };

        $attributes = (new Attributes())
            ->setCallback('callback', $callback);

        $attributes->get('callback');
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testGetterCallbackValueException()
    {
        $callback = function () {
            return [];
        };

        $attributes = (new Attributes())
            ->setCallback('callback', $callback);

        $attributes->get('callback');
    }
}
