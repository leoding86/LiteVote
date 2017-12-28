<?php namespace Exceptions;

class ModelCURDException extends CriticalException
{
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, Code::MODEL_CURD_EXCEPTION, $previous);
    }
}
