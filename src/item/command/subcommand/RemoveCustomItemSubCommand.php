<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\item\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;

/**
 * Subcommand for removing custom items
 */
class RemoveCustomItemSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "remove",
            "Remove a custom item",
            "/customitem remove <id>",
            1,
            1,
            "coreapi.customitem.remove"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $itemId = $args[0];
        $customItemManager = CoreAPI::getInstance()->getCustomItemManager();

        // Check if item exists
        $customItem = $customItemManager->getCustomItem($itemId);
        if ($customItem === null) {
            $sender->sendMessage("§cCustom item with ID '$itemId' not found!");
            return;
        }

        // Remove the item
        if ($customItemManager->unregisterCustomItem($itemId)) {
            $sender->sendMessage("§aSuccessfully removed custom item:");
            $sender->sendMessage("§7ID: §f$itemId");
            $sender->sendMessage("§7Name: §f{$customItem->getName()}");
            $sender->sendMessage("§7Type: §f{$customItem->getType()}");
        } else {
            $sender->sendMessage("§cFailed to remove custom item '$itemId'!");
        }
    }
}
