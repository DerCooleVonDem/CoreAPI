<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\item\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;

/**
 * Debug subcommand for testing custom item functionality
 */
class DebugCustomItemSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "debug",
            "Debug custom item system and reload examples",
            "/customitem debug [reload]",
            0,
            1,
            "coreapi.customitem.debug"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $customItemManager = CoreAPI::getInstance()->getCustomItemManager();
        
        if (isset($args[0]) && $args[0] === "reload") {
            $sender->sendMessage("§6=== Reloading Example Items ===");
            
            // Force reload the configuration and example items
            $reflection = new \ReflectionClass($customItemManager);
            $configProperty = $reflection->getProperty('config');
            $configProperty->setAccessible(true);
            $config = $configProperty->getValue($customItemManager);
            $config->reload();
            
            // Call the private loadExampleItems method
            $loadExampleItemsMethod = $reflection->getMethod('loadExampleItems');
            $loadExampleItemsMethod->setAccessible(true);
            $loadExampleItemsMethod->invoke($customItemManager);
            
            $sender->sendMessage("§aExample items reload completed!");
        }
        
        // Show current state
        $sender->sendMessage("§6=== Custom Item Manager Debug ===");
        
        // Show configuration status
        $reflection = new \ReflectionClass($customItemManager);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($customItemManager);
        
        $examplesEnabled = $config->get("examples.enabled", false);
        $exampleItems = $config->get("examples.items", []);
        
        $sender->sendMessage("§7Examples Enabled: " . ($examplesEnabled ? "§aYES" : "§cNO"));
        $sender->sendMessage("§7Example Items in Config: §f" . count($exampleItems));
        
        // Show registered items
        $allItems = $customItemManager->getAllCustomItems();
        $sender->sendMessage("§7Registered Items: §f" . count($allItems));
        
        if (!empty($allItems)) {
            $sender->sendMessage("§7Registered Item List:");
            foreach ($allItems as $item) {
                $sender->sendMessage("  §7- §e{$item->getId()} §7(§f{$item->getName()}§7) §8[{$item->getType()}]");
            }
        }
        
        // Show registry stats
        $stats = $customItemManager->getStats();
        $sender->sendMessage("§7Registry Stats:");
        $sender->sendMessage("  §7Total Types: §f{$stats['registry_stats']['total_types']}");
        $sender->sendMessage("  §7Max Types: §f{$stats['registry_stats']['max_types']}");
        $sender->sendMessage("  §7Allow Duplicates: " . ($stats['registry_stats']['allow_duplicates'] ? "§aYES" : "§cNO"));
        
        if (!empty($stats['registry_stats']['types_by_category'])) {
            $sender->sendMessage("  §7Types by Category:");
            foreach ($stats['registry_stats']['types_by_category'] as $type => $count) {
                $sender->sendMessage("    §7- §e$type.§7: §f$count");
            }
        }
        
        // Show example items from config
        if ($examplesEnabled && !empty($exampleItems)) {
            $sender->sendMessage("§7Example Items from Config:");
            foreach ($exampleItems as $index => $itemData) {
                if (is_array($itemData) && isset($itemData['id'])) {
                    $isRegistered = $customItemManager->getCustomItem($itemData['id']) !== null;
                    $status = $isRegistered ? "§aREGISTERED" : "§cNOT REGISTERED";
                    $sender->sendMessage("  §7- §e{$itemData['id']} §7( $status §7)");
                }
            }
        }
        
        $sender->sendMessage("§7Use '/customitem debug reload' to force reload example items");
    }
}
