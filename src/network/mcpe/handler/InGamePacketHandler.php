<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace wolfcore\network\mcpe\handler;

use wolfcore\block\BaseSign;
use wolfcore\block\Water;
use wolfcore\block\ItemFrame;
use wolfcore\block\Jukebox;
use wolfcore\block\Lectern;
use wolfcore\block\tile\Sign;
use wolfcore\block\utils\SignText;
use wolfcore\entity\Attribute;
use wolfcore\entity\animation\ArmSwingAnimation;
use wolfcore\entity\InvalidSkinException;
use wolfcore\network\mcpe\convert\DefaultSkinFactory;
use wolfcore\event\player\PlayerEditBookEvent;
use wolfcore\inventory\transaction\action\DropItemAction;
use wolfcore\inventory\transaction\InventoryTransaction;
use wolfcore\inventory\transaction\TransactionBuilder;
use wolfcore\inventory\transaction\TransactionCancelledException;
use wolfcore\inventory\transaction\TransactionValidationException;
use wolfcore\item\VanillaItems;
use wolfcore\item\Record;
use wolfcore\item\WritableBook;
use wolfcore\item\WritableBookPage;
use wolfcore\item\WrittenBook;
use wolfcore\math\Facing;
use wolfcore\math\Vector3;
use wolfcore\nbt\tag\CompoundTag;
use wolfcore\nbt\tag\StringTag;
use wolfcore\network\mcpe\InventoryManager;
use wolfcore\network\mcpe\NetworkSession;
use wolfcore\network\mcpe\protocol\ActorEventPacket;
use wolfcore\network\mcpe\protocol\ActorPickRequestPacket;
use wolfcore\network\mcpe\protocol\AnimatePacket;
use wolfcore\network\mcpe\protocol\BlockActorDataPacket;
use wolfcore\network\mcpe\protocol\BlockPickRequestPacket;
use wolfcore\network\mcpe\protocol\BookEditPacket;
use wolfcore\network\mcpe\protocol\BossEventPacket;
use wolfcore\network\mcpe\protocol\CommandBlockUpdatePacket;
use wolfcore\network\mcpe\protocol\CommandRequestPacket;
use wolfcore\network\mcpe\protocol\ContainerClosePacket;
use wolfcore\network\mcpe\protocol\CraftingEventPacket;
use wolfcore\network\mcpe\protocol\EmoteListPacket;
use wolfcore\network\mcpe\protocol\EmotePacket;
use wolfcore\network\mcpe\protocol\InteractPacket;
use wolfcore\network\mcpe\protocol\InventoryTransactionPacket;
use wolfcore\network\mcpe\protocol\ItemFrameDropItemPacket;
use wolfcore\network\mcpe\protocol\ItemStackRequestPacket;
use wolfcore\network\mcpe\protocol\ItemStackResponsePacket;
use wolfcore\network\mcpe\protocol\LabTablePacket;
use wolfcore\network\mcpe\protocol\LecternUpdatePacket;
use wolfcore\network\mcpe\protocol\LevelSoundEventPacket;
use wolfcore\network\mcpe\protocol\LevelSoundEventPacketV1;
use wolfcore\network\mcpe\protocol\MapInfoRequestPacket;
use wolfcore\network\mcpe\protocol\MobArmorEquipmentPacket;
use wolfcore\network\mcpe\protocol\MobEquipmentPacket;
use wolfcore\network\mcpe\protocol\ModalFormResponsePacket;
use wolfcore\network\mcpe\protocol\MovePlayerPacket;
use wolfcore\network\mcpe\protocol\NetworkStackLatencyPacket;
use wolfcore\network\mcpe\protocol\PlayerActionPacket;
use wolfcore\network\mcpe\protocol\PlayerAuthInputPacket;
use wolfcore\network\mcpe\protocol\PlayerHotbarPacket;
use wolfcore\network\mcpe\protocol\PlayerInputPacket;
use wolfcore\network\mcpe\protocol\PlayerSkinPacket;
use wolfcore\network\mcpe\protocol\RequestAbilityPacket;
use wolfcore\network\mcpe\protocol\RefreshEntitlementsPacket;
use wolfcore\network\mcpe\protocol\PlayerToggleCrafterSlotRequestPacket;
use wolfcore\network\mcpe\protocol\SetPlayerInventoryOptionsPacket;
use wolfcore\network\mcpe\protocol\RequestChunkRadiusPacket;
use wolfcore\network\mcpe\protocol\ServerSettingsRequestPacket;
use wolfcore\network\mcpe\protocol\SetActorMotionPacket;
use wolfcore\network\mcpe\protocol\SetPlayerGameTypePacket;
use wolfcore\network\mcpe\protocol\ShowCreditsPacket;
use wolfcore\network\mcpe\protocol\SpawnExperienceOrbPacket;
use wolfcore\network\mcpe\protocol\SubClientLoginPacket;
use wolfcore\network\mcpe\protocol\TextPacket;
use wolfcore\network\mcpe\protocol\types\BlockPosition;
use wolfcore\network\mcpe\protocol\types\inventory\ContainerIds;
use wolfcore\network\mcpe\protocol\types\inventory\MismatchTransactionData;
use wolfcore\network\mcpe\protocol\types\inventory\NetworkInventoryAction;
use wolfcore\network\mcpe\protocol\types\inventory\NormalTransactionData;
use wolfcore\network\mcpe\protocol\types\inventory\ReleaseItemTransactionData;
use wolfcore\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequest;
use wolfcore\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponse;
use wolfcore\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use wolfcore\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use wolfcore\network\mcpe\protocol\types\LevelSoundEvent;
use wolfcore\network\mcpe\protocol\types\PlayerAction;
use wolfcore\network\mcpe\protocol\types\PlayerAuthInputFlags;
use wolfcore\network\mcpe\protocol\types\PlayerBlockActionStopBreak;
use wolfcore\network\mcpe\protocol\types\PlayerBlockActionWithBlockInfo;
use wolfcore\network\PacketHandlingException;
use wolfcore\player\Player;
use wolfcore\utils\AssumptionFailedError;
use wolfcore\utils\Limits;
use wolfcore\utils\TextFormat;
use wolfcore\utils\Utils;
use wolfcore\world\format\Chunk;
use wolfcore\world\sound\SwimSound;
use wolfcore\world\sound\WaterSplashSound;
use function array_push;
use function count;
use function fmod;
use function floor;
use function get_debug_type;
use function implode;
use function in_array;
use function is_bool;
use function is_infinite;
use function is_nan;
use function json_decode;
use function max;
use function mb_strlen;
use function microtime;
use function sprintf;
use function str_starts_with;
use function strlen;
use const JSON_THROW_ON_ERROR;

/**
 * This handler handles packets related to general gameplay.
 */
#[SilentDiscard(CraftingEventPacket::class, "Legacy crafting event is ignored by the default gameplay handler")]
#[SilentDiscard(RefreshEntitlementsPacket::class, "Client refresh notice has no server-side side effects")]
#[SilentDiscard(PlayerToggleCrafterSlotRequestPacket::class, "Protocol 630 crafter is not part of base 1.20.0 gameplay")]
#[SilentDiscard(SetPlayerInventoryOptionsPacket::class, "Protocol 630 inventory UI preference is client-only")]

