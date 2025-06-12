<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\item\command;

use JonasWindmann\CoreAPI\command\BaseCommand;
use JonasWindmann\CoreAPI\item\command\subcommand\CreateCustomItemSubCommand;
use JonasWindmann\CoreAPI\item\command\subcommand\GiveCustomItemSubCommand;
use JonasWindmann\CoreAPI\item\command\subcommand\ListCustomItemsSubCommand;
use JonasWindmann\CoreAPI\item\command\subcommand\RemoveCustomItemSubCommand;
use JonasWindmann\CoreAPI\item\command\subcommand\InfoCustomItemSubCommand;
use JonasWindmann\CoreAPI\item\command\subcommand\ExportCustomItemsSubCommand;
use JonasWindmann\CoreAPI\item\command\subcommand\ImportCustomItemsSubCommand;
use JonasWindmann\CoreAPI\item\command\subcommand\DebugCustomItemSubCommand;

/**
 * Main command for managing custom items in CoreAPI
 * Provides comprehensive custom item management with CLI interface
 */
class CustomItemCommand extends BaseCommand
{
    /**
     * CustomItemCommand constructor
     */
    public function __construct()
    {
        parent::__construct(
            "customitem",
            "Manage custom items in CoreAPI",
            "/customitem <create|give|list|remove|info|export|import|debug> [args...]",
            ["citem", "ci"],
            "coreapi.customitem.use"
        );

        // Register subcommands
        $this->registerSubCommands([
            new CreateCustomItemSubCommand(),
            new GiveCustomItemSubCommand(),
            new ListCustomItemsSubCommand(),
            new RemoveCustomItemSubCommand(),
            new InfoCustomItemSubCommand(),
            new ExportCustomItemsSubCommand(),
            new ImportCustomItemsSubCommand(),
            new DebugCustomItemSubCommand()
        ]);
    }
}
