<?php namespace Exceptions;

class NotImplementedException extends \Exception
{
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(
            Message::string(Code::NOT_IMPLEMENTED_EXCEPTION),
            Code::NOT_IMPLEMENTED_EXCEPTION,
            $previous
        );
    }
}
