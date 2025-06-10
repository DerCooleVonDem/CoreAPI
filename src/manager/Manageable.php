<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\manager;

/**
 * Interface for objects that can be managed by a Manager
 */
interface Manageable {
    /**
     * Get the unique identifier for this object
     * 
     * @return string|int The unique identifier
     */
    public function getId(): string|int;
}