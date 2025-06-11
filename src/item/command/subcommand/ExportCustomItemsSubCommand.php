<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\item\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;

/**
 * Subcommand for exporting custom items
 */
class ExportCustomItemsSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "export",
            "Export all custom items to a file",
            "/customitem export [filename]",
            0,
            1,
            "coreapi.customitem.export"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $customItemManager = CoreAPI::getInstance()->getCustomItemManager();
        $allItems = $customItemManager->getAllCustomItems();

        if (empty($allItems)) {
            $sender->sendMessage("§cNo custom items to export!");
            return;
        }

        $filename = $args[0] ?? "custom_items_export_" . date("Y-m-d_H-i-s") . ".json";
        
        // Ensure .json extension
        if (!str_ends_with($filename, ".json")) {
            $filename .= ".json";
        }

        $exportData = $customItemManager->exportAll();
        $exportPath = CoreAPI::getInstance()->getDataFolder() . "exports/";
        
        // Create exports directory if it doesn't exist
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0755, true);
        }

        $fullPath = $exportPath . $filename;
        
        try {
            $jsonData = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if (file_put_contents($fullPath, $jsonData) !== false) {
                $sender->sendMessage("§aSuccessfully exported " . count($allItems) . " custom items to:");
                $sender->sendMessage("§7$fullPath");
            } else {
                $sender->sendMessage("§cFailed to write export file!");
            }
        } catch (\Exception $e) {
            $sender->sendMessage("§cError during export: " . $e->getMessage());
        }
    }
}
