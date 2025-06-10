<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\form;

/**
 * Enum for predefined button types
 * Used to specify the type of predefined button to add to a form
 */
enum ButtonType {
    case BACK;
    case CLOSE;
    case OPEN_FORM;
    case HOME;
    case REFRESH;
    case SETTINGS;
    case CONFIRM;
    case CANCEL;
}