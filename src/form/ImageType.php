<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\form;

/**
 * Enum for form button image types
 * Addresses UX issue 2.b - Confusing Image Type Parameters
 */
enum ImageType: string {
    case PATH = "path";
    case URL = "url";
}
