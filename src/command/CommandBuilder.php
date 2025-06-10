<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\command;

use JonasWindmann\CoreAPI\CoreAPI;

/**
 * Fluent builder for creating commands with subcommands
 * Addresses UX issue 1.b - Inconsistent Command Registration
 */
class CommandBuilder {
    private string $name;
    private string $description = "";
    private string $usage = "";
    private array $aliases = [];
    private string $permission = "";
    private bool $autoRegisterHelp = true;
    private array $subCommands = [];

    /**
     * Create a new command builder
     * 
     * @param string $name The command name
     */
    public function __construct(string $name) {
        $this->name = $name;
        $this->usage = "/" . $name . " <subcommand>";
    }

    /**
     * Set the command description
     * 
     * @param string $description
     * @return $this
     */
    public function description(string $description): self {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the command usage
     * 
     * @param string $usage
     * @return $this
     */
    public function usage(string $usage): self {
        $this->usage = $usage;
        return $this;
    }

    /**
     * Add command aliases
     * 
     * @param string ...$aliases
     * @return $this
     */
    public function aliases(string ...$aliases): self {
        $this->aliases = array_merge($this->aliases, $aliases);
        return $this;
    }

    /**
     * Set the command permission
     * 
     * @param string $permission
     * @return $this
     */
    public function permission(string $permission): self {
        $this->permission = $permission;
        return $this;
    }

    /**
     * Disable automatic help subcommand registration
     * 
     * @return $this
     */
    public function disableAutoHelp(): self {
        $this->autoRegisterHelp = false;
        return $this;
    }

    /**
     * Add a subcommand
     * 
     * @param SubCommand $subCommand
     * @return $this
     */
    public function subCommand(SubCommand $subCommand): self {
        $this->subCommands[] = $subCommand;
        return $this;
    }

    /**
     * Add multiple subcommands
     * 
     * @param SubCommand ...$subCommands
     * @return $this
     */
    public function subCommands(SubCommand ...$subCommands): self {
        $this->subCommands = array_merge($this->subCommands, $subCommands);
        return $this;
    }

    /**
     * Build and register the command
     * 
     * @return BaseCommand The created command
     */
    public function build(): BaseCommand {
        $command = new class($this->name, $this->description, $this->usage, $this->aliases, $this->permission, $this->autoRegisterHelp) extends BaseCommand {
            // Anonymous class to create the command
        };

        // Register subcommands
        foreach ($this->subCommands as $subCommand) {
            $command->registerSubCommand($subCommand);
        }

        // Auto-register with CoreAPI
        CoreAPI::getInstance()->getCommandManager()->registerCommand($command);

        return $command;
    }

    /**
     * Static factory method for fluent interface
     * 
     * @param string $name
     * @return CommandBuilder
     */
    public static function create(string $name): self {
        return new self($name);
    }
}
