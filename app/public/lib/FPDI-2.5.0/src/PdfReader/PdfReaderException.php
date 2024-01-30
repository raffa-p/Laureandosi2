<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2023 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\Fpdi\PdfReader;

use setasign\Fpdi\FpdiException;

/**
 * Exception for the pdf reader class
 */
class PdfReaderException extends FpdiException
{
    /**
     * @var int
     */
    public const KIDS_EMPTY = 0x0101;

    /**
     * @var int
     */
    public const UNEXPECTED_DATA_TYPE = 0x0102;

    /**
     * @var int
     */
    public const MISSING_DATA = 0x0103;
}