class InGamePacketHandler extends PacketHandler{
	private const MAX_FORM_RESPONSE_SIZE = 10 * 1024; //10 KiB: prevent oversized form-response JSON
	private const MAX_FORM_RESPONSE_DEPTH = 2; //modal/simple will be 1, custom forms 2 - they will never contain anything other than string|int|float|bool|null

	protected float $lastRightClickTime = 0.0;
	protected ?UseItemTransactionData $lastRightClickData = null;

	protected ?Vector3 $lastPlayerAuthInputPosition = null;
	protected ?float $lastPlayerAuthInputYaw = null;
	protected ?float $lastPlayerAuthInputPitch = null;
	protected ?int $lastPlayerAuthInputFlags = null;

	/** De-duplicate multiple 594 swing signals from the same client tick. */
	private int $lastRemoteSwingTick = -100;
	/** Actual accepted server position, not an untrusted client position. */
	private ?Vector3 $lastWaterMotionPosition = null;
	private ?bool $wasTouchingWater = null;
	private int $lastSwimSoundTick = -100;
	private int $lastWaterEntrySoundTick = -100;

	/**
	 * If a 594 client only reports a jukebox music event, process the corresponding
	 * use on the authoritative 1.20.0 world, then ignore its delayed duplicate use.
	 */
	private ?BlockPosition $lastClientJukeboxFallbackPos = null;
	private int $lastClientJukeboxFallbackTick = -100;



	public bool $forceMoveSync = false;

	protected ?string $lastRequestedFullSkinId = null;

	public function __construct(
		private Player $player,
		private NetworkSession $session,
		private InventoryManager $inventoryManager
	){}

	public function handleText(TextPacket $packet) : bool{
		if($packet->type === TextPacket::TYPE_CHAT){
			return $this->player->chat($packet->message);
		}

		return false;
	}

	public function handleMovePlayer(MovePlayerPacket $packet) : bool{
		//The client sends this every time it lands on the ground, even when using PlayerAuthInputPacket.
		//Silence the debug spam that this causes.
		return true;
	}

	private function resolveOnOffInputFlags(int $inputFlags, int $startFlag, int $stopFlag) : ?bool{
		$enabled = ($inputFlags & (1 << $startFlag)) !== 0;
		$disabled = ($inputFlags & (1 << $stopFlag)) !== 0;
		if($enabled !== $disabled){
			return $enabled;
		}
		//neither flag was set, or both were set
		return null;
	}

	/**
	 * The 594 client can report an empty-hand miss in auth-input and/or a
	 * PlayerAction/Animate packet. Only the player is allowed to animate themself.
	 * Server-side block and entity attacks already broadcast their own swings.
	 */
	private function relayMissedSwing() : void{
		if($this->session->getProtocolVersion() < 594 || !$this->player->isAlive()){
			return;
		}
		$tick = $this->player->getServer()->getTick();
		if($tick - $this->lastRemoteSwingTick < 2){
			return;
		}
		$this->lastRemoteSwingTick = $tick;
		$this->player->broadcastAnimation(new ArmSwingAnimation($this->player), $this->player->getViewers());
	}

	/**
	 * Produce remote-player water movement audio without trusting arbitrary
	 * LevelSoundEventPacket payloads from clients. This core advertises
	 * server-authoritative audio in StartGame (disableClientSounds = true),
	 * so the moving player MUST receive their own water sound as well.
	 * The 589 base mechanics and movement collision remain unchanged.
	 */
	private function syncWaterMotionSounds() : void{
		$pos = $this->player->getPosition();
		$world = $this->player->getWorld();
		$inWater = $world->getBlockAt((int) floor($pos->x), (int) floor($pos->y), (int) floor($pos->z)) instanceof Water;
		$previous = $this->lastWaterMotionPosition;
		$wasInWater = $this->wasTouchingWater;
		$this->lastWaterMotionPosition = $pos->asVector3();
		$this->wasTouchingWater = $inWater;
		if($previous === null || !$this->player->isAlive() || $this->player->isSpectator()){
			return;
		}
		$distanceSquared = $previous->distanceSquared($pos);
		if($distanceSquared > 16){
			return; // position reset, teleport or lag spike: not a splash
		}
		$tick = $this->player->getServer()->getTick();
		$audience = $this->player->getViewers();
		$audience[] = $this->player; // server-authoritative audio disables local playback
		if($inWater && $wasInWater === false && ($previous->y - $pos->y) > 0.15 && $tick - $this->lastWaterEntrySoundTick >= 10){
			$this->lastWaterEntrySoundTick = $tick;
			$this->player->broadcastSound(new WaterSplashSound(0.5), $audience);
		}
		$horizontalSquared = ($pos->x - $previous->x) ** 2 + ($pos->z - $previous->z) ** 2;
		if($inWater && $horizontalSquared >= 0.0064 && $tick - $this->lastSwimSoundTick >= 7){
			$this->lastSwimSoundTick = $tick;
			$this->player->broadcastSound(new SwimSound(), $audience);
		}
	}

