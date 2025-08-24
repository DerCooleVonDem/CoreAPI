<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\npc\command;

use JonasWindmann\CoreAPI\command\BaseCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\npc\command\subcommand\ListNpcsSubCommand;
use JonasWindmann\CoreAPI\npc\command\subcommand\CreateNpcSubCommand;
use JonasWindmann\CoreAPI\npc\command\subcommand\RemoveNpcSubCommand;
use JonasWindmann\CoreAPI\npc\command\subcommand\InfoNpcSubCommand;
use JonasWindmann\CoreAPI\npc\command\subcommand\HowToUseSubCommand;
use JonasWindmann\CoreAPI\npc\command\subcommand\InfoNearSubCommand;
use JonasWindmann\CoreAPI\npc\command\subcommand\ListTypesSubCommand;

/**
 * Core NPC management command for CoreAPI
 * Provides comprehensive NPC management with both CLI and form interfaces
 */
class CoreNpcCommand extends BaseCommand {

    /**
     * CoreNpcCommand constructor
     */
    public function __construct() {
        parent::__construct(
            "corenpc", 
            "Manage NPCs in CoreAPI", 
            "/corenpc <list|create|remove|info|howtouse|infonear|listtypes> [args...]",
            ["cnpc", "npc"],
            "coreapi.npc.use"
        );

        // Register subcommands
        $this->registerSubCommands([
            new ListNpcsSubCommand(),
            new CreateNpcSubCommand(),
            new RemoveNpcSubCommand(),
            new InfoNpcSubCommand(),
            new HowToUseSubCommand(),
            new InfoNearSubCommand(),
            new ListTypesSubCommand()
        ]);
    }
}
