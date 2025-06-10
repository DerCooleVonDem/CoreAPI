<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\command;

use JonasWindmann\CoreAPI\command\defaults\HelpSubCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

abstract class BaseCommand extends Command {

    /** @var SubCommand[] */
    protected array $subCommands = [];

    /** @var bool */
    protected bool $autoRegisterHelpCommand;

    /**
     * BaseCommand constructor
     * 
     * @param string $name Command name
     * @param string $description Command description
     * @param string $usageMessage Usage message
     * @param array $aliases Command aliases
     * @param bool $autoRegisterHelpCommand Whether to automatically register the help subcommand
     */
    public function __construct(
        string $name, 
        string $description = "", 
        string $usageMessage = null, 
        array $aliases = [],
        string $permission = "",
        bool $autoRegisterHelpCommand = true
    ) {
        parent::__construct($name, $description, $usageMessage, $aliases);

        // Only set permission if it's not empty
        if ($permission !== "") {
            $this->setPermission($permission);
        }

        $this->autoRegisterHelpCommand = $autoRegisterHelpCommand;

        // Register the help subcommand if enabled
        if ($this->autoRegisterHelpCommand) {
            $this->registerHelpSubCommand();
        }
    }

    /**
     * Register the default help subcommand
     * This is called automatically if autoRegisterHelpCommand is true
     */
    protected function registerHelpSubCommand(): void {
        // Only register if no help subcommand is already registered
        if (!isset($this->subCommands["help"])) {
            $this->registerSubCommand(new HelpSubCommand($this));
        }
    }

    /**
     * Register a subcommand to this command
     * 
     * @param SubCommand $subCommand The subcommand to register
     */
    public function registerSubCommand(SubCommand $subCommand): void {
        $this->subCommands[$subCommand->getName()] = $subCommand;
    }

    /**
     * Register multiple subcommands at once
     * 
     * @param SubCommand[] $subCommands Array of subcommands to register
     */
    public function registerSubCommands(array $subCommands): void {
        foreach ($subCommands as $subCommand) {
            if ($subCommand instanceof SubCommand) {
                $this->registerSubCommand($subCommand);
            }
        }
    }

    /**
     * Get a subcommand by name
     * 
     * @param string $name The name of the subcommand
     * @return SubCommand|null The subcommand if found, null otherwise
     */
    public function getSubCommand(string $name): ?SubCommand {
        return $this->subCommands[$name] ?? null;
    }

    /**
     * Get all registered subcommands
     * 
     * @return SubCommand[]
     */
    public function getSubCommands(): array {
        return $this->subCommands;
    }

    /**
     * Execute the command
     * 
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            return false;
        }

        if (count($args) === 0) {
            // If help subcommand exists, execute it
            if (isset($this->subCommands["help"])) {
                $this->subCommands["help"]->execute($sender, []);
                return true;
            } else {
                // Fall back to showing usage message
                $sender->sendMessage(TextFormat::RED . $this->getUsage());
                return false;
            }
        }

        $subCommandName = strtolower($args[0]);
        $subCommandArgs = array_slice($args, 1);

        foreach ($this->subCommands as $name => $subCommand) {
            if ($name === $subCommandName) {
                if (
                    count($subCommandArgs) < $subCommand->getMinArgs() || 
                    (count($subCommandArgs) > $subCommand->getMaxArgs() && $subCommand->getMaxArgs() !== -1)
                ) {
                    $sender->sendMessage(TextFormat::RED . $subCommand->getUsage());
                    return false;
                }

                if ($subCommand->getPermission() !== "" && !$sender->hasPermission($subCommand->getPermission()) && !Server::getInstance()->isOp($sender->getName())) {
                    $sender->sendMessage(TextFormat::RED . "You don't have permission to use this subcommand.");
                    $sender->sendMessage(TextFormat::GRAY . "Required permission: " . TextFormat::YELLOW . $subCommand->getPermission());
                    return false;
                }

                $subCommand->execute($sender, $subCommandArgs);
                return true;
            }
        }

        // Provide helpful error message with suggestions
        $sender->sendMessage(TextFormat::RED . "Unknown subcommand: " . TextFormat::YELLOW . $subCommandName);
        $this->suggestSimilarCommands($sender, $subCommandName);
        $this->showAvailableCommands($sender);
        return false;
    }

    /**
     * Suggest similar commands based on string similarity
     *
     * @param CommandSender $sender
     * @param string $input
     */
    private function suggestSimilarCommands(CommandSender $sender, string $input): void {
        $suggestions = [];
        foreach ($this->subCommands as $name => $subCommand) {
            $similarity = 0;
            similar_text($input, $name, $similarity);
            if ($similarity > 60) { // 60% similarity threshold
                $suggestions[] = $name;
            }
        }

        if (!empty($suggestions)) {
            $sender->sendMessage(TextFormat::GRAY . "Did you mean: " . TextFormat::AQUA . implode(TextFormat::GRAY . ", " . TextFormat::AQUA, $suggestions) . TextFormat::GRAY . "?");
        }
    }

    /**
     * Show available commands to the user
     *
     * @param CommandSender $sender
     */
    private function showAvailableCommands(CommandSender $sender): void {
        if (empty($this->subCommands)) {
            return;
        }

        $availableCommands = [];
        foreach ($this->subCommands as $name => $subCommand) {
            // Only show commands the user has permission for
            if ($subCommand->getPermission() === "" || $sender->hasPermission($subCommand->getPermission())) {
                $availableCommands[] = $name;
            }
        }

        if (!empty($availableCommands)) {
            $sender->sendMessage(TextFormat::GRAY . "Available subcommands: " . TextFormat::AQUA . implode(TextFormat::GRAY . ", " . TextFormat::AQUA, $availableCommands));
            $sender->sendMessage(TextFormat::GRAY . "Use " . TextFormat::YELLOW . "/" . $this->getName() . " help" . TextFormat::GRAY . " for more information.");
        }
    }
}
