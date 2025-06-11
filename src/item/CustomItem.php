<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\item;

use JonasWindmann\CoreAPI\manager\Manageable;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;

/**
 * Represents a custom item with NBT data and metadata
 * Implements Manageable to work with CoreAPI's manager system
 */
class CustomItem implements Manageable
{
    private string $id;
    private string $name;
    private string $type;
    private Item $baseItem;
    private array $customData;
    private array $lore;
    private string $namespace;

    public function __construct(
        string $id,
        string $name,
        string $type,
        Item $baseItem,
        array $customData = [],
        array $lore = [],
        string $namespace = "CoreAPI"
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->baseItem = $baseItem;
        $this->customData = $customData;
        $this->lore = $lore;
        $this->namespace = $namespace;
    }

    /**
     * Get the unique identifier for this custom item
     * Required by Manageable interface
     * 
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the display name of the custom item
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the type/category of the custom item
     * 
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the base item
     * 
     * @return Item
     */
    public function getBaseItem(): Item
    {
        return $this->baseItem;
    }

    /**
     * Get custom data array
     * 
     * @return array
     */
    public function getCustomData(): array
    {
        return $this->customData;
    }

    /**
     * Get lore array
     * 
     * @return array
     */
    public function getLore(): array
    {
        return $this->lore;
    }

    /**
     * Get namespace
     * 
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Set custom data
     * 
     * @param array $customData
     */
    public function setCustomData(array $customData): void
    {
        $this->customData = $customData;
    }

    /**
     * Set lore
     * 
     * @param array $lore
     */
    public function setLore(array $lore): void
    {
        $this->lore = $lore;
    }

    /**
     * Create an Item instance with NBT data
     * 
     * @param int $count
     * @return Item
     */
    public function createItem(int $count = 1): Item
    {
        $item = clone $this->baseItem;
        $item->setCount($count);

        // Set custom name
        $item->setCustomName(TextFormat::RESET . $this->name);

        // Set lore
        if (!empty($this->lore)) {
            $formattedLore = [];
            foreach ($this->lore as $line) {
                $formattedLore[] = TextFormat::RESET . $line;
            }
            $item->setLore($formattedLore);
        }

        // Set NBT data
        $nbt = $item->getNamedTag();
        
        // Add custom item identifier
        $nbt->setTag($this->namespace . "_id", new StringTag($this->id));
        $nbt->setTag($this->namespace . "_type", new StringTag($this->type));

        // Add custom data
        if (!empty($this->customData)) {
            $customDataTag = new CompoundTag();
            foreach ($this->customData as $key => $value) {
                if (is_string($value)) {
                    $customDataTag->setTag($key, new StringTag($value));
                } elseif (is_numeric($value)) {
                    $customDataTag->setTag($key, new StringTag((string) $value));
                }
            }
            $nbt->setTag($this->namespace . "_data", $customDataTag);
        }

        $item->setNamedTag($nbt);
        return $item;
    }

    /**
     * Check if an item is a custom item
     * 
     * @param Item $item
     * @param string $namespace
     * @return bool
     */
    public static function isCustomItem(Item $item, string $namespace = "CoreAPI"): bool
    {
        $nbt = $item->getNamedTag();
        return $nbt->getTag($namespace . "_id") !== null;
    }

    /**
     * Get custom item ID from an item
     * 
     * @param Item $item
     * @param string $namespace
     * @return string|null
     */
    public static function getCustomItemId(Item $item, string $namespace = "CoreAPI"): ?string
    {
        $nbt = $item->getNamedTag();
        $tag = $nbt->getTag($namespace . "_id");
        return $tag instanceof StringTag ? $tag->getValue() : null;
    }

    /**
     * Get custom item type from an item
     * 
     * @param Item $item
     * @param string $namespace
     * @return string|null
     */
    public static function getCustomItemType(Item $item, string $namespace = "CoreAPI"): ?string
    {
        $nbt = $item->getNamedTag();
        $tag = $nbt->getTag($namespace . "_type");
        return $tag instanceof StringTag ? $tag->getValue() : null;
    }

    /**
     * Get custom data from an item's NBT
     *
     * @param Item $item
     * @param string $key
     * @param string $namespace
     * @return mixed
     */
    public static function getCustomDataFromItem(Item $item, string $key, string $namespace = "CoreAPI"): mixed
    {
        $nbt = $item->getNamedTag();
        $dataTag = $nbt->getCompoundTag($namespace . "_data");
        if ($dataTag === null) {
            return null;
        }

        $tag = $dataTag->getTag($key);
        return $tag instanceof StringTag ? $tag->getValue() : null;
    }

    /**
     * Convert to array for serialization
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'base_item' => $this->baseItem->getName(),
            'custom_data' => $this->customData,
            'lore' => $this->lore,
            'namespace' => $this->namespace
        ];
    }

    /**
     * Create from array
     * 
     * @param array $data
     * @return CustomItem|null
     */
    public static function fromArray(array $data): ?CustomItem
    {
        if (!isset($data['id'], $data['name'], $data['type'], $data['base_item'])) {
            return null;
        }

        try {
            $baseItem = StringToItemParser::getInstance()->parse($data['base_item']);
            if ($baseItem === null) {
                $baseItem = VanillaItems::STICK();
            }
        } catch (\Exception $e) {
            $baseItem = VanillaItems::STICK();
        }

        return new CustomItem(
            $data['id'],
            $data['name'],
            $data['type'],
            $baseItem,
            $data['custom_data'] ?? [],
            $data['lore'] ?? [],
            $data['namespace'] ?? "CoreAPI"
        );
    }

    /**
     * Get a string representation of the custom item
     * 
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            "CustomItem[id=%s, name=%s, type=%s, base=%s]",
            $this->id,
            $this->name,
            $this->type,
            $this->baseItem->getName()
        );
    }
}
