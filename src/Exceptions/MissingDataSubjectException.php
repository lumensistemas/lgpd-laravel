<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Exceptions;

use LogicException;

/**
 * Thrown when a processing activity is recorded against a model
 * that is not linked to a DataSubject.
 */
class MissingDataSubjectException extends LogicException
{
    public function __construct()
    {
        parent::__construct('Cannot record a processing activity: model is not linked to a DataSubject.');
    }
}