	public function handlePlayerAuthInput(PlayerAuthInputPacket $packet) : bool{
		$rawPos = $packet->getPosition();
		$rawYaw = $packet->getYaw();
		$rawPitch = $packet->getPitch();
		foreach([$rawPos->x, $rawPos->y, $rawPos->z, $rawYaw, $packet->getHeadYaw(), $rawPitch] as $float){
			if(is_infinite($float) || is_nan($float)){
				$this->session->getLogger()->debug("Invalid movement received, contains NAN/INF components");
				return false;
			}
		}

		if($rawYaw !== $this->lastPlayerAuthInputYaw || $rawPitch !== $this->lastPlayerAuthInputPitch){
			$this->lastPlayerAuthInputYaw = $rawYaw;
			$this->lastPlayerAuthInputPitch = $rawPitch;

			$yaw = fmod($rawYaw, 360);
			$pitch = fmod($rawPitch, 360);
			if($yaw < 0){
				$yaw += 360;
			}

			$this->player->setRotation($yaw, $pitch);
		}

		$hasMoved = $this->lastPlayerAuthInputPosition === null || !$this->lastPlayerAuthInputPosition->equals($rawPos);
		$newPos = $rawPos->round(4)->subtract(0, 1.62, 0);

		if($this->forceMoveSync && $hasMoved){
			$curPos = $this->player->getLocation();

			if($newPos->distanceSquared($curPos) > 1){  //Tolerate up to 1 block to avoid problems with client-sided physics when spawning in blocks
				$this->session->getLogger()->debug("Got outdated pre-teleport movement, received " . $newPos . ", expected " . $curPos);
				//Still getting movements from before teleport, ignore them
				return true;
			}

			// Once we get a movement within a reasonable distance, treat it as a teleport ACK and remove position lock
			$this->forceMoveSync = false;
		}

		$inputFlags = $packet->getInputFlags();
		// Read held controls on EVERY tick, independently from the one-shot
		// START/STOP_SNEAKING transition flags. Bedrock expects the dedicated
		// FALL_THROUGH_SCAFFOLDING actor flag while Shift is held.
		$this->player->setScaffoldingDescendPressed(
			$packet->hasFlag(PlayerAuthInputFlags::SNEAK_DOWN) ||
			$packet->hasFlag(PlayerAuthInputFlags::SNEAKING) ||
			$packet->hasFlag(PlayerAuthInputFlags::DESCEND_BLOCK) ||
			$packet->hasFlag(PlayerAuthInputFlags::WANT_DOWN)
		);
		if($inputFlags !== $this->lastPlayerAuthInputFlags){
			$this->lastPlayerAuthInputFlags = $inputFlags;

			$sneaking = $this->resolveOnOffInputFlags($inputFlags, PlayerAuthInputFlags::START_SNEAKING, PlayerAuthInputFlags::STOP_SNEAKING);
			// Client-authoritative Bedrock sends held sneak/scaffolding flags,
			// not necessarily a START_SNEAKING transition. DESCEND_BLOCK is
			// the dedicated scaffolding-down control. Honor these for Survival
			// as well as creative flight; otherwise the server still considers
			// the scaffold a floor and rejects descending movements.
			$heldSneak = $packet->hasFlag(PlayerAuthInputFlags::SNEAK_DOWN) ||
				$packet->hasFlag(PlayerAuthInputFlags::SNEAKING) ||
				$packet->hasFlag(PlayerAuthInputFlags::DESCEND_BLOCK) ||
				$packet->hasFlag(PlayerAuthInputFlags::WANT_DOWN);
			if($heldSneak){
				$sneaking = true;
			}elseif($packet->hasFlag(PlayerAuthInputFlags::STOP_SNEAKING) || $sneaking === null){
				$sneaking = false;
			}
			$sprinting = $this->resolveOnOffInputFlags($inputFlags, PlayerAuthInputFlags::START_SPRINTING, PlayerAuthInputFlags::STOP_SPRINTING);
			$swimming = $this->resolveOnOffInputFlags($inputFlags, PlayerAuthInputFlags::START_SWIMMING, PlayerAuthInputFlags::STOP_SWIMMING);
			$gliding = $this->resolveOnOffInputFlags($inputFlags, PlayerAuthInputFlags::START_GLIDING, PlayerAuthInputFlags::STOP_GLIDING);
			if($this->session->getProtocolVersion() >= 594){
				$crawling = $this->resolveOnOffInputFlags($inputFlags, PlayerAuthInputFlags::START_CRAWLING, PlayerAuthInputFlags::STOP_CRAWLING);
				if($crawling !== null){
					$this->player->setProtocol594Crawling($crawling);
				}
			}
			$mismatch =
				($sneaking !== null && !$this->player->toggleSneak($sneaking)) |
				($sprinting !== null && !$this->player->toggleSprint($sprinting)) |
				($swimming !== null && !$this->player->toggleSwim($swimming)) |
				($gliding !== null && !$this->player->toggleGlide($gliding));
			if((bool) $mismatch){
				$this->player->sendData([$this->player]);
			}

			if($packet->hasFlag(PlayerAuthInputFlags::START_JUMPING)){
				// The Bedrock client already simulates ladder-like ascent using
				// ASCEND_BLOCK. Injecting a full vanilla jump here forces the
				// player to pop upwards like a normal solid block.
				$feet = $this->player->getPosition();
				$insideScaffolding = $this->player->getWorld()->getBlock($feet) instanceof \wolfcore\block\Scaffolding;
				if(!$packet->hasFlag(PlayerAuthInputFlags::ASCEND_BLOCK) && !$insideScaffolding){
					$this->player->jump();
				}
			}
		}

		if($this->session->getProtocolVersion() >= 594 && $packet->hasFlag(PlayerAuthInputFlags::MISSED_SWING)){
			$this->relayMissedSwing();
		}

		if(!$this->forceMoveSync){
			// Simulate a stuck scaffolding descent at exactly the input tick that
			// will be corrected on the client. Don't wait for Player::onUpdate():
			// it is not synchronized with PlayerAuthInputPacket ticks and produced
			// the previous stair-step descent and repeated rewinds.
			$handledScaffoldingDescent = $this->player->tryScaffoldingDescent($newPos, $rawPos, $packet->getTick());
			if($hasMoved){
				$this->lastPlayerAuthInputPosition = $rawPos;
				if(!$handledScaffoldingDescent){
					//TODO: this packet has WAYYYYY more useful information that we're not using
					$this->player->handleMovement($newPos);
				}
				$this->syncWaterMotionSounds();
			}
		}

		$packetHandled = true;

		$blockActions = $packet->getBlockActions();
		if($blockActions !== null){
			if(count($blockActions) > 100){
				throw new PacketHandlingException("Too many block actions in PlayerAuthInputPacket");
			}
			foreach($blockActions as $k => $blockAction){
				$actionHandled = false;
				if($blockAction instanceof PlayerBlockActionStopBreak){
					$actionHandled = $this->handlePlayerActionFromData($blockAction->getActionType(), new BlockPosition(0, 0, 0), Facing::DOWN);
				}elseif($blockAction instanceof PlayerBlockActionWithBlockInfo){
					$actionHandled = $this->handlePlayerActionFromData($blockAction->getActionType(), $blockAction->getBlockPosition(), $blockAction->getFace());
				}

				if(!$actionHandled){
					$packetHandled = false;
					$this->session->getLogger()->debug("Unhandled player block action at offset $k in PlayerAuthInputPacket");
				}
			}
		}

		$useItemTransaction = $packet->getItemInteractionData();
		if($useItemTransaction !== null){
			if(count($useItemTransaction->getTransactionData()->getActions()) > 100){
				throw new PacketHandlingException("Too many actions in item use transaction");
			}

			$this->inventoryManager->setCurrentItemStackRequestId($useItemTransaction->getRequestId());
			$this->inventoryManager->addRawPredictedSlotChanges($useItemTransaction->getTransactionData()->getActions());
			if(!$this->handleUseItemTransaction($useItemTransaction->getTransactionData())){
				$packetHandled = false;
				$this->session->getLogger()->debug("Unhandled transaction in PlayerAuthInputPacket (type " . $useItemTransaction->getTransactionData()->getActionType() . ")");
			}else{
				$this->inventoryManager->syncMismatchedPredictedSlotChanges();
			}
			$this->inventoryManager->setCurrentItemStackRequestId(null);
		}

		$itemStackRequest = $packet->getItemStackRequest();
		if($itemStackRequest !== null){
			$result = $this->handleSingleItemStackRequest($itemStackRequest);
			$this->session->sendDataPacket(ItemStackResponsePacket::create([$result]));
		}

		return $packetHandled;
	}

	public function handleLevelSoundEventPacketV1(LevelSoundEventPacketV1 $packet) : bool{
		return true; //useless leftover from 1.8
	}

	public function handleActorEvent(ActorEventPacket $packet) : bool{
		return true; //Ignored: consumption animations are now triggered server-side only.
	}

