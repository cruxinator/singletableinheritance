<?php


namespace Cruxinator\SingleTableInheritance\Exceptions;


class InvalidAttributesException extends SingleTableInheritanceException
{
    protected array $invalidAttributes;

    public function __construct($message, array $invalidAttributes)
    {
        parent::__construct($message . 'The attributes: ' . implode(',', $invalidAttributes) . ' are invalid.');
        $this->invalidAttributes = $invalidAttributes;
    }

    public function getInvalidAttributes(): array
    {
        return $this->invalidAttributes;
    }
}