<?php

declare(strict_types=1);

namespace wolfcore\network\mcpe\convert;

use wolfcore\data\bedrock\block\BlockStateData;
use wolfcore\nbt\LittleEndianNbtSerializer;
use wolfcore\nbt\TreeRoot;
use wolfcore\nbt\tag\CompoundTag;
use wolfcore\nbt\tag\IntTag;
use wolfcore\nbt\tag\ByteTag;
use wolfcore\nbt\tag\StringTag;

/**
 * Bedrock hashed block IDs (introduced before 1.20.10): hash the canonical
 * little-endian NBT {name, states}, sorted state keys. The version is NOT hashed.
 * Never change the base engine palette; this is only a network adapter.
 */
final class BlockNetworkHash{
    private function __construct(){}

    /** @var array<int, array<int, int>> protocol => base runtime ID => wire hash. */
    private static array $baseToHash = [];

    public static function hash(BlockStateData $data) : int{
        if($data->getName() === "minecraft:unknown"){
            return 0xfffffffe; // Mojang's reserved unknown-block network ID
        }
        $properties = $data->getStates();
        ksort($properties, SORT_STRING);
        $states = CompoundTag::create();
        foreach($properties as $key => $value){
            $states->setTag($key, $value);
        }
        $nbt = CompoundTag::create()->setString("name", $data->getName())->setTag("states", $states);
        $raw = (new LittleEndianNbtSerializer())->write(new TreeRoot($nbt));
        return (int) hexdec(hash("fnv1a32", $raw));
    }

    /**
     * Minecraft Bedrock 1.20.10 (594) flattened the 16 concrete colour states.
     * The 1.20.0 engine MUST retain minecraft:concrete[color=...] internally.
     * The hash sent to a 594 client MUST instead be that of the flattened name.
     * No blocks or mechanics are added to the game engine by this conversion.
     */
    public static function baseTo594State(BlockStateData $state) : BlockStateData{
        // 1.20.10 switched the vanilla observer direction from the integer
        // facing_direction to the namespaced string minecraft:facing_direction.
        // Change ONLY the wire representation, not the 589 game state.
        if($state->getName() === "minecraft:observer"){
            $properties = $state->getStates();
            if(($direction = $properties["facing_direction"] ?? null) instanceof IntTag){
                $names = ["down", "up", "north", "south", "west", "east"];
                $index = $direction->getValue();
                if(isset($names[$index])){
                    unset($properties["facing_direction"]);
                    $properties["minecraft:facing_direction"] = new StringTag($names[$index]);
                    return new BlockStateData($state->getName(), $properties, $state->getVersion());
                }
            }
            return $state;
        }
        // Bedrock 1.20.10 (594) flattened coloured shulker boxes, too.
        // The 589 world/block representation remains minecraft:shulker_box[color=...].
        if($state->getName() === "minecraft:shulker_box"){
            $properties = $state->getStates();
            if(count($properties) === 1 && ($colour = $properties["color"] ?? null) instanceof StringTag){
                $c = $colour->getValue();
                if(in_array($c, self::LEGACY_CONCRETE_COLOURS, true)){
                    return new BlockStateData(
                        "minecraft:" . ($c === "silver" ? "light_gray" : $c) . "_shulker_box",
                        [],
                        $state->getVersion()
                    );
                }
            }
        }
        if($state->getName() !== "minecraft:concrete"){
            return $state;
        }
        $properties = $state->getStates();
        if(count($properties) !== 1 || !isset($properties["color"]) || !$properties["color"] instanceof StringTag){
            return $state;
        }
        $color = $properties["color"]->getValue();
        if(!in_array($color, self::LEGACY_CONCRETE_COLOURS, true)){
            return $state;
        }
        // Mojang called light grey 'silver' in the old concrete palette.
        return new BlockStateData(
            "minecraft:" . ($color === "silver" ? "light_gray" : $color) . "_concrete",
            [],
            $state->getVersion()
        );
    }