	public function handleInventoryTransaction(InventoryTransactionPacket $packet) : bool{
		$result = true;

		if(count($packet->trData->getActions()) > 50){
			throw new PacketHandlingException("Too many actions in inventory transaction");
		}
		if(count($packet->requestChangedSlots) > 10){
			throw new PacketHandlingException("Too many slot sync requests in inventory transaction");
		}

		$this->inventoryManager->setCurrentItemStackRequestId($packet->requestId);
		$this->inventoryManager->addRawPredictedSlotChanges($packet->trData->getActions());

		if($packet->trData instanceof NormalTransactionData){
			$result = $this->handleNormalTransaction($packet->trData, $packet->requestId);
		}elseif($packet->trData instanceof MismatchTransactionData){
			$this->session->getLogger()->debug("Mismatch transaction received");
			$this->inventoryManager->requestSyncAll();
			$result = true;
		}elseif($packet->trData instanceof UseItemTransactionData){
			$result = $this->handleUseItemTransaction($packet->trData);
		}elseif($packet->trData instanceof UseItemOnEntityTransactionData){
			$result = $this->handleUseItemOnEntityTransaction($packet->trData);
		}elseif($packet->trData instanceof ReleaseItemTransactionData){
			$result = $this->handleReleaseItemTransaction($packet->trData);
		}

		$this->inventoryManager->syncMismatchedPredictedSlotChanges();

		//requestChangedSlots asks the server to always send out the contents of the specified slots, even if they
		//haven't changed. Handling these is necessary to ensure the client inventory stays in sync if the server
		//rejects the transaction. The most common example of this is equipping armor by right-click, which doesn't send
		//a legacy prediction action for the destination armor slot.
		foreach($packet->requestChangedSlots as $containerInfo){
			foreach($containerInfo->getChangedSlotIndexes() as $netSlot){
				[$windowId, $slot] = ItemStackContainerIdTranslator::translate($containerInfo->getContainerId(), $this->inventoryManager->getCurrentWindowId(), $netSlot);
				$inventoryAndSlot = $this->inventoryManager->locateWindowAndSlot($windowId, $slot);
				if($inventoryAndSlot !== null){ //trigger the normal slot sync logic
					$this->inventoryManager->onSlotChange($inventoryAndSlot[0], $inventoryAndSlot[1]);
				}
			}
		}

		$this->inventoryManager->setCurrentItemStackRequestId(null);
		return $result;
	}

	private function executeInventoryTransaction(InventoryTransaction $transaction, int $requestId) : bool{
		$this->player->setUsingItem(false);

		$this->inventoryManager->setCurrentItemStackRequestId($requestId);
		$this->inventoryManager->addTransactionPredictedSlotChanges($transaction);
		try{
			$transaction->execute();
		}catch(TransactionValidationException $e){
			$this->inventoryManager->requestSyncAll();
			$logger = $this->session->getLogger();
			$logger->debug("Invalid inventory transaction $requestId: " . $e->getMessage());

			return false;
		}catch(TransactionCancelledException){
			$this->session->getLogger()->debug("Inventory transaction $requestId cancelled by a plugin");

			return false;
		}finally{
			$this->inventoryManager->syncMismatchedPredictedSlotChanges();
			$this->inventoryManager->setCurrentItemStackRequestId(null);
		}

		return true;
	}

	private function handleNormalTransaction(NormalTransactionData $data, int $itemStackRequestId) : bool{
		//When the ItemStackRequest system is used, this transaction type is used for dropping items by pressing Q.
		//I don't know why they don't just use ItemStackRequest for that too, which already supports dropping items by
		//clicking them outside an open inventory menu, but for now it is what it is.
		//Fortunately, this means we can be much stricter about the validation criteria.

		$actionCount = count($data->getActions());
		if($actionCount > 2){
			if($actionCount > 5){
				throw new PacketHandlingException("Too many actions ($actionCount) in normal inventory transaction");
			}

			//Due to a bug in the game, this transaction type is still sent when a player edits a book. We don't need
			//these transactions for editing books, since we have BookEditPacket, so we can just ignore them.
			$this->session->getLogger()->debug("Ignoring normal inventory transaction with $actionCount actions (drop-item should have exactly 2 actions)");
			return false;
		}

		$sourceSlot = null;
		$clientItemStack = null;
		$droppedCount = null;

		foreach($data->getActions() as $networkInventoryAction){
			if($networkInventoryAction->sourceType === NetworkInventoryAction::SOURCE_WORLD && $networkInventoryAction->inventorySlot == NetworkInventoryAction::ACTION_MAGIC_SLOT_DROP_ITEM){
				$droppedCount = $networkInventoryAction->newItem->getItemStack()->getCount();
				if($droppedCount <= 0){
					throw new PacketHandlingException("Expected positive count for dropped item");
				}
			}elseif($networkInventoryAction->sourceType === NetworkInventoryAction::SOURCE_CONTAINER && $networkInventoryAction->windowId === ContainerIds::INVENTORY){
				//mobile players can drop an item from a non-selected hotbar slot
				$sourceSlot = $networkInventoryAction->inventorySlot;
				$clientItemStack = $networkInventoryAction->oldItem->getItemStack();
			}else{
				$this->session->getLogger()->debug("Unexpected inventory action type $networkInventoryAction->sourceType in drop item transaction");
				return false;
			}
		}
		if($sourceSlot === null || $clientItemStack === null || $droppedCount === null){
			$this->session->getLogger()->debug("Missing information in drop item transaction, need source slot, client item stack and dropped count");
			return false;
		}

		$inventory = $this->player->getInventory();

		if(!$inventory->slotExists($sourceSlot)){
			return false; //TODO: size desync??
		}

		$sourceSlotItem = $inventory->getItem($sourceSlot);
		if($sourceSlotItem->getCount() < $droppedCount){
			return false;
		}
		$serverItemStack = $this->session->getTypeConverter()->coreItemStackToNet($sourceSlotItem);
		//because the client doesn't tell us the expected itemstack ID, we have to deep-compare our known
		//itemstack info with the one the client sent. This is costly, but we don't have any other option :(
		if(!$serverItemStack->equals($clientItemStack)){
			return false;
		}

		//this modifies $sourceSlotItem
		$droppedItem = $sourceSlotItem->pop($droppedCount);

		$builder = new TransactionBuilder();
		$builder->getInventory($inventory)->setItem($sourceSlot, $sourceSlotItem);
		$builder->addAction(new DropItemAction($droppedItem));

		$transaction = new InventoryTransaction($this->player, $builder->generateActions());
		return $this->executeInventoryTransaction($transaction, $itemStackRequestId);
	}

