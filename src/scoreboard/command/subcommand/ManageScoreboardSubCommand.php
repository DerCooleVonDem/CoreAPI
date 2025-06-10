<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\scoreboard\form\ScoreboardManagementForm;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

/**
 * Subcommand to open form-based scoreboard management
 */
class ManageScoreboardSubCommand extends SubCommand {

    public function __construct() {
        parent::__construct(
            "manage",
            "Open form-based scoreboard management",
            "/coresb manage",
            0,
            0,
            "coreapi.scoreboard.manage"
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used by players.");
            $sender->sendMessage(TextFormat::GRAY . "Use " . TextFormat::YELLOW . "/coresb list" . TextFormat::GRAY . " to see available scoreboards from console.");
            return;
        }

        // Open the scoreboard management form
        $form = new ScoreboardManagementForm();
        $formManager = CoreAPI::getInstance()->getFormManager();
        
        $form->sendTo($sender);
        $sender->sendMessage(TextFormat::GREEN . "§l§6CoreAPI §r§7» §aOpening scoreboard management...");
    }
}
