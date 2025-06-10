<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\command\defaults;

use JonasWindmann\CoreAPI\command\BaseCommand;
use JonasWindmann\CoreAPI\command\SubCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Default help subcommand that displays all available subcommands
 */
class HelpSubCommand extends SubCommand {
    
    /** @var BaseCommand */
    private BaseCommand $parentCommand;
    
    /**
     * HelpSubCommand constructor
     * 
     * @param BaseCommand $parentCommand The parent command
     */
    public function __construct(BaseCommand $parentCommand) {
        parent::__construct(
            "help", 
            "Displays all available subcommands", 
            $parentCommand->getName() . " help", 
            0, 
            0
        );
        
        $this->parentCommand = $parentCommand;
    }
    
    /**
     * Execute the help subcommand
     * 
     * @param CommandSender $sender The command sender
     * @param array $args The command arguments
     */
    public function execute(CommandSender $sender, array $args): void {
        $subCommands = $this->parentCommand->getSubCommands();
        
        $sender->sendMessage(TextFormat::YELLOW . "Available subcommands:");
        
        foreach ($subCommands as $subCommand) {
            $sender->sendMessage($this->formatSubCommandHelp($subCommand));
        }
    }
    
    /**
     * Format a subcommand's help line
     * 
     * @param SubCommand $subCommand The subcommand
     * @return string The formatted help line
     */
    private function formatSubCommandHelp(SubCommand $subCommand): string {
        $aqua = TextFormat::AQUA;
        $white = TextFormat::WHITE;
        $gray = TextFormat::GRAY;
        
        return $aqua . $subCommand->getUsage() . $white . ": " . $gray . $subCommand->getDescription();
    }
}