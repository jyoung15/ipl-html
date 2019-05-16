<?php

namespace ipl\Html\FormElement;

class HiddenElement extends InputElement
{
    protected $type = 'hidden';

    protected $protected = false;

    protected function registerCallbacks()
    {
        parent::registerCallbacks();
        $this->getAttributes()->registerAttributeCallback(
            'protected',
            null,
            [$this, 'setProtected']
        );
    }

    public function setValue($value)
    {
        if ($this->value === null || ! $this->protected) {
            parent::setValue($value);
        }

        return $this;
    }

    public function isProtected()
    {
        return $this->protected;
    }

    public function setProtected($protected = true)
    {
        $this->protected = (bool) $protected;

        return $this;
    }
}