    /** Reverse network-only flattening before handing items to the 1.20.0 engine. */
    public static function state594ToBase(BlockStateData $state) : BlockStateData{
        if($state->getName() === "minecraft:observer"){
            $properties = $state->getStates();
            if(($direction = $properties["minecraft:facing_direction"] ?? null) instanceof StringTag){
                $index = array_search($direction->getValue(), ["down", "up", "north", "south", "west", "east"], true);
                if($index !== false){
                    unset($properties["minecraft:facing_direction"]);
                    $properties["facing_direction"] = new IntTag($index);
                    return new BlockStateData($state->getName(), $properties, $state->getVersion());
                }
            }
            return $state;
        }
        // Reverse 594+ shulker flattening before feeding the 589 serializers.
        if(count($state->getStates()) === 0 && str_starts_with($state->getName(), "minecraft:") && str_ends_with($state->getName(), "_shulker_box")){
            $colour = substr($state->getName(), strlen("minecraft:"), -strlen("_shulker_box"));
            if($colour === "light_gray" || ($colour !== "silver" && in_array($colour, self::LEGACY_CONCRETE_COLOURS, true))){
                return new BlockStateData(
                    "minecraft:shulker_box",
                    ["color" => new StringTag($colour === "light_gray" ? "silver" : $colour)],
                    $state->getVersion()
                );
            }
        }
        if(count($state->getStates()) !== 0 || !str_starts_with($state->getName(), "minecraft:") || !str_ends_with($state->getName(), "_concrete")){
            return $state;
        }
        $color = substr($state->getName(), strlen("minecraft:"), -strlen("_concrete"));
        $color = $color === "light_gray" ? "silver" : $color;
        if(!in_array($color, self::LEGACY_CONCRETE_COLOURS, true)){
            return $state;
        }
        return new BlockStateData("minecraft:concrete", ["color" => new StringTag($color)], $state->getVersion());
    }

    private const LEGACY_CONCRETE_COLOURS = [
        "white", "orange", "magenta", "light_blue", "yellow", "lime", "pink", "gray",
        "silver", "cyan", "purple", "blue", "brown", "green", "red", "black"
    ];

    /** Exact flattened concrete registry names known to 1.20.10; no suffix wildcard. */
    public static function is594ConcreteStateName(string $name) : bool{
        if(!str_starts_with($name, "minecraft:") || !str_ends_with($name, "_concrete")){
            return false;
        }
        $colour = substr($name, strlen("minecraft:"), -strlen("_concrete"));
        return $colour === "light_gray" || ($colour !== "silver" && in_array($colour, self::LEGACY_CONCRETE_COLOURS, true));
    }

    /**
     * Bedrock 1.20.50 (630) split the original 589 planks and stone states
     * into independent block IDs. Only a network representation change:
     * worlds, engine blocks and plugin API continue using 1.20.0 states.
     * Use an exact table, not a suffix heuristic (avoids touching unrelated
     * blocks such as sandstone, red_sandstone, or named plank stairs).
     *
     * @var array<string, array<string, string>>
     */
    private const FLATTENED_630 = [
        'minecraft:planks' => [
            'oak' => 'minecraft:oak_planks',
            'spruce' => 'minecraft:spruce_planks',
            'birch' => 'minecraft:birch_planks',
            'jungle' => 'minecraft:jungle_planks',
            'acacia' => 'minecraft:acacia_planks',
            'dark_oak' => 'minecraft:dark_oak_planks'
        ],
        'minecraft:stone' => [
            'stone' => 'minecraft:stone',
            'granite' => 'minecraft:granite',
            'granite_smooth' => 'minecraft:polished_granite',
            'diorite' => 'minecraft:diorite',
            'diorite_smooth' => 'minecraft:polished_diorite',
            'andesite' => 'minecraft:andesite',
            'andesite_smooth' => 'minecraft:polished_andesite'
        ]
    ];

    /**
     * Returns the 1.20.0 engine name and legacy state of a 630 flattened block.
     * @return array{string, string, string}|null
     */
    private static function original630State(string $wireName) : ?array{
        foreach(self::FLATTENED_630 as $originalName => $values){
            foreach($values as $legacyValue => $flattenedName){
                if($wireName === $flattenedName){
                    return [$originalName, $originalName === 'minecraft:planks' ? 'wood_type' : 'stone_type', $legacyValue];
                }
            }
        }
        return null;
    }

