<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\command;

use pocketmine\command\CommandSender;

/**
 * Fluent builder for creating subcommands
 * Addresses UX issue 1.c - Complex Constructor Parameters
 */
class SubCommandBuilder {
    private string $name;
    private string $description = "";
    private string $usage = "";
    private int $minArgs = 0;
    private int $maxArgs = -1; // -1 means unlimited
    private string $permission = "";
    private ?\Closure $executor = null;

    /**
     * Create a new subcommand builder
     * 
     * @param string $name The subcommand name
     */
    public function __construct(string $name) {
        $this->name = $name;
    }

    /**
     * Set the subcommand description
     * 
     * @param string $description
     * @return $this
     */
    public function description(string $description): self {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the subcommand usage
     * 
     * @param string $usage
     * @return $this
     */
    public function usage(string $usage): self {
        $this->usage = $usage;
        return $this;
    }

    /**
     * Set the minimum number of arguments
     * 
     * @param int $minArgs
     * @return $this
     */
    public function minArgs(int $minArgs): self {
        $this->minArgs = $minArgs;
        return $this;
    }

    /**
     * Set the maximum number of arguments
     * 
     * @param int $maxArgs Use -1 for unlimited
     * @return $this
     */
    public function maxArgs(int $maxArgs): self {
        $this->maxArgs = $maxArgs;
        return $this;
    }

    /**
     * Set both min and max arguments
     * 
     * @param int $minArgs
     * @param int $maxArgs Use -1 for unlimited
     * @return $this
     */
    public function args(int $minArgs, int $maxArgs = -1): self {
        $this->minArgs = $minArgs;
        $this->maxArgs = $maxArgs;
        return $this;
    }

    /**
     * Set exactly the number of arguments required
     * 
     * @param int $exactArgs
     * @return $this
     */
    public function exactArgs(int $exactArgs): self {
        $this->minArgs = $exactArgs;
        $this->maxArgs = $exactArgs;
        return $this;
    }

    /**
     * Set the permission required for this subcommand
     * 
     * @param string $permission
     * @return $this
     */
    public function permission(string $permission): self {
        $this->permission = $permission;
        return $this;
    }

    /**
     * Set the executor function for this subcommand
     * 
     * @param \Closure $executor Function that takes (CommandSender $sender, array $args): void
     * @return $this
     */
    public function executes(\Closure $executor): self {
        $this->executor = $executor;
        return $this;
    }

    /**
     * Build the subcommand
     * 
     * @return SubCommand
     */
    public function build(): SubCommand {
        if ($this->executor === null) {
            throw new \InvalidArgumentException("SubCommand must have an executor function. Use executes() method.");
        }

        return new class($this->name, $this->description, $this->usage, $this->minArgs, $this->maxArgs, $this->permission, $this->executor) extends SubCommand {
            private \Closure $executor;

            public function __construct(string $name, string $description, string $usage, int $minArgs, int $maxArgs, string $permission, \Closure $executor) {
                parent::__construct($name, $description, $usage, $minArgs, $maxArgs, $permission);
                $this->executor = $executor;
            }

            public function execute(CommandSender $sender, array $args): void {
                ($this->executor)($sender, $args);
            }
        };
    }

    /**
     * Static factory method for fluent interface
     * 
     * @param string $name
     * @return SubCommandBuilder
     */
    public static function create(string $name): self {
        return new self($name);
    }
}
