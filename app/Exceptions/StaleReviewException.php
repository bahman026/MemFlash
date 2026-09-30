<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * A queued offline review that is older than the card's current state, so
 * replaying it would rewind the card's schedule.
 */
class StaleReviewException extends RuntimeException {}