    /**
     * The 1.20.30 changes are *only* applied to network palettes (protocol 618).
     * The base 1.20.0 state, registry, and server's block implementation remain unchanged.
     */
    public static function baseToProtocolState(BlockStateData $original, int $protocol) : BlockStateData{
        $state = self::baseTo594State($original);
        if($protocol < 618){
            return $state;
        }
        $name = $state->getName();
        $properties = $state->getStates();
        $version = $state->getVersion();

        // In 630, planks[wood_type=...] and stone[stone_type=...] were
        // flattened to individual names with no properties. Keep 618/622
        // behaviour untouched so older clients retain their original hashes.
        if($protocol >= 630){
            $legacyProperty = match($name){
                'minecraft:planks' => 'wood_type',
                'minecraft:stone' => 'stone_type',
                default => null
            };
            if($legacyProperty !== null && count($properties) === 1 && ($variant = $properties[$legacyProperty] ?? null) instanceof StringTag){
                $newName = self::FLATTENED_630[$name][$variant->getValue()] ?? null;
                if($newName !== null){
                    return new BlockStateData($newName, [], $version);
                }
            }
        }

        // 1.20.30 split legacy coloured blocks into individual named block states.
        $flattened = [
            "minecraft:stained_glass" => "stained_glass",
            "minecraft:stained_glass_pane" => "stained_glass_pane",
            "minecraft:hard_stained_glass_pane" => "hard_stained_glass_pane",
            "minecraft:hard_stained_glass" => "hard_stained_glass",
            "minecraft:stained_hardened_clay" => "terracotta",
            "minecraft:concrete_powder" => "concrete_powder"
        ];
        if(isset($flattened[$name]) && count($properties) === 1 && ($colour = $properties["color"] ?? null) instanceof StringTag){
            $c = $colour->getValue();
            if(in_array($c, self::LEGACY_CONCRETE_COLOURS, true)){
                return new BlockStateData("minecraft:" . (str_starts_with($flattened[$name], 'hard_') ? 'hard_' : '') . ($c === "silver" ? "light_gray" : $c) . "_" . (str_starts_with($flattened[$name], 'hard_') ? substr($flattened[$name], 5) : $flattened[$name]), [], $version);
            }
        }
        // Older amethyst buds and cluster use 0..5: down/up/north/south/west/east.
        if(in_array($name, [
            "minecraft:small_amethyst_bud", "minecraft:medium_amethyst_bud",
            "minecraft:large_amethyst_bud", "minecraft:amethyst_cluster"
        ], true) && ($face = $properties["facing_direction"] ?? null) instanceof IntTag){
            $faceNames = ["down", "up", "north", "south", "west", "east"];
            if(isset($faceNames[$face->getValue()])){
                unset($properties["facing_direction"]);
                $properties["minecraft:block_face"] = new StringTag($faceNames[$face->getValue()]);
            }
        }
        // 1.20.30 slab orientation is a namespaced string.
        if(($half = $properties["top_slot_bit"] ?? null) instanceof ByteTag){
            unset($properties["top_slot_bit"]);
            $properties["minecraft:vertical_half"] = new StringTag($half->getValue() === 0 ? "bottom" : "top");
        }
        // Four-way facing direction had a numeric state 0..3 in 1.20.0.
        if(in_array($name, [
            "minecraft:big_dripleaf", "minecraft:small_dripleaf", "minecraft:pink_petals",
            "minecraft:end_portal_frame", "minecraft:lectern", "minecraft:anvil",
            "minecraft:campfire", "minecraft:soul_campfire", "minecraft:calibrated_sculk_sensor",
            "minecraft:powered_comparator", "minecraft:unpowered_comparator",
            "minecraft:powered_repeater", "minecraft:unpowered_repeater"
        ], true) && ($dir = $properties["direction"] ?? null) instanceof IntTag){
            $names = ["south", "west", "north", "east"];
            if(isset($names[$dir->getValue()])){
                unset($properties["direction"]);
                $properties["minecraft:cardinal_direction"] = new StringTag($names[$dir->getValue()]);
            }
        }
        // Furnace family historically used six faces. Horizontal orientations
        // are canonicalised to the four 1.20.30 compass values. Down/up map north.
        if(in_array($name, [
            "minecraft:furnace", "minecraft:lit_furnace", "minecraft:blast_furnace",
            "minecraft:lit_blast_furnace", "minecraft:smoker", "minecraft:lit_smoker"
        ], true) && ($dir = $properties["facing_direction"] ?? null) instanceof IntTag){
            $names = [0 => "north", 1 => "north", 2 => "north", 3 => "south", 4 => "west", 5 => "east", 6 => "east"];
            if(isset($names[$dir->getValue()])){
                unset($properties["facing_direction"]);
                $properties["minecraft:cardinal_direction"] = new StringTag($names[$dir->getValue()]);
            }
        }
        if($properties === $state->getStates()){
            return $state;
        }
        return new BlockStateData($name, $properties, $version);
    }

    /** Convert only network states; do not rewrite level DB or engine block states. */
    public static function protocolStateToBase(BlockStateData $state, int $protocol) : BlockStateData{
        if($protocol < 618){
            return self::state594ToBase($state);
        }
        $name = $state->getName();
        $props = $state->getStates();
        $version = $state->getVersion();
        if($protocol >= 630 && count($props) === 0 && ($original = self::original630State($name)) !== null){
            [$originalName, $originalProperty, $originalValue] = $original;
            return new BlockStateData($originalName, [$originalProperty => new StringTag($originalValue)], $version);
        }
        $baseName = self::flattenedBaseName($name, $protocol);
        if($baseName !== null && $baseName !== 'minecraft:concrete' && $baseName !== 'minecraft:shulker_box' && count($props) === 0){
            $suffix = match($baseName){
                'minecraft:stained_glass' => '_stained_glass',
                'minecraft:stained_glass_pane' => '_stained_glass_pane',
                'minecraft:hard_stained_glass_pane' => '_stained_glass_pane',
                'minecraft:hard_stained_glass' => '_stained_glass',
                'minecraft:stained_hardened_clay' => '_terracotta',
                'minecraft:concrete_powder' => '_concrete_powder',
                default => ''
            };
            $c = substr($name, strlen('minecraft:'), -strlen($suffix));
            if(str_starts_with($c, 'hard_')){
                $c = substr($c, 5);
            }
            return new BlockStateData($baseName, ['color' => new StringTag($c === 'light_gray' ? 'silver' : $c)], $version);
        }
        if(in_array($name, ["minecraft:small_amethyst_bud", "minecraft:medium_amethyst_bud", "minecraft:large_amethyst_bud", "minecraft:amethyst_cluster"], true) && ($face = $props["minecraft:block_face"] ?? null) instanceof StringTag){
            $i = array_search($face->getValue(), ["down", "up", "north", "south", "west", "east"], true);
            if($i !== false){
                unset($props["minecraft:block_face"]);
                $props["facing_direction"] = new IntTag($i);
            }
        }
        if(($half = $props["minecraft:vertical_half"] ?? null) instanceof StringTag){
            unset($props["minecraft:vertical_half"]);
            $props["top_slot_bit"] = new ByteTag($half->getValue() === 'top' ? 1 : 0);
        }
        if(in_array($name, [
            "minecraft:big_dripleaf", "minecraft:small_dripleaf", "minecraft:pink_petals",
            "minecraft:end_portal_frame", "minecraft:lectern", "minecraft:anvil",
            "minecraft:campfire", "minecraft:soul_campfire", "minecraft:calibrated_sculk_sensor",
            "minecraft:powered_comparator", "minecraft:unpowered_comparator",
            "minecraft:powered_repeater", "minecraft:unpowered_repeater",
            "minecraft:furnace", "minecraft:lit_furnace", "minecraft:blast_furnace",
            "minecraft:lit_blast_furnace", "minecraft:smoker", "minecraft:lit_smoker"
        ], true) && ($cardinal = $props["minecraft:cardinal_direction"] ?? null) instanceof StringTag){
            $isFurnace = in_array($name, ["minecraft:furnace", "minecraft:lit_furnace", "minecraft:blast_furnace", "minecraft:lit_blast_furnace", "minecraft:smoker", "minecraft:lit_smoker"], true);
            $names = $isFurnace ? ["north" => 2, "south" => 3, "west" => 4, "east" => 5] : ["south" => 0, "west" => 1, "north" => 2, "east" => 3];
            if(isset($names[$cardinal->getValue()])){
                unset($props["minecraft:cardinal_direction"]);
                $props[$isFurnace ? "facing_direction" : "direction"] = new IntTag($names[$cardinal->getValue()]);
            }
        }
        return self::state594ToBase(new BlockStateData($name, $props, $version));
    }

    /** Identify flattened variants only for exact known colour+suffix combinations. */
    public static function flattenedBaseName(string $wireName, int $protocol) : ?string{
        if($protocol >= 630 && ($old = self::original630State($wireName)) !== null){
            return $old[0];
        }
        if($protocol >= 594 && str_starts_with($wireName, "minecraft:") && str_ends_with($wireName, "_shulker_box")){
            $colour = substr($wireName, strlen("minecraft:"), -strlen("_shulker_box"));
            if($colour === "light_gray" || ($colour !== "silver" && in_array($colour, self::LEGACY_CONCRETE_COLOURS, true))){
                return "minecraft:shulker_box";
            }
        }
        $types = $protocol >= 618 ? [
            '_stained_glass_pane' => 'minecraft:stained_glass_pane',
            '_stained_glass' => 'minecraft:stained_glass',
            '_concrete_powder' => 'minecraft:concrete_powder',
            '_terracotta' => 'minecraft:stained_hardened_clay',
            '_concrete' => 'minecraft:concrete'
        ] : ['_concrete' => 'minecraft:concrete'];
        foreach($types as $suffix => $base){
            if(!str_starts_with($wireName, 'minecraft:') || !str_ends_with($wireName, $suffix)){
                continue;
            }
            $color = substr($wireName, strlen('minecraft:'), -strlen($suffix));
            if($protocol >= 618 && str_starts_with($color, 'hard_') && in_array($suffix, ['_stained_glass_pane', '_stained_glass'], true)){
                $color = substr($color, 5);
                $base = $suffix === '_stained_glass_pane' ? 'minecraft:hard_stained_glass_pane' : 'minecraft:hard_stained_glass';
            }
            if($color === 'light_gray' || ($color !== 'silver' && in_array($color, self::LEGACY_CONCRETE_COLOURS, true))){
                return $base;
            }
        }
        return null;
    }

    /** Converts a base 589 runtime block ID to a hash for a hash-mode client. */
    public static function baseIdToHash(int $baseId, int $protocol = 594) : int{
        if(isset(self::$baseToHash[$protocol][$baseId])){
            return self::$baseToHash[$protocol][$baseId];
        }
        $base = TypeConverter::getInstance()->getBlockTranslator()->getBlockStateDictionary();
        $state = $base->generateDataFromStateId($baseId);
        if($state === null){
            throw new \InvalidArgumentException("Invalid base block runtime ID: " . $baseId);
        }
        return self::$baseToHash[$protocol][$baseId] = self::hash(self::baseToProtocolState($state, $protocol));
    }

    public static function toSigned32(int $id) : int{
        return $id >= 0x80000000 ? $id - 0x100000000 : $id;
    }

    public static function hashedDictionary(BlockStateDictionary $base, int $protocol = 594) : BlockStateDictionary{
        $states = [];
        $aliases = [];
        foreach($base->getStates() as $entry){
            $baseState = $entry->generateStateData();
            $wireState = self::baseToProtocolState($baseState, $protocol);
            $hash = self::hash($wireState);
            $wireEntry = $baseState === $wireState
                ? $entry
                : new BlockStateDictionaryEntry($wireState->getName(), $wireState->getStates(), $entry->getMeta());
            if(isset($states[$hash]) && (
                $states[$hash]->getStateName() !== $wireEntry->getStateName() ||
                $states[$hash]->getRawStateProperties() !== $wireEntry->getRawStateProperties()
            )){
                throw new \RuntimeException("Bedrock block network hash collision " . $hash);
            }
            $states[$hash] = $wireEntry;
            if($wireState !== $baseState){
                $aliases[] = [$baseState, $hash, $entry->getMeta()];
            }
        }
        $dictionary = new BlockStateDictionary($states);
        foreach($aliases as [$legacy, $hash, $meta]){
            $dictionary->registerLegacyStateAlias($legacy, $hash, $meta);
        }
        return $dictionary;
    }
}
