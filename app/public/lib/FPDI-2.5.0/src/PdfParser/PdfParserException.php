<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2023 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\Fpdi\PdfParser;

use setasign\Fpdi\FpdiException;

/**
 * Exception for the pdf parser class
 */
class PdfParserException extends FpdiException
{
    /**
     * @var int
     */
    public const NOT_IMPLEMENTED = 0x0001;

    /**
     * @var int
     */
    public const IMPLEMENTED_IN_FPDI_PDF_PARSER = 0x0002;

    /**
     * @var int
     */
    public const INVALID_DATA_TYPE = 0x0003;

    /**
     * @var int
     */
    public const FILE_HEADER_NOT_FOUND = 0x0004;

    /**
     * @var int
     */
    public const PDF_VERSION_NOT_FOUND = 0x0005;

    /**
     * @var int
     */
    public const INVALID_DATA_SIZE = 0x0006;
}
