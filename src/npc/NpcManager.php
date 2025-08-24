<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\npc;

use JonasWindmann\CoreAPI\manager\BaseManager;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\World;

/**
 * Manager for NPCs in CoreAPI
 * Handles registration, creation, and management of NPCs
 */
class NpcManager extends BaseManager {
    /** @var Config */
    private Config $config;

    /** @var array<int, CoreNpc> */
    private array $activeNpcs = [];

    /** @var array<string, string> */
    private array $registeredNpcTypes = [];

    /**
     * NpcManager constructor
     */
    public function __construct(Plugin $plugin) {
        parent::__construct($plugin);
        $this->initializeConfig();
    }

    /**
     * Initialize configuration
     */
    private function initializeConfig(): void {
        $this->plugin->saveResource("npc_config.yml");
        $this->config = new Config($this->plugin->getDataFolder() . "npc_config.yml", Config::YAML, [
            'npcs' => [],
            'settings' => [
                'default_look_distance' => 8.0,
                'auto_save' => true
            ]
        ]);
    }


    /**
     * Register an NPC type
     * 
     * @param string $className The class name of the NPC type
     * @return bool True if the NPC type was registered, false otherwise
     */
    public function registerNpcType(string $className): bool {
        if (!is_subclass_of($className, CoreNpc::class)) {
            $this->plugin->getLogger()->error("Failed to register NPC type: $className is not a subclass of CoreNpc");
            return false;
        }

        // Get the simple name of the class (without namespace)
        $parts = explode("\\", $className);
        $simpleName = end($parts);

        // Register the NPC type
        $this->registeredNpcTypes[$simpleName] = $className;

        // Register entity factory for this NPC type
        EntityFactory::getInstance()->register($className, function(World $world, CompoundTag $nbt) use ($className): CoreNpc {
            return new $className(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
        }, [$simpleName, $className]);

        $this->plugin->getLogger()->debug("Registered NPC type: $simpleName");

        return true;
    }

    /**
     * Get all registered NPC types
     * 
     * @return array<string, string>
     */
    public function getRegisteredNpcTypes(): array {
        return $this->registeredNpcTypes;
    }

    /**
     * Create a new NPC
     * 
     * @param string $type The type of NPC to create
     * @param Player $creator The player who is creating the NPC
     * @param array $data Additional data for the NPC
     * @return CoreNpc|null The created NPC, or null if creation failed
     */
    public function createNpc(string $type, Player $creator, array $data = []): ?CoreNpc {
        if (!isset($this->registeredNpcTypes[$type])) {
            $this->plugin->getLogger()->error("Failed to create NPC: Unknown type $type");
            return null;
        }

        $className = $this->registeredNpcTypes[$type];

        // Create NBT data
        $nbt = CompoundTag::create();
        $nbt->setString("CoreNpcType", $type);

        if (!empty($data)) {
            $nbt->setString("CoreNpcData", json_encode($data));
        }

        // Create the NPC entity
        /** @var CoreNpc $npc */
        $npc = new $className(
            Location::fromObject(
                $creator->getPosition(),
                $creator->getWorld(),
                $creator->getLocation()->getYaw(),
                $creator->getLocation()->getPitch()
            ),
            $creator->getSkin(),
            $nbt
        );

        // Spawn the NPC
        $npc->spawnToAll();

        // Add to active NPCs
        $this->activeNpcs[$npc->getId()] = $npc;
        $this->items[$npc->getId()] = $npc;

        return $npc;
    }

    /**
     * Remove an NPC
     * 
     * @param int $entityId The entity ID of the NPC to remove
     * @return bool True if the NPC was removed, false otherwise
     */
    public function removeNpc(int $entityId): bool {
        if (!isset($this->activeNpcs[$entityId])) {
            $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
            $entity = $world->getEntity($entityId);
            if ($entity !== null) {
                $entity->close();
            }

            return false;
        }

        $npc = $this->activeNpcs[$entityId];
        $npc->close();

        unset($this->activeNpcs[$entityId]);
        $this->removeItem($entityId);

        return true;
    }

    /**
     * Get an NPC by entity ID
     * 
     * @param int $entityId The entity ID of the NPC
     * @return CoreNpc|null The NPC, or null if not found
     */
    public function getNpcById(int $entityId): ?CoreNpc {
        return $this->activeNpcs[$entityId] ?? null;
    }

    /**
     * Get all active NPCs
     * 
     * @return array<int, CoreNpc>
     */
    public function getActiveNpcs(): array {
        return $this->activeNpcs;
    }

    /**
     * Load items from storage
     */
    public function loadItems(): void {
        $npcs = $this->config->get('npcs', []);

        foreach ($npcs as $npcData) {
            $type = $npcData['type'] ?? null;
            $position = $npcData['position'] ?? null;
            $rotation = $npcData['rotation'] ?? null;
            $data = $npcData['data'] ?? [];

            if ($type === null || $position === null || !isset($this->registeredNpcTypes[$type])) {
                $this->plugin->getLogger()->warning("Failed to load NPC: Invalid data");
                continue;
            }

            $worldName = $position['world'] ?? null;
            if ($worldName === null) {
                $this->plugin->getLogger()->warning("Failed to load NPC: Invalid world");
                continue;
            }

            $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($worldName);
            if ($world === null) {
                $this->plugin->getLogger()->warning("Failed to load NPC: World $worldName not found");
                continue;
            }

            $className = $this->registeredNpcTypes[$type];

            // Create NBT data
            $nbt = CompoundTag::create();
            $nbt->setString("CoreNpcType", $type);

            if (!empty($data)) {
                $nbt->setString("CoreNpcData", json_encode($data));
            }

            // Create the NPC entity
            /** @var CoreNpc $npc */
            $npc = new $className(
                new Location(
                    $position['x'] ?? 0,
                    $position['y'] ?? 0,
                    $position['z'] ?? 0,
                    $world,
                    $rotation['yaw'] ?? 0,
                    $rotation['pitch'] ?? 0
                ),
                $this->getDefaultSkin(),
                $nbt
            );

            // Spawn the NPC
            $npc->spawnToAll();

            // Add to active NPCs
            $this->activeNpcs[$npc->getId()] = $npc;
            $this->items[$npc->getId()] = $npc;
        }
    }

    /**
     * Save items to storage
     */
    public function saveItems(): void {
        $npcs = [];

        foreach ($this->activeNpcs as $id => $npc) {
            $npcs[] = [
                'type' => $npc->getNpcType(),
                'position' => [
                    'x' => $npc->getPosition()->x,
                    'y' => $npc->getPosition()->y,
                    'z' => $npc->getPosition()->z,
                    'world' => $npc->getWorld()->getFolderName()
                ],
                'rotation' => [
                    'yaw' => $npc->getLocation()->getYaw(),
                    'pitch' => $npc->getLocation()->getPitch()
                ],
                'data' => $npc->getAllData()
            ];
        }

        $this->config->set('npcs', $npcs);
        $this->config->save();
    }

    /**
     * Get a default skin for NPCs
     * 
     * @return Skin
     */
    private function getDefaultSkin(): Skin {
        // This is a placeholder - in a real implementation, you'd load a default skin
        $skinData = str_repeat("\x00", 8192);
        return new Skin("Standard_Custom", $skinData, "", "geometry.humanoid.custom");
    }
}
