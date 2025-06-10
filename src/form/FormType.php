<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\form;

/**
 * Enum for form types
 * Used to specify the type of form to create
 */
enum FormType {
    case MODAL;
    case SIMPLE;
    case CUSTOM;
}