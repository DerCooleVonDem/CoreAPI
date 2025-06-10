<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\command;

use pocketmine\command\Command;
use pocketmine\command\CommandMap;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

class CommandManager {

    /** @var Command[] */
    private array $commands = [];
    
    /** @var Plugin */
    private Plugin $plugin;
    
    /** @var CommandMap */
    private CommandMap $commandMap;

    /**
     * CommandManager constructor
     * 
     * @param Plugin $plugin The plugin instance
     */
    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
        $this->commandMap = Server::getInstance()->getCommandMap();
    }

    /**
     * Register a command to the server
     * 
     * @param Command $command The command to register
     */
    public function registerCommand(Command $command): void {
        $this->commands[$command->getName()] = $command;
        $this->commandMap->register($this->plugin->getName(), $command);
    }

    /**
     * Register multiple commands at once
     * 
     * @param Command[] $commands Array of commands to register
     */
    public function registerCommands(array $commands): void {
        foreach ($commands as $command) {
            if ($command instanceof Command) {
                $this->registerCommand($command);
            }
        }
    }

    /**
     * Unregister a command from the server
     * 
     * @param string $commandName The name of the command to unregister
     */
    public function unregisterCommand(string $commandName): void {
        if (isset($this->commands[$commandName])) {
            $this->commandMap->unregister($this->commands[$commandName]);
            unset($this->commands[$commandName]);
        }
    }

    /**
     * Get a command by name
     * 
     * @param string $commandName The name of the command
     * @return Command|null The command if found, null otherwise
     */
    public function getCommand(string $commandName): ?Command {
        return $this->commands[$commandName] ?? null;
    }

    /**
     * Get all registered commands
     * 
     * @return Command[]
     */
    public function getCommands(): array {
        return $this->commands;
    }

    /**
     * Get the plugin instance
     * 
     * @return Plugin
     */
    public function getPlugin(): Plugin {
        return $this->plugin;
    }
}