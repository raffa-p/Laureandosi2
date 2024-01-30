<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2023 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\Fpdi\PdfParser\CrossReference;

use setasign\Fpdi\PdfParser\PdfParserException;

/**
 * Exception used by the CrossReference and Reader classes.
 */
class CrossReferenceException extends PdfParserException
{
    /**
     * @var int
     */
    public const INVALID_DATA = 0x0101;

    /**
     * @var int
     */
    public const XREF_MISSING = 0x0102;

    /**
     * @var int
     */
    public const ENTRIES_TOO_LARGE = 0x0103;

    /**
     * @var int
     */
    public const ENTRIES_TOO_SHORT = 0x0104;

    /**
     * @var int
     */
    public const NO_ENTRIES = 0x0105;

    /**
     * @var int
     */
    public const NO_TRAILER_FOUND = 0x0106;

    /**
     * @var int
     */
    public const NO_STARTXREF_FOUND = 0x0107;

    /**
     * @var int
     */
    public const NO_XREF_FOUND = 0x0108;

    /**
     * @var int
     */
    public const UNEXPECTED_END = 0x0109;

    /**
     * @var int
     */
    public const OBJECT_NOT_FOUND = 0x010A;

    /**
     * @var int
     */
    public const COMPRESSED_XREF = 0x010B;

    /**
     * @var int
     */
    public const ENCRYPTED = 0x010C;
}
