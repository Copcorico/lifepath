<?php

namespace exceptions;

class RouteurNotFoundException extends \Exception 
{
    protected $message = 'Cette route n\'existe pas.';
    
}