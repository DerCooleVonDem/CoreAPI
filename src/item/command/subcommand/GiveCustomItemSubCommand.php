<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\item\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

/**
 * Subcommand for giving custom items to players
 */
class GiveCustomItemSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "give",
            "Give a custom item to a player",
            "/customitem give <player> <id> [count]",
            2,
            3,
            "coreapi.customitem.give"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $playerName = $args[0];
        $itemId = $args[1];
        $count = isset($args[2]) ? (int) $args[2] : 1;

        if ($count <= 0) {
            $sender->sendMessage("§cCount must be greater than 0!");
            return;
        }

        // Find the target player
        $targetPlayer = Server::getInstance()->getPlayerByPrefix($playerName);
        if ($targetPlayer === null) {
            $sender->sendMessage("§cPlayer '$playerName' not found!");
            return;
        }

        $customItemManager = CoreAPI::getInstance()->getCustomItemManager();

        // Check if custom item exists
        $customItem = $customItemManager->getCustomItem($itemId);
        if ($customItem === null) {
            $sender->sendMessage("§cCustom item with ID '$itemId' not found!");
            return;
        }

        // Give the item
        if ($customItemManager->giveCustomItem($targetPlayer, $itemId, $count)) {
            $sender->sendMessage("§aGave {$count}x {$customItem->getName()} to {$targetPlayer->getName()}!");
            $targetPlayer->sendMessage("§aYou received {$count}x {$customItem->getName()}!");
        } else {
            $sender->sendMessage("§cFailed to give item. Player's inventory might be full.");
        }
    }
}
