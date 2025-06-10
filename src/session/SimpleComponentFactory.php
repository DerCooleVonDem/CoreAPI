<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\session;

/**
 * Simple component factory that uses a closure to create components
 * Addresses UX issue 3.a - Component Cloning Confusion
 */
class SimpleComponentFactory implements ComponentFactory {
    private string $id;
    private \Closure $factory;

    /**
     * SimpleComponentFactory constructor
     * 
     * @param string $id The component ID
     * @param \Closure $factory Factory function that returns PlayerSessionComponent
     */
    public function __construct(string $id, \Closure $factory) {
        $this->id = $id;
        $this->factory = $factory;
    }

    /**
     * Create a new instance of the component
     * 
     * @return PlayerSessionComponent
     */
    public function create(): PlayerSessionComponent {
        return ($this->factory)();
    }

    /**
     * Get the component ID
     *
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * Static factory method for creating component factories
     *
     * @param string $id
     * @param \Closure $factory
     * @return SimpleComponentFactory
     */
    public static function createFactory(string $id, \Closure $factory): self {
        return new self($id, $factory);
    }

    /**
     * Create a factory for a component class
     * 
     * @param string $componentClass
     * @return SimpleComponentFactory
     */
    public static function forClass(string $componentClass): self {
        if (!is_subclass_of($componentClass, PlayerSessionComponent::class)) {
            throw new \InvalidArgumentException("Component class must implement PlayerSessionComponent");
        }

        // Create a temporary instance to get the ID
        $tempInstance = new $componentClass();
        $id = $tempInstance->getId();

        return new self($id, function() use ($componentClass) {
            return new $componentClass();
        });
    }
}