	private function handleUseItemTransaction(UseItemTransactionData $data) : bool{
		$this->player->selectHotbarSlot($data->getHotbarSlot());

		// A 594 local jukebox sound can arrive before the matching item use.
		// The former was converted into a real, permission-checked interaction.
		// Suppress ONLY its matching delayed duplicate, not other clicks.
		if($this->session->getProtocolVersion() >= 594 &&
			$data->getActionType() === UseItemTransactionData::ACTION_CLICK_BLOCK &&
			$this->lastClientJukeboxFallbackPos !== null &&
			$this->lastClientJukeboxFallbackPos->equals($data->getBlockPosition()) &&
			$this->player->getServer()->getTick() - $this->lastClientJukeboxFallbackTick <= 3){
			$this->lastClientJukeboxFallbackPos = null;
			return true;
		}


		switch($data->getActionType()){
			case UseItemTransactionData::ACTION_CLICK_BLOCK:
				//TODO: start hack for client spam bug
				$clickPos = $data->getClickPosition();
				$spamBug = ($this->lastRightClickData !== null &&
					microtime(true) - $this->lastRightClickTime < 0.1 && //100ms
					$this->lastRightClickData->getPlayerPosition()->distanceSquared($data->getPlayerPosition()) < 0.00001 &&
					$this->lastRightClickData->getBlockPosition()->equals($data->getBlockPosition()) &&
					$this->lastRightClickData->getFace() === $data->getFace() &&
					$this->lastRightClickData->getHotbarSlot() === $data->getHotbarSlot() &&
					$this->lastRightClickData->getClickPosition()->distanceSquared($clickPos) < 0.00001 //signature spam bug has 0 distance, but allow some error
				);
				//get rid of continued spam if the player clicks and holds right-click
				$this->lastRightClickData = $data;
				$this->lastRightClickTime = microtime(true);
				if($spamBug){
					return true;
				}
				//TODO: end hack for client spam bug

				self::validateFacing($data->getFace());

				$blockPos = $data->getBlockPosition();
				$vBlockPos = new Vector3($blockPos->getX(), $blockPos->getY(), $blockPos->getZ());
				if(!$this->player->interactBlock($vBlockPos, $data->getFace(), $clickPos)){
					$this->onFailedBlockAction($vBlockPos, $data->getFace());
				}elseif($this->player->isCreative() && $this->player->isFlying()){
					// Confirm the server's actual blocks (including chest/hopper tile NBT)
					// to the hovering player, rather than leaving client prediction in
					// place until another adjacent block is interacted with.
					$this->syncFlightBlockPlacement($vBlockPos, $data->getFace());
				}
				return true;
			case UseItemTransactionData::ACTION_BREAK_BLOCK:
				$blockPos = $data->getBlockPosition();
				$vBlockPos = new Vector3($blockPos->getX(), $blockPos->getY(), $blockPos->getZ());
				if(!$this->player->breakBlock($vBlockPos)){
					$this->onFailedBlockAction($vBlockPos, null);
				}
				return true;
			case UseItemTransactionData::ACTION_CLICK_AIR:
				if($this->player->isUsingItem()){
					if(!$this->player->consumeHeldItem()){
						$hungerAttr = $this->player->getAttributeMap()->get(Attribute::HUNGER) ?? throw new AssumptionFailedError();
						$hungerAttr->markSynchronized(false);
					}
					return true;
				}
				$this->player->useHeldItem();
				return true;
		}

		return false;
	}

	/**
	 * @throws PacketHandlingException
	 */
	private static function validateFacing(int $facing) : void{
		if(!in_array($facing, Facing::ALL, true)){
			throw new PacketHandlingException("Invalid facing value $facing");
		}
	}

	/**
	 * Immediately acknowledge the authoritative block state to the creative
	 * flying placer. The ordinary world broadcast may arrive one tick later,
	 * which is too late for some clients' block-placement prediction.
	 */
	private function syncFlightBlockPlacement(Vector3 $blockPos, int $face) : void{
		if($blockPos->distanceSquared($this->player->getLocation()) >= 10000){
			return;
		}
		$positions = [$blockPos, $blockPos->getSide($face)];
		$this->syncAuthoritativeBlocks($positions);
	}

	/**
	 * Queues all block states together and flushes them in a single immediate
	 * packet batch. This avoids one compression/send per corrected block.
	 *
	 * @param Vector3[] $positions
	 */
	private function syncAuthoritativeBlocks(array $positions) : void{
		$packets = $this->player->getWorld()->createBlockUpdatePackets($positions);
		$lastIndex = count($packets) - 1;
		foreach($packets as $index => $packet){
			$this->session->sendDataPacket($packet, $index === $lastIndex);
		}
	}

	/**
	 * Internal function used to execute rollbacks when an action fails on a block.
	 */
	private function onFailedBlockAction(Vector3 $blockPos, ?int $face) : void{
		if($blockPos->distanceSquared($this->player->getLocation()) < 10000){
			$blocks = $blockPos->sidesArray();
			if($face !== null){
				$sidePos = $blockPos->getSide($face);

				/** @var Vector3[] $blocks */
				array_push($blocks, ...$sidePos->sidesArray()); //getAllSides() on each of these will include $blockPos and $sidePos because they are next to each other
			}else{
				$blocks[] = $blockPos;
			}
			$this->syncAuthoritativeBlocks($blocks);
		}
	}

	private function handleUseItemOnEntityTransaction(UseItemOnEntityTransactionData $data) : bool{
		$target = $this->player->getWorld()->getEntity($data->getActorRuntimeId());
		// Despawning entities remain in the entity table briefly: never attack or interact with them.
		if($target === null || $target->isFlaggedForDespawn()){
			return false;
		}

		$this->player->selectHotbarSlot($data->getHotbarSlot());

		switch($data->getActionType()){
			case UseItemOnEntityTransactionData::ACTION_INTERACT:
				$this->player->interactEntity($target, $data->getClickPosition());
				return true;
			case UseItemOnEntityTransactionData::ACTION_ATTACK:
				$this->player->attackEntity($target);
				return true;
		}

		return false;
	}

	private function handleReleaseItemTransaction(ReleaseItemTransactionData $data) : bool{
		$this->player->selectHotbarSlot($data->getHotbarSlot());

		if($data->getActionType() == ReleaseItemTransactionData::ACTION_RELEASE){
			$this->player->releaseHeldItem();
			return true;
		}

		return false;
	}

	private function handleSingleItemStackRequest(ItemStackRequest $request) : ItemStackResponse{
		if(count($request->getActions()) > 60){
			//recipe book auto crafting can affect all slots of the inventory when consuming inputs or producing outputs
			//this means there could be as many as 50 CraftingConsumeInput actions or Place (taking the result) actions
			//in a single request (there are certain ways items can be arranged which will result in the same stack
			//being taken from multiple times, but this is behaviour with a calculable limit)
			//this means there SHOULD be AT MOST 53 actions in a single request, but 60 is a nice round number.
			//n64Stacks = ?
			//n1Stacks = 45 - n64Stacks
			//nItemsRequiredFor1Craft = 9
			//nResults = floor((n1Stacks + (n64Stacks * 64)) / nItemsRequiredFor1Craft)
			//nTakeActionsTotal = floor(64 / nResults) + max(1, 64 % nResults) + ((nResults * nItemsRequiredFor1Craft) - (n64Stacks * 64))
			throw new PacketHandlingException("Too many actions in ItemStackRequest");
		}
		$executor = new ItemStackRequestExecutor($this->player, $this->inventoryManager, $request);
		try{
			$transaction = $executor->generateInventoryTransaction();
			$result = $this->executeInventoryTransaction($transaction, $request->getRequestId());
		}catch(ItemStackRequestProcessException $e){
			$result = false;
			$this->session->getLogger()->debug("ItemStackRequest #" . $request->getRequestId() . " failed: " . $e->getMessage());
			$this->session->getLogger()->debug(implode("\n", Utils::printableExceptionInfo($e)));
			$this->inventoryManager->requestSyncAll();
		}

		if(!$result){
			return new ItemStackResponse(ItemStackResponse::RESULT_ERROR, $request->getRequestId());
		}
		return $executor->buildItemStackResponse();
	}

	public function handleItemStackRequest(ItemStackRequestPacket $packet) : bool{
		$responses = [];
		if(count($packet->getRequests()) > 80){
			//TODO: we can probably lower this limit, but this will do for now
			throw new PacketHandlingException("Too many requests in ItemStackRequestPacket");
		}
		foreach($packet->getRequests() as $request){
			$responses[] = $this->handleSingleItemStackRequest($request);
		}

		$this->session->sendDataPacket(ItemStackResponsePacket::create($responses));

		return true;
	}

