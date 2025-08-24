<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\npc;

use JonasWindmann\CoreAPI\manager\Manageable;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

/**
 * Base class for all NPCs in CoreAPI
 */
abstract class CoreNpc extends Human implements Manageable {
    /** @var string */
    protected string $npcType;

    /** @var array */
    protected array $data = [];

    /** @var bool */
    protected bool $lookAtPlayer = true;

    /** @var float */
    protected float $lookDistance = 8.0;

    /** @var Player|null */
    protected ?Player $lookTarget = null;

    /** @var bool */
    protected bool $isInvulnerable = true;

    /**
     * CoreNpc constructor.
     */
    public function __construct(Location $location, Skin $skin, CompoundTag $nbt) {
        parent::__construct($location, $skin, $nbt);

        $this->setNameTagAlwaysVisible(); // Prevent movement
        $this->setScale(1.0); // Ensure proper scale

        // Load data from NBT
        if ($nbt->getTag("CoreNpcData") !== null) {
            $this->data = json_decode($nbt->getString("CoreNpcData"), true) ?? [];
        }

        if ($nbt->getTag("CoreNpcType") !== null) {
            $this->npcType = $nbt->getString("CoreNpcType");
        }

        $this->initNpc();
    }

    /**
     * Initialize NPC-specific properties
     */
    protected function initNpc(): void {
        // Override in child classes
    }

    /**
     * Called when the entity is ticked
     */
    public function onUpdate(int $currentTick): bool {
        // Prevent movement
        $this->motion->x = 0;
        $this->motion->y = 0;
        $this->motion->z = 0;

        // Look at nearby players
        if ($this->lookAtPlayer) {
            $this->updateLookTarget();
        }

        return parent::onUpdate($currentTick);
    }

    /**
     * Update the entity the NPC should look at
     */
    protected function updateLookTarget(): void {
        // Find the closest player within range
        $closestPlayer = null;
        $closestDistance = $this->lookDistance;

        foreach ($this->getWorld()->getPlayers() as $player) {
            $distance = $this->getPosition()->distance($player->getPosition());

            if ($distance < $closestDistance) {
                $closestDistance = $distance;
                $closestPlayer = $player;
            }
        }

        // Update look target
        $this->lookTarget = $closestPlayer;

        // Make NPC look at player's eye level
        if ($this->lookTarget !== null) {
            // Use the player's eye height (typically around 1.62 blocks above their position)
            // But adjust slightly to look directly at eyes, not above
            $this->lookAt($this->lookTarget->getPosition());
        }
    }

    /**
     * Make the NPC look at a position
     */
    public function lookAt(Vector3 $target): void {
        $horizontal = sqrt(($target->x - $this->getPosition()->x) ** 2 + ($target->z - $this->getPosition()->z) ** 2);
        $vertical = $target->y - $this->getPosition()->y;
        $pitch = -atan2($vertical, $horizontal) / M_PI * 180;

        $xDist = $target->x - $this->getPosition()->x;
        $zDist = $target->z - $this->getPosition()->z;
        $yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;

        if ($yaw < 0) {
            $yaw += 360.0;
        }

        $this->setRotation($yaw, $pitch);
    }

    /**
     * Save NPC data to NBT
     */
    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();

        // Save NPC data
        $nbt->setString("CoreNpcData", json_encode($this->data));
        $nbt->setString("CoreNpcType", $this->npcType);

        return $nbt;
    }

    /**
     * Set a data value
     */
    public function setData(string $key, $value): void {
        $this->data[$key] = $value;
    }

    /**
     * Get a data value
     */
    public function getData(string $key, $default = null) {
        return $this->data[$key] ?? $default;
    }

    /**
     * Get all data
     */
    public function getAllData(): array {
        return $this->data;
    }

    /**
     * Get the NPC type
     */
    public function getNpcType(): string {
        return $this->npcType;
    }

    /**
     * Set the NPC name/nametag
     */
    public function setNpcName(string $name): void {
        $this->setNameTag(TextFormat::colorize($name));
        $this->setData("name", $name);
    }

    /**
     * Get the NPC name
     */
    public function getNpcName(): string {
        return $this->getData("name", $this->getNameTag());
    }

    /**
     * Get the unique identifier for this object (implements Manageable)
     */
    public function getId(): int {
        return parent::getId();
    }

    /**
     * Called when a player clicks on the NPC
     */
    abstract public function onClick(Player $player): void;

    /**
     * Handle player interaction with the NPC
     * This is called when a player right-clicks on the NPC
     * 
     * @param Player $player The player interacting with the NPC
     * @param Vector3 $clickPos The position that was clicked
     * @return bool Whether the interaction was handled
     */
    public function onInteract(Player $player, Vector3 $clickPos): bool {
        // Call the onClick method when a player interacts with the NPC
        $this->onClick($player);
        return true;
    }

    /**
     * Prevent the NPC from taking damage
     * 
     * @param EntityDamageEvent $source The damage event
     */
    public function attack(EntityDamageEvent $source): void {
        // Cancel the damage event - NPCs are invulnerable
        $source->cancel();

        $entity = $source->getentity()->getId();

        // foreach online player compare the id to get the player class
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            if($entity === $player->getId()){
                $this->onClick($player);
            }
        }
    }
}
