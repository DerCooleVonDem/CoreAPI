<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\item\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;

/**
 * Subcommand for showing custom item information
 */
class InfoCustomItemSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "info",
            "Show information about a custom item",
            "/customitem info <id>",
            1,
            1,
            "coreapi.customitem.info"
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

        $sender->sendMessage("§6=== Custom Item Info ===");
        $sender->sendMessage("§7ID: §f{$customItem->getId()}");
        $sender->sendMessage("§7Name: §f{$customItem->getName()}");
        $sender->sendMessage("§7Type: §f{$customItem->getType()}");
        $sender->sendMessage("§7Base Item: §f{$customItem->getBaseItem()->getName()}");
        $sender->sendMessage("§7Namespace: §f{$customItem->getNamespace()}");

        // Show custom data
        $customData = $customItem->getCustomData();
        if (!empty($customData)) {
            $sender->sendMessage("§7Custom Data:");
            foreach ($customData as $key => $value) {
                $sender->sendMessage("  §7- §e$key§7: §f$value");
            }
        } else {
            $sender->sendMessage("§7Custom Data: §8None");
        }

        // Show lore
        $lore = $customItem->getLore();
        if (!empty($lore)) {
            $sender->sendMessage("§7Lore:");
            foreach ($lore as $line) {
                $sender->sendMessage("  §7- §f$line");
            }
        } else {
            $sender->sendMessage("§7Lore: §8None");
        }
    }
}