	public function handleMobEquipment(MobEquipmentPacket $packet) : bool{
		if($packet->windowId === ContainerIds::OFFHAND){
			return true; //this happens when we put an item into the offhand
		}
		if($packet->windowId === ContainerIds::INVENTORY){
			$this->inventoryManager->onClientSelectHotbarSlot($packet->hotbarSlot);
			if(!$this->player->selectHotbarSlot($packet->hotbarSlot)){
				$this->inventoryManager->syncSelectedHotbarSlot();
			}
			return true;
		}
		return false;
	}

	public function handleMobArmorEquipment(MobArmorEquipmentPacket $packet) : bool{
		return true; //Not used
	}

	public function handleInteract(InteractPacket $packet) : bool{
		if($packet->action === InteractPacket::ACTION_MOUSEOVER){
			//TODO HACK: silence useless spam (MCPE 1.8)
			//due to some messy Mojang hacks, it sends this when changing the held item now, which causes us to think
			//the inventory was closed when it wasn't.
			//this is also sent whenever entity metadata updates, which can get really spammy.
			//TODO: implement handling for this where it matters
			return true;
		}
		$target = $this->player->getWorld()->getEntity($packet->targetActorRuntimeId);
		if($target === null || $target->isFlaggedForDespawn()){
			return false;
		}
		if($packet->action === InteractPacket::ACTION_OPEN_INVENTORY && $target === $this->player){
			$this->inventoryManager->onClientOpenMainInventory();
			return true;
		}
		return false; //TODO
	}

	public function handleBlockPickRequest(BlockPickRequestPacket $packet) : bool{
		return $this->player->pickBlock(new Vector3($packet->blockPosition->getX(), $packet->blockPosition->getY(), $packet->blockPosition->getZ()), $packet->addUserData);
	}

	public function handleActorPickRequest(ActorPickRequestPacket $packet) : bool{
		return false; //TODO
	}

	public function handlePlayerAction(PlayerActionPacket $packet) : bool{
		return $this->handlePlayerActionFromData($packet->action, $packet->blockPosition, $packet->face);
	}

	private function handlePlayerActionFromData(int $action, BlockPosition $blockPosition, int $face) : bool{
		$pos = new Vector3($blockPosition->getX(), $blockPosition->getY(), $blockPosition->getZ());

		switch($action){
			case PlayerAction::START_BREAK:
				self::validateFacing($face);
				if(!$this->player->attackBlock($pos, $face)){
					$this->onFailedBlockAction($pos, $face);
				}

				break;

			case PlayerAction::ABORT_BREAK:
			case PlayerAction::STOP_BREAK:
				$this->player->stopBreakBlock($pos);
				break;
			case PlayerAction::START_SLEEPING:
				//unused
				break;
			case PlayerAction::STOP_SLEEPING:
				$this->player->stopSleep();
				break;
			case PlayerAction::CRACK_BREAK:
				self::validateFacing($face);
				$this->player->continueBreakBlock($pos, $face);
				break;
			case PlayerAction::INTERACT_BLOCK: //TODO: ignored (for now)
				break;
			case PlayerAction::CREATIVE_PLAYER_DESTROY_BLOCK:
				//TODO: do we need to handle this?
				break;
			// Protocol 594 sends missed swings and crawling pose changes on this
			// path as well as PlayerAuthInput. Keep them presentation-only, so
			// we do not accidentally cancel item use or change 1.20.0 physics.
			case PlayerAction::MISSED_SWING:
				$this->relayMissedSwing();
				return true;
			case PlayerAction::START_CRAWLING:
				if($this->session->getProtocolVersion() >= 594){
					$this->player->setProtocol594Crawling(true);
				}
				return true;
			case PlayerAction::STOP_CRAWLING:
				if($this->session->getProtocolVersion() >= 594){
					$this->player->setProtocol594Crawling(false);
				}
				return true;
			case PlayerAction::START_FLYING:
			case PlayerAction::STOP_FLYING:
				// 1.20.30+ emits these in addition to RequestAbilityPacket.
				// Apply normal server-side flight permission checks, never trust the client.
				if($this->session->getProtocolVersion() >= 618){
					$shouldFly = $action === PlayerAction::START_FLYING;
					if($shouldFly !== $this->player->isFlying() && !$this->player->toggleFlight($shouldFly)){
						$this->session->syncAbilities($this->player);
					}
				}
				return true;
			case PlayerAction::CLIENT_ACK_SERVER_DATA:
				// Client acknowledgement: no world or inventory mutation needed.
				return true;
			case PlayerAction::START_ITEM_USE_ON:
			case PlayerAction::STOP_ITEM_USE_ON:
				//TODO: this has no obvious use and seems only used for analytics in vanilla - ignore it
				break;
			default:
				$this->session->getLogger()->debug("Unhandled/unknown player action type " . $action);
				return false;
		}

		$this->player->setUsingItem(false);

		return true;
	}

	public function handleSetActorMotion(SetActorMotionPacket $packet) : bool{
		return true; //Not used: This packet is (erroneously) sent to the server when the client is riding a vehicle.
	}

	public function handleAnimate(AnimatePacket $packet) : bool{
		// Clients may also send this legacy animation, with their own runtime ID.
		// Never rebroadcast a client-supplied actor ID or double-play 594 misses.
		if($packet->action === AnimatePacket::ACTION_SWING_ARM && $packet->actorRuntimeId === $this->player->getId()){
			$this->relayMissedSwing();
		}
		return true;
	}

	public function handleContainerClose(ContainerClosePacket $packet) : bool{
		$this->inventoryManager->onClientRemoveWindow($packet->windowId);
		return true;
	}

	public function handlePlayerHotbar(PlayerHotbarPacket $packet) : bool{
		return true; //this packet is useless
	}

	public function handleBlockActorData(BlockActorDataPacket $packet) : bool{
		$pos = new Vector3($packet->blockPosition->getX(), $packet->blockPosition->getY(), $packet->blockPosition->getZ());
		if($pos->distanceSquared($this->player->getLocation()) > 10000){
			return false;
		}

		$block = $this->player->getLocation()->getWorld()->getBlock($pos);
		$nbt = $packet->nbt->getRoot();
		if(!($nbt instanceof CompoundTag)) throw new AssumptionFailedError("PHPStan should ensure this is a CompoundTag"); //for phpstorm's benefit

		if($block instanceof BaseSign){
			$frontTextTag = $nbt->getTag(Sign::TAG_FRONT_TEXT);
			if(!$frontTextTag instanceof CompoundTag){
				throw new PacketHandlingException("Invalid tag type " . get_debug_type($frontTextTag) . " for tag \"" . Sign::TAG_FRONT_TEXT . "\" in sign update data");
			}
			$textBlobTag = $frontTextTag->getTag(Sign::TAG_TEXT_BLOB);
			if(!$textBlobTag instanceof StringTag){
				throw new PacketHandlingException("Invalid tag type " . get_debug_type($textBlobTag) . " for tag \"" . Sign::TAG_TEXT_BLOB . "\" in sign update data");
			}

			// Bedrock signs have only four text lines; reject abnormally large untrusted blobs.
			if(strlen($textBlobTag->getValue()) > 16 * 1024){
				throw new PacketHandlingException("Sign text update exceeds 16384 bytes");
			}
			try{
				$text = SignText::fromBlob($textBlobTag->getValue());
			}catch(\InvalidArgumentException $e){
				throw PacketHandlingException::wrap($e, "Invalid sign text update");
			}

			try{
				if(!$block->updateText($this->player, $text)){
					foreach($this->player->getWorld()->createBlockUpdatePackets([$pos]) as $updatePacket){
						$this->session->sendDataPacket($updatePacket);
					}
				}
			}catch(\UnexpectedValueException $e){
				throw PacketHandlingException::wrap($e);
			}

			return true;
		}

		return false;
	}

