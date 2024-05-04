<?php

namespace App\Validators;

use Illuminate\Validation\Validator as IlluminateValidator;

class CustomValidator extends IlluminateValidator
{
    /**
     * Carga las reglas de mensajes personalizados.
     *
     * @return void
     */
    protected function loadCustomMessages()
    {
        $this->setCustomMessages(config('validation_messages'));
    }
}
