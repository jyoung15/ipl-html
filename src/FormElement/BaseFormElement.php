<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attribute;
use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use ipl\Stdlib\Messages;
use ipl\Stdlib\Contract\ValidatorInterface;
use InvalidArgumentException;

abstract class BaseFormElement extends BaseHtmlElement
{
    use Messages;

    /** @var string */
    protected $description;

    /** @var string */
    protected $label;

    /** @var string */
    protected $name;

    /** @var bool */
    protected $ignored = false;

    /** @var bool */
    protected $required = false;

    /** @var bool */
    protected $valid;

    /** @var ValidatorInterface[] */
    protected $validators = [];

    /** @var mixed */
    protected $value;

    // TODO: Validators, errors, errorMessages()

    /**
     * Link constructor.
     * @param $name
     * @param \ipl\Html\Attributes|array|null $attributes
     */
    public function __construct($name, $attributes = null)
    {
        if ($attributes !== null) {
            $this->addAttributes($attributes);
        }
        $this->setName($name);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function hasValue()
    {
        $value = $this->getValue();

        return $value !== null && $value !== '' && $value !== [];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        if ($value === '') {
            $this->value = null;
        } else {
            $this->value = $value;
        }
        $this->valid = null;

        return $this;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function setRequired($required = true)
    {
        $this->required = (bool) $required;

        return $this;
    }

    public function isIgnored()
    {
        return $this->ignored;
    }

    public function setIgnored($ignored = true)
    {
        $this->ignored = (bool) $ignored;

        return $this;
    }

    /**
     * @return Attribute|string
     */
    public function getNameAttribute()
    {
        return $this->getName();
    }

    /**
     * @return mixed
     */
    public function getValueAttribute()
    {
        return $this->getValue();
    }

    /**
     * @return null|Attribute
     */
    public function getRequiredAttribute()
    {
        if ($this->isRequired()) {
            return new Attribute('required', true);
        }

        return null;
    }

    /**
     * @return ValidatorInterface[]
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * @param array $validators
     */
    public function setValidators(array $validators)
    {
        $this->validators = [];
        $this->addValidators($validators);
    }

    /**
     * @param array $validators
     */
    public function addValidators(array $validators)
    {
        foreach ($validators as $name => $validator) {
            if ($validator instanceof ValidatorInterface) {
                $this->validators[] = $validator;
            } else {
                $validator = $this->createValidator($name, $validator);
                $this->validators[] = $validator;
            }
        }
    }

    /**
     * @param $name
     * @param $options
     * @return ValidatorInterface
     */
    public function createValidator($name, $options)
    {
        $class = 'ipl\\Validator\\' . ucfirst($name) . 'Validator';
        if (class_exists($class)) {
            return new $class($options);
        } else {
            throw new InvalidArgumentException(
                'Unable to create Validator: %s',
                $name
            );
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if ($this->valid === null) {
            $this->validate();
        }

        return $this->valid;
    }

    /**
     * @return bool
     */
    public function hasBeenValidatedAndIsNotValid()
    {
        return $this->valid !== null && ! $this->valid;
    }

    /**
     * @return $this
     */
    public function validate()
    {
        $valid = true;

        foreach ($this->getValidators() as $validator) {
            if (! $validator->isValid($this->getValue())) {
                $valid = false;
                foreach ($validator->getMessages() as $message) {
                    $this->addMessage($message);
                }
            }
        }

        $this->valid = $valid;

        return $this;
    }

    protected function registerValueCallback(Attributes $attributes)
    {
        $attributes->registerAttributeCallback(
            'value',
            [$this, 'getValueAttribute'],
            [$this, 'setValue']
        );
    }

    protected function registerAttributeCallbacks(Attributes $attributes)
    {
        $this->registerValueCallback($attributes);

        $attributes
            ->registerAttributeCallback('label', null, [$this, 'setLabel'])
            ->registerAttributeCallback('name', [$this, 'getNameAttribute'], [$this, 'setName'])
            ->registerAttributeCallback('description', null, [$this, 'setDescription'])
            ->registerAttributeCallback('validators', null, [$this, 'setValidators'])
            ->registerAttributeCallback('ignore', null, [$this, 'setIgnored'])
            ->registerAttributeCallback('required', [$this, 'getRequiredAttribute'], [$this, 'setRequired']);

        $this->registerCallbacks();
    }

    /** @deprecated Use {@link registerAttributeCallbacks()} instead */
    protected function registerCallbacks()
    {
    }
}
