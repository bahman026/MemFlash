<?php

declare(strict_types=1);

namespace App\Services\GoogleTranslate;

use RuntimeException;

/**
 * The sheet could not be read. The message is written for the user: it is
 * shown on the dashboard as it is.
 */
final class SheetUnavailable extends RuntimeException {}
