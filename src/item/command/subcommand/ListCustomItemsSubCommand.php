<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\item\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;

/**
 * Subcommand for listing all custom items
 */
class ListCustomItemsSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "list",
            "List all registered custom items",
            "/customitem list [type]",
            0,
            1,
            "coreapi.customitem.list"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $customItemManager = CoreAPI::getInstance()->getCustomItemManager();
        $allItems = $customItemManager->getAllCustomItems();

        if (empty($allItems)) {
            $sender->sendMessage("§cNo custom items registered!");
            return;
        }

        // Filter by type if specified
        $filterType = $args[0] ?? null;
        if ($filterType !== null) {
            $allItems = array_filter($allItems, fn($item) => $item->getType() === $filterType);
            if (empty($allItems)) {
                $sender->sendMessage("§cNo custom items found with type '$filterType'!");
                return;
            }
        }

        // Group items by type
        $itemsByType = [];
        foreach ($allItems as $item) {
            $itemsByType[$item->getType()][] = $item;
        }

        $sender->sendMessage("§6=== Custom Items " . ($filterType ? "($filterType)" : "") . " ===");
        
        foreach ($itemsByType as $type => $items) {
            $sender->sendMessage("§e$type:");
            foreach ($items as $item) {
                $sender->sendMessage("  §7- §f{$item->getId()} §7(§a{$item->getName()}§7) §8[{$item->getBaseItem()->getName()}]");
            }
        }

        $totalCount = count($allItems);
        $sender->sendMessage("§7Total: §a$totalCount §7items");

        // Show statistics
        $stats = $customItemManager->getStats();
        $sender->sendMessage("§7Registry: §a{$stats['registry_stats']['total_types']}§7/§e{$stats['registry_stats']['max_types']} §7types");
    }
}
