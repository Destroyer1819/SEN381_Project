<?php

namespace App\Exceptions;

use RuntimeException;

// The request or offer changed between page load and submit, or the decline limit refused the action (D-018).
class OfferUnavailableException extends RuntimeException {}
