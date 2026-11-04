<?php

namespace App\Imports;

use RuntimeException;

/**
 * A row left out of a waitlist import because of data-quality warnings
 * (PRESTA_MIN code, patient age); it is returned in the rejected rows file.
 */
class RowRejectedException extends RuntimeException
{
    /**
     * @param string[] $reasons
     */
    public function __construct(public readonly array $reasons)
    {
        parent::__construct(implode(' | ', $reasons));
    }
}
