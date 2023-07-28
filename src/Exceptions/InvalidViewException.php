<?php

namespace BNETDocs\Exceptions;

/**
 * InvalidViewException extends InvalidArgumentException, a subclass of LogicException.
 * It is used when a View is provided to a Router that cannot be used.
 * A custom message is set based on the provided View.
 */
class InvalidViewException extends \InvalidArgumentException
{
    private string $view_name = '';

    public function __construct(\BNETDocs\Interfaces\View|string $view, int $errno = 0, ?\Throwable $previous = null)
    {
        $this->view_name = \is_string($view) ? $view : \get_class($view);
        parent::__construct(
            \sprintf('Invalid View (%s)', $this->view_name),
            $errno,
            $previous
        );
    }

    public function getViewName(): string
    {
        return $this->view_name;
    }
}
