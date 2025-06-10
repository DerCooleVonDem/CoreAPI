<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\command;

use JonasWindmann\CoreAPI\command\BaseCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\scoreboard\command\subcommand\ListScoreboardsSubCommand;
use JonasWindmann\CoreAPI\scoreboard\command\subcommand\ShowScoreboardSubCommand;
use JonasWindmann\CoreAPI\scoreboard\command\subcommand\HideScoreboardSubCommand;
use JonasWindmann\CoreAPI\scoreboard\command\subcommand\ManageScoreboardSubCommand;
use JonasWindmann\CoreAPI\scoreboard\command\subcommand\InfoScoreboardSubCommand;

/**
 * Core scoreboard management command for CoreAPI
 * Provides comprehensive scoreboard management with both CLI and form interfaces
 */
class CoreScoreboardCommand extends BaseCommand {

    /**
     * CoreScoreboardCommand constructor
     */
    public function __construct() {
        parent::__construct(
            "coresb", 
            "Manage scoreboards in CoreAPI", 
            "/coresb <list|show|hide|manage|info> [args...]",
            ["csb", "scoreboard"],
            "coreapi.scoreboard.use"
        );
        
        // Register subcommands
        $this->registerSubCommands([
            new ListScoreboardsSubCommand(),
            new ShowScoreboardSubCommand(),
            new HideScoreboardSubCommand(),
            new ManageScoreboardSubCommand(),
            new InfoScoreboardSubCommand()
        ]);
    }
}
