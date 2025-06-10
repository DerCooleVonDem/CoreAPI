<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

/**
 * Subcommand to show a specific scoreboard
 */
class ShowScoreboardSubCommand extends SubCommand {

    public function __construct() {
        parent::__construct(
            "show",
            "Display a specific scoreboard",
            "/coresb show <scoreboard_id>",
            1,
            1,
            "coreapi.scoreboard.show"
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used by players.");
            return;
        }

        $scoreboardId = $args[0];
        $scoreboardManager = CoreAPI::getInstance()->getScoreboardManager();
        
        // Check if the scoreboard exists
        $scoreboard = $scoreboardManager->getScoreboard($scoreboardId);
        if ($scoreboard === null) {
            $sender->sendMessage(TextFormat::RED . "Scoreboard '" . $scoreboardId . "' not found.");
            $sender->sendMessage(TextFormat::GRAY . "Use " . TextFormat::YELLOW . "/coresb list" . TextFormat::GRAY . " to see available scoreboards.");
            return;
        }

        // Display the scoreboard
        $success = $scoreboardManager->displayScoreboard($sender, $scoreboardId);
        
        if ($success) {
            $sender->sendMessage(TextFormat::GREEN . "§l§6CoreAPI §r§7» §aNow displaying: " . TextFormat::RESET . $scoreboard->getTitle());
            $sender->sendMessage(TextFormat::GRAY . "Owner: " . TextFormat::AQUA . $scoreboard->getOwnerPlugin());
            
            if ($scoreboard->isAutoUpdate()) {
                $interval = $scoreboard->getUpdateInterval();
                $seconds = round($interval / 20, 1);
                $sender->sendMessage(TextFormat::GRAY . "Updates every " . TextFormat::YELLOW . $seconds . " seconds");
            }
        } else {
            $sender->sendMessage(TextFormat::RED . "Failed to display scoreboard. Make sure you have a valid session.");
        }
    }
}
