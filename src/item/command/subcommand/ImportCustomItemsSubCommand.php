<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\item\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;

/**
 * Subcommand for importing custom items
 */
class ImportCustomItemsSubCommand extends SubCommand
{
    public function __construct()
    {
        parent::__construct(
            "import",
            "Import custom items from a file",
            "/customitem import <filename> [overwrite]",
            1,
            2,
            "coreapi.customitem.import"
        );
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $filename = $args[0];
        $overwrite = isset($args[1]) && strtolower($args[1]) === "true";

        // Ensure .json extension
        if (!str_ends_with($filename, ".json")) {
            $filename .= ".json";
        }

        $importPath = CoreAPI::getInstance()->getDataFolder() . "exports/" . $filename;

        if (!file_exists($importPath)) {
            $sender->sendMessage("§cImport file not found: $filename");
            $sender->sendMessage("§7Place the file in: " . dirname($importPath));
            return;
        }

        try {
            $jsonData = file_get_contents($importPath);
            $importData = json_decode($jsonData, true);

            if ($importData === null) {
                $sender->sendMessage("§cInvalid JSON format in import file!");
                return;
            }

            if (!is_array($importData)) {
                $sender->sendMessage("§cImport data must be an array of custom items!");
                return;
            }

            $customItemManager = CoreAPI::getInstance()->getCustomItemManager();
            $imported = $customItemManager->importCustomItems($importData, $overwrite);

            if ($imported > 0) {
                $sender->sendMessage("§aSuccessfully imported $imported custom items from $filename");
                if (!$overwrite) {
                    $sender->sendMessage("§7Note: Existing items were not overwritten. Use 'true' as second argument to overwrite.");
                }
            } else {
                $sender->sendMessage("§cNo items were imported. They may already exist or have invalid data.");
            }

        } catch (\Exception $e) {
            $sender->sendMessage("§cError during import: " . $e->getMessage());
        }
    }
}
