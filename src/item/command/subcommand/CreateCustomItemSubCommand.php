<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\item\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

/**
 * Subcommand for creating custom items
 */
class CreateCustomItemSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "create",
            "Create a new custom item",
            "/customitem create <id> <name> [type] [base_item]",
            2,
            4,
            "coreapi.customitem.create"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $id = $args[0];
        $name = $args[1];
        $type = $args[2] ?? "generic";
        $baseItemString = $args[3] ?? null;

        $customItemManager = CoreAPI::getInstance()->getCustomItemManager();

        // Check if item already exists
        if ($customItemManager->getCustomItem($id) !== null) {
            $sender->sendMessage("§cCustom item with ID '$id' already exists!");
            return;
        }

        // Parse base item if provided
        $baseItem = null;
        if ($baseItemString !== null) {
            try {
                $baseItem = StringToItemParser::getInstance()->parse($baseItemString);
                if ($baseItem === null) {
                    $sender->sendMessage("§cInvalid base item: $baseItemString");
                    return;
                }
            } catch (\Exception $e) {
                $sender->sendMessage("§cError parsing base item: " . $e->getMessage());
                return;
            }
        }

        // Create and register the custom item
        $customItem = $customItemManager->createAndRegisterCustomItem(
            $id,
            $name,
            $type,
            $baseItem
        );

        if ($customItem !== null) {
            $sender->sendMessage("§aSuccessfully created custom item:");
            $sender->sendMessage("§7ID: §f$id");
            $sender->sendMessage("§7Name: §f$name");
            $sender->sendMessage("§7Type: §f$type");
            $sender->sendMessage("§7Base Item: §f" . $customItem->getBaseItem()->getName());

            // If sender is a player, give them the item
            if ($sender instanceof Player) {
                if ($customItemManager->giveCustomItem($sender, $id)) {
                    $sender->sendMessage("§aGiven 1x $name to your inventory!");
                }
            }
        } else {
            $sender->sendMessage("§cFailed to create custom item. Check the logs for details.");
        }
    }
}