	public function handlePlayerInput(PlayerInputPacket $packet) : bool{
		return false; //TODO
	}

	public function handleSetPlayerGameType(SetPlayerGameTypePacket $packet) : bool{
		$gameMode = $this->session->getTypeConverter()->protocolGameModeToCore($packet->gamemode);
		if($gameMode === null || !$gameMode->equals($this->player->getGamemode())){
			//Set this back to default. TODO: handle this properly
			$this->session->syncGameMode($this->player->getGamemode(), true);
		}
		return true;
	}

	public function handleSpawnExperienceOrb(SpawnExperienceOrbPacket $packet) : bool{
		return false; //TODO
	}

	public function handleMapInfoRequest(MapInfoRequestPacket $packet) : bool{
		return false; //TODO
	}

	public function handleRequestChunkRadius(RequestChunkRadiusPacket $packet) : bool{
		$this->player->setViewDistance($packet->radius);

		return true;
	}

	public function handleItemFrameDropItem(ItemFrameDropItemPacket $packet) : bool{
		$blockPosition = $packet->blockPosition;
		$block = $this->player->getWorld()->getBlockAt($blockPosition->getX(), $blockPosition->getY(), $blockPosition->getZ());
		if($block instanceof ItemFrame && $block->getFramedItem() !== null){
			return $this->player->attackBlock(new Vector3($blockPosition->getX(), $blockPosition->getY(), $blockPosition->getZ()), $block->getFacing());
		}
		return false;
	}

	public function handleBossEvent(BossEventPacket $packet) : bool{
		return false; //TODO
	}

	public function handleShowCredits(ShowCreditsPacket $packet) : bool{
		return false; //TODO: handle resume
	}

	public function handleCommandRequest(CommandRequestPacket $packet) : bool{
		if(str_starts_with($packet->command, '/')){
			$this->player->chat($packet->command);
			return true;
		}
		return false;
	}

	public function handleCommandBlockUpdate(CommandBlockUpdatePacket $packet) : bool{
		return false; //TODO
	}

	public function handlePlayerSkin(PlayerSkinPacket $packet) : bool{
		if($packet->skin->getFullSkinId() === $this->lastRequestedFullSkinId){
			//TODO: HACK! In 1.19.60, the client sends its skin back to us if we sent it a skin different from the one
			//it's using. We need to prevent this from causing a feedback loop.
			$this->session->getLogger()->debug("Refused duplicate skin change request");
			return true;
		}
		$this->lastRequestedFullSkinId = $packet->skin->getFullSkinId();

		$this->session->getLogger()->debug("Processing skin change request");
		try{
			$skin = $this->session->getTypeConverter()->getSkinAdapter()->fromSkinData($packet->skin);
		}catch(\InvalidArgumentException | InvalidSkinException | \JsonException $e){
			$this->session->getLogger()->debug("Skin change has no usable texture: " . $e->getMessage() . "; using default skin");
			$skin = DefaultSkinFactory::fromIdentity($this->player->getUniqueId()->toString());
		}
		return $this->player->changeSkin($skin, $packet->newSkinName, $packet->oldSkinName);
	}

	public function handleSubClientLogin(SubClientLoginPacket $packet) : bool{
		return false; //TODO
	}

	/**
	 * @throws PacketHandlingException
	 */
	private function checkBookText(string $string, string $fieldName, int $softLimit, int $hardLimit, bool &$cancel) : string{
		if(strlen($string) > $hardLimit){
			throw new PacketHandlingException(sprintf("Book %s must be at most %d bytes, but have %d bytes", $fieldName, $hardLimit, strlen($string)));
		}

		$result = TextFormat::clean($string, false);
		//strlen() is O(1), mb_strlen() is O(n)
		if(strlen($result) > $softLimit * 4 || mb_strlen($result, 'UTF-8') > $softLimit){
			$cancel = true;
			$this->session->getLogger()->debug("Cancelled book edit due to $fieldName exceeded soft limit of $softLimit chars");
		}

		return $result;
	}

	public function handleBookEdit(BookEditPacket $packet) : bool{
		$inventory = $this->player->getInventory();
		if(!$inventory->slotExists($packet->inventorySlot)){
			return false;
		}
		//TODO: break this up into book API things
		$oldBook = $inventory->getItem($packet->inventorySlot);
		if(!($oldBook instanceof WritableBook)){
			return false;
		}

		$newBook = clone $oldBook;
		$modifiedPages = [];
		$cancel = false;
		switch($packet->type){
			case BookEditPacket::TYPE_REPLACE_PAGE:
				$text = self::checkBookText($packet->text, "page text", 256, WritableBookPage::PAGE_LENGTH_HARD_LIMIT_BYTES, $cancel);
				$newBook->setPageText($packet->pageNumber, $text);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_ADD_PAGE:
				if(!$newBook->pageExists($packet->pageNumber)){
					//this may only come before a page which already exists
					//TODO: the client can send insert-before actions on trailing client-side pages which cause odd behaviour on the server
					return false;
				}
				$text = self::checkBookText($packet->text, "page text", 256, WritableBookPage::PAGE_LENGTH_HARD_LIMIT_BYTES, $cancel);
				$newBook->insertPage($packet->pageNumber, $text);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_DELETE_PAGE:
				if(!$newBook->pageExists($packet->pageNumber)){
					return false;
				}
				$newBook->deletePage($packet->pageNumber);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_SWAP_PAGES:
				if(!$newBook->pageExists($packet->pageNumber) || !$newBook->pageExists($packet->secondaryPageNumber)){
					//the client will create pages on its own without telling us until it tries to switch them
					$newBook->addPage(max($packet->pageNumber, $packet->secondaryPageNumber));
				}
				$newBook->swapPages($packet->pageNumber, $packet->secondaryPageNumber);
				$modifiedPages = [$packet->pageNumber, $packet->secondaryPageNumber];
				break;
			case BookEditPacket::TYPE_SIGN_BOOK:
				$title = self::checkBookText($packet->title, "title", 16, Limits::INT16_MAX, $cancel);
				//this one doesn't have a limit in vanilla, so we have to improvise
				$author = self::checkBookText($packet->author, "author", 256, Limits::INT16_MAX, $cancel);

				$newBook = VanillaItems::WRITTEN_BOOK()
					->setPages($oldBook->getPages())
					->setAuthor($author)
					->setTitle($title)
					->setGeneration(WrittenBook::GENERATION_ORIGINAL);
				break;
			default:
				return false;
		}

		//for redundancy, in case of protocol changes, we don't want to pass these directly
		$action = match($packet->type){
			BookEditPacket::TYPE_REPLACE_PAGE => PlayerEditBookEvent::ACTION_REPLACE_PAGE,
			BookEditPacket::TYPE_ADD_PAGE => PlayerEditBookEvent::ACTION_ADD_PAGE,
			BookEditPacket::TYPE_DELETE_PAGE => PlayerEditBookEvent::ACTION_DELETE_PAGE,
			BookEditPacket::TYPE_SWAP_PAGES => PlayerEditBookEvent::ACTION_SWAP_PAGES,
			BookEditPacket::TYPE_SIGN_BOOK => PlayerEditBookEvent::ACTION_SIGN_BOOK,
			default => throw new AssumptionFailedError("We already filtered unknown types in the switch above")
		};

		/*
		 * Plugins may have created books with more than 50 pages; we allow plugins to do this, but not players.
		 * Don't allow the page count to grow past 50, but allow deleting, swapping or altering text of existing pages.
		 */
		$oldPageCount = count($oldBook->getPages());
		$newPageCount = count($newBook->getPages());
		if(($newPageCount > $oldPageCount && $newPageCount > 50)){
			$this->session->getLogger()->debug("Cancelled book edit due to adding too many pages (new page count would be $newPageCount)");
			$cancel = true;
		}

		$event = new PlayerEditBookEvent($this->player, $oldBook, $newBook, $action, $modifiedPages);
		if($cancel){
			$event->cancel();
		}

		$event->call();
		if($event->isCancelled()){
			return true;
		}

		$this->player->getInventory()->setItem($packet->inventorySlot, $event->getNewBook());

		return true;
	}

