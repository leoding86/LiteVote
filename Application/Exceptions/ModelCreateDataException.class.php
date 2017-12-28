<?php namespace Exceptions;

class ModelCreateDataException extends \Exception
{
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, Code::MODEL_CREATE_DATA_EXCEPTION, $previous);
    }
}
