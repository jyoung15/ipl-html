<?php

namespace ipl\Html\FormElement;

class MultiInstanceElement extends BaseFormElement
{
    use FormElementContainer;

    protected $tag = 'div';

    protected $defaultAttributes = [
        'class' => 'ipl-subform'
    ];

    protected $instances = [];

    public function getValue($name = null)
    {
        if ($name === null) {
            return $this->getValues();
        } else {
            return $this->getElement($name)->getValue();
        }
    }

    public function getInstance($index)
    {
        if (! isset($this->instances[$index])) {
            $this->instances[$index] = clone(
                $this->prototype
            );
        }

        return $this->instances[$index];
    }

    public function setValue($value)
    {
        $this->populate($value);

        return $this;
    }

    public function isValid()
    {
        foreach ($this->getElements() as $element) {
            if (! $element->isValid) {
                return false;
            }
        }

        return true;
    }

    protected function registerValueCallback()
    {
        $this->getAttributes()->registerAttributeCallback(
            'value',
            null,
            [$this, 'setValue']
        );
    }

    public function __call()
    {
        $args = func_get_args();
        $method = array_shift($args);

        return call_user_func_array([$this, $method], $args);
    }
}