	public function handleModalFormResponse(ModalFormResponsePacket $packet) : bool{
		if($packet->cancelReason !== null){
			//TODO: make APIs for this to allow plugins to use this information
			return $this->player->onFormSubmit($packet->formId, null);
		}elseif($packet->formData !== null){
			if(strlen($packet->formData) > self::MAX_FORM_RESPONSE_SIZE){
				throw new PacketHandlingException("Form response data too large, refusing to decode (received " . strlen($packet->formData) . " bytes, max " . self::MAX_FORM_RESPONSE_SIZE . " bytes)");
			}
			if(!$this->player->hasPendingForm($packet->formId)){
				$this->session->getLogger()->debug("Got unexpected response for form $packet->formId");
				return false;
			}
			try{
				$responseData = json_decode($packet->formData, true, self::MAX_FORM_RESPONSE_DEPTH, JSON_THROW_ON_ERROR);
			}catch(\JsonException $e){
				throw PacketHandlingException::wrap($e, "Failed to decode form response data");
			}
			return $this->player->onFormSubmit($packet->formId, $responseData);
		}else{
			throw new PacketHandlingException("Expected either formData or cancelReason to be set in ModalFormResponsePacket");
		}
	}

	public function handleServerSettingsRequest(ServerSettingsRequestPacket $packet) : bool{
		return false; //TODO: GUI stuff
	}

	public function handleLabTable(LabTablePacket $packet) : bool{
		return false; //TODO
	}

	public function handleLecternUpdate(LecternUpdatePacket $packet) : bool{
		if($packet->dropBook){
			//Drop book is handled with an interact event on use item transaction
			return true;
		}

		$pos = $packet->blockPosition;
		$chunkX = $pos->getX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $pos->getZ() >> Chunk::COORD_BIT_SIZE;
		$world = $this->player->getWorld();
		if(!$world->isChunkLoaded($chunkX, $chunkZ) || $world->isChunkLocked($chunkX, $chunkZ)){
			return false;
		}

		$lectern = $world->getBlockAt($pos->getX(), $pos->getY(), $pos->getZ());
		if($lectern instanceof Lectern && $this->player->canInteract($lectern->getPosition(), 15)){
			if(!$lectern->onPageTurn($packet->page)){
				$this->onFailedBlockAction($lectern->getPosition(), null);
			}
			return true;
		}

		return false;
	}

	public function handleNetworkStackLatency(NetworkStackLatencyPacket $packet) : bool{
		return true; //TODO: implement this properly - this is here to silence debug spam from MCPE dev builds
	}

	public function handleLevelSoundEvent(LevelSoundEventPacket $packet) : bool{
		// The 594 client may optimistically start a music disc locally and only
		// report the record's LevelSoundEvent. Other clients then hear nothing,
		// because ordinary client sound events are deliberately NOT forwarded.
		// For records ONLY, turn that signal into a normal server interaction.
		// Never forward arbitrary client audio or trust a claimed disc identity.
		if($this->session->getProtocolVersion() < 594 || !$this->player->isAlive() || $this->player->isSpectator()){
			return true;
		}
		$isStop = $packet->sound === LevelSoundEvent::STOP_RECORD;
		$heldRecord = $this->player->getInventory()->getItemInHand();
		$isMatchingStart = $heldRecord instanceof Record && $heldRecord->getRecordType()->getSoundId() === $packet->sound;
		if(!$isStop && !$isMatchingStart){
			return true;
		}

		// Do not load distant chunks or let an untrusted audio position act as a
		// remote block-use command. The actual Player::interactBlock() repeats the
		// regular reach, gamemode, plugin-event and inventory checks.
		$pos = $packet->position;
		if(!is_finite($pos->x) || !is_finite($pos->y) || !is_finite($pos->z)){
			return true;
		}
		$blockPos = new BlockPosition((int) floor($pos->x), (int) floor($pos->y), (int) floor($pos->z));
		$blockVector = new Vector3($blockPos->getX(), $blockPos->getY(), $blockPos->getZ());
		$world = $this->player->getWorld();
		if(!$world->isChunkLoaded($blockPos->getX() >> 4, $blockPos->getZ() >> 4) ||
			!$this->player->canInteract($blockVector->add(0.5, 0.5, 0.5), $this->player->isCreative() ? 13 : 7)){
			return true;
		}
		$jukebox = $world->getBlockAt($blockPos->getX(), $blockPos->getY(), $blockPos->getZ());
		if(!$jukebox instanceof Jukebox || ($isStop ? $jukebox->getRecord() === null : $jukebox->getRecord() !== null)){
			// Already processed by the normal use-item path, or not a jukebox.
			// In particular, do not replay a record which is already running.
			return true;
		}
		$tick = $this->player->getServer()->getTick();
		if($this->lastClientJukeboxFallbackPos !== null && $tick - $this->lastClientJukeboxFallbackTick < 4 &&
			$this->lastClientJukeboxFallbackPos->equals($blockPos)){
			return true;
		}
		if($this->player->interactBlock($blockVector, Facing::UP, new Vector3(0.5, 0.5, 0.5))){
			$this->lastClientJukeboxFallbackPos = $blockPos;
			$this->lastClientJukeboxFallbackTick = $tick;
		}
		return true;
	}

	public function handleEmote(EmotePacket $packet) : bool{
		$this->player->emote($packet->getEmoteId());
		return true;
	}

	public function handleEmoteList(EmoteListPacket $packet) : bool{
		// Client-provided emote inventory is not used by the server.
		// Consume it without forwarding, changing state, or logging it as unhandled.
		return true;
	}

	public function handleRequestAbility(RequestAbilityPacket $packet) : bool{
		if($packet->getAbilityId() === RequestAbilityPacket::ABILITY_FLYING){
			$isFlying = $packet->getAbilityValue();
			if(!is_bool($isFlying)){
				throw new PacketHandlingException("Flying ability should always have a bool value");
			}
			if($isFlying !== $this->player->isFlying()){
				if(!$this->player->toggleFlight($isFlying)){
					$this->session->syncAbilities($this->player);
				}
			}

			return true;
		}

		return false;
	}
}
