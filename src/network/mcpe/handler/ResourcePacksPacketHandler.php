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

use wolfcore\lang\KnownTranslationFactory;
use wolfcore\network\mcpe\NetworkSession;
use wolfcore\network\mcpe\protocol\ProtocolInfo;
use wolfcore\network\mcpe\protocol\ResourcePackChunkDataPacket;
use wolfcore\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use wolfcore\network\mcpe\protocol\ResourcePackClientResponsePacket;
use wolfcore\network\mcpe\protocol\ResourcePackDataInfoPacket;
use wolfcore\network\mcpe\protocol\ResourcePacksInfoPacket;
use wolfcore\network\mcpe\protocol\ResourcePackStackPacket;
use wolfcore\network\mcpe\protocol\types\Experiments;
use wolfcore\network\mcpe\protocol\types\resourcepacks\ResourcePackInfoEntry;
use wolfcore\network\mcpe\protocol\types\resourcepacks\ResourcePackStackEntry;
use wolfcore\network\mcpe\protocol\types\resourcepacks\ResourcePackType;
use wolfcore\network\PacketHandlingException;
use wolfcore\resourcepacks\ResourcePack;
use wolfcore\resourcepacks\ResourcePackManager;
use function array_map;
use function ceil;
use function count;
use function preg_match;
use function sprintf;
use function strlen;
use function strpos;
use function substr;

/**
 * Handler used for the resource packs sequence phase of the session. This handler takes care of downloading resource
 * packs to the client.
 */
class ResourcePacksPacketHandler extends PacketHandler{
	private const PACK_CHUNK_SIZE = 128 * 1024; //128KB
	private const MAX_PACK_IDENTIFIER_LENGTH = 128; //UUID plus optional Bedrock version suffix
	private const PACK_UUID_PATTERN = "/\A[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}\z/i";
	private const VERSIONED_PACK_UUID_PATTERN = "/\A[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}(?:_[a-z0-9][a-z0-9._+-]{0,90})?\z/i";

	// Resource pack negotiation must follow the server -> client -> server handshake.
	private const STATE_INITIAL = 0;
	private const STATE_INFO_SENT = 1;
	private const STATE_DOWNLOAD_REQUESTED = 2;
	private const STATE_STACK_SENT = 3;
	private const STATE_COMPLETED = 4;

	private int $negotiationState = self::STATE_INITIAL;

	/** @var bool[][] uuid => [chunk index => hasSent] */
	private array $downloadedChunks = [];

	/**
	 * @phpstan-param \Closure() : void $completionCallback
	 */
	public function __construct(
		private NetworkSession $session,
		private ResourcePackManager $resourcePackManager,
		private \Closure $completionCallback
	){}

	public function setUp() : void{
		$resourcePackEntries = array_map(function(ResourcePack $pack) : ResourcePackInfoEntry{
			//TODO: more stuff
			$encryptionKey = $this->resourcePackManager->getPackEncryptionKey($pack->getPackId());

			return new ResourcePackInfoEntry(
				$pack->getPackId(),
				$pack->getPackVersion(),
				$pack->getPackSize(),
				$encryptionKey ?? "",
				"",
				$pack->getPackId(),
				false
			);
		}, $this->resourcePackManager->getResourceStack());
		//TODO: support forcing server packs
		if(!$this->session->sendDataPacket(ResourcePacksInfoPacket::create($resourcePackEntries, [], $this->resourcePackManager->resourcePacksRequired(), false, false))){
			$this->disconnectWithError("Failed to send resource pack information");
			return;
		}
		$this->negotiationState = self::STATE_INFO_SENT;
		$this->session->getLogger()->debug("Waiting for client to accept resource packs");
	}

	private function disconnectWithError(string $error) : void{
		$this->session->getLogger()->error("Error downloading resource packs: " . $error);
		$this->session->disconnectWithError(KnownTranslationFactory::disconnectionScreen_resourcePack());
	}

	/**
	 * Reject untrusted pack IDs before lookup and never put unchecked client strings in log messages.
	 * Metadata requests may include the Bedrock UUID_version suffix; chunk requests may not.
	 */
	private static function isValidPackIdentifier(string $id, bool $allowVersionSuffix) : bool{
		return strlen($id) <= ($allowVersionSuffix ? self::MAX_PACK_IDENTIFIER_LENGTH : 36)
			&& preg_match($allowVersionSuffix ? self::VERSIONED_PACK_UUID_PATTERN : self::PACK_UUID_PATTERN, $id) === 1;
	}

	public function handleResourcePackClientResponse(ResourcePackClientResponsePacket $packet) : bool{
		switch($packet->status){
			case ResourcePackClientResponsePacket::STATUS_REFUSED:
				//TODO: add lang strings for this
				$this->session->disconnect("Refused resource packs", "You must accept resource packs to join this server.", true);
				break;
			case ResourcePackClientResponsePacket::STATUS_SEND_PACKS:
				if($this->negotiationState !== self::STATE_INFO_SENT){
					throw new PacketHandlingException("Resource pack metadata requested outside the initial negotiation stage");
				}

				$availablePackCount = count($this->resourcePackManager->getPackIdList());
				if(count($packet->packIds) > $availablePackCount){
					throw new PacketHandlingException(sprintf("Requested metadata for more resource packs (%d) than available on the server (%d)", count($packet->packIds), $availablePackCount));
				}

				$seen = [];
				foreach($packet->packIds as $uuid){
					if(!self::isValidPackIdentifier($uuid, true)){
						$this->disconnectWithError("Invalid resource pack identifier in metadata request");
						return false;
					}
					//dirty hack for mojang's dirty hack for versions
					$splitPos = strpos($uuid, "_");
					if($splitPos !== false){
						$uuid = substr($uuid, 0, $splitPos);
					}
					$pack = $this->resourcePackManager->getPackById($uuid);

					if(!($pack instanceof ResourcePack)){
						//Client requested a resource pack but we don't have it available on the server
						$this->disconnectWithError("Unknown resource pack requested");
						return false;
					}
					if(isset($seen[$pack->getPackId()])){
						throw new PacketHandlingException("Repeated metadata request for resource pack");
					}

					if(!$this->session->sendDataPacket(ResourcePackDataInfoPacket::create(
						$pack->getPackId(),
						self::PACK_CHUNK_SIZE,
						(int) ceil($pack->getPackSize() / self::PACK_CHUNK_SIZE),
						$pack->getPackSize(),
						$pack->getSha256(),
						false,
						ResourcePackType::RESOURCES //TODO: this might be an addon (not behaviour pack), needed to properly support client-side custom items
					))){
						$this->disconnectWithError("Failed to send resource pack metadata");
						return false;
					}
					$seen[$pack->getPackId()] = true;
				}
				$this->negotiationState = self::STATE_DOWNLOAD_REQUESTED;
				$this->session->getLogger()->debug("Player requested download of " . count($packet->packIds) . " resource packs");

				break;
			case ResourcePackClientResponsePacket::STATUS_HAVE_ALL_PACKS:
				if($this->negotiationState !== self::STATE_INFO_SENT && $this->negotiationState !== self::STATE_DOWNLOAD_REQUESTED){
					throw new PacketHandlingException("Resource pack stack requested outside the pack selection stage");
				}

				$stack = array_map(static function(ResourcePack $pack) : ResourcePackStackEntry{
					return new ResourcePackStackEntry($pack->getPackId(), $pack->getPackVersion(), ""); //TODO: subpacks
				}, $this->resourcePackManager->getResourceStack());
				//we don't force here, because it doesn't have user-facing effects
				//but it does have an annoying side-effect when true: it makes
				//the client remove its own non-server-supplied resource packs.
				if(!$this->session->sendDataPacket(ResourcePackStackPacket::create($stack, [], false, $this->session->getMinecraftVersionNetwork(), new Experiments([], false)))){
					$this->disconnectWithError("Failed to send resource pack stack");
					return false;
				}
				$this->negotiationState = self::STATE_STACK_SENT;
				$this->session->getLogger()->debug("Applying resource pack stack");
				break;
			case ResourcePackClientResponsePacket::STATUS_COMPLETED:
				// The client may complete only after the server has actually sent the pack stack.
				// The callback must be invoked at most once per network session.
				if($this->negotiationState === self::STATE_COMPLETED || $this->session->getHandler() !== $this){
					break;
				}
				if($this->negotiationState !== self::STATE_STACK_SENT){
					throw new PacketHandlingException("Cannot complete resource pack negotiation before the pack stack is sent");
				}
				$this->negotiationState = self::STATE_COMPLETED;
				$this->session->getLogger()->debug("Resource packs sequence completed");
				$this->session->setHandler(null);
				($this->completionCallback)();
				break;
			default:
				return false;
		}

		return true;
	}

	public function handleResourcePackChunkRequest(ResourcePackChunkRequestPacket $packet) : bool{
		if($this->negotiationState !== self::STATE_DOWNLOAD_REQUESTED){
			throw new PacketHandlingException("Resource pack chunk requested outside the download stage");
		}
		if(!self::isValidPackIdentifier($packet->packId, false)){
			$this->disconnectWithError("Invalid resource pack identifier in chunk request");
			return false;
		}

		$pack = $this->resourcePackManager->getPackById($packet->packId);
		if(!($pack instanceof ResourcePack)){
			$this->disconnectWithError("Invalid request for chunk $packet->chunkIndex of unknown resource pack");
			return false;
		}

		$packId = $pack->getPackId(); //use this because case may be different

		if(isset($this->downloadedChunks[$packId][$packet->chunkIndex])){
			$this->disconnectWithError("Duplicate request for chunk $packet->chunkIndex of pack $packId");
			return false;
		}

		$offset = $packet->chunkIndex * self::PACK_CHUNK_SIZE;
		if($offset < 0 || $offset >= $pack->getPackSize()){
			$this->disconnectWithError("Invalid out-of-bounds request for chunk $packet->chunkIndex of $packId: offset $offset, file size " . $pack->getPackSize());
			return false;
		}

		if(!isset($this->downloadedChunks[$packId])){
			$this->downloadedChunks[$packId] = [$packet->chunkIndex => true];
		}else{
			$this->downloadedChunks[$packId][$packet->chunkIndex] = true;
		}

		$this->session->sendDataPacket(ResourcePackChunkDataPacket::create($packId, $packet->chunkIndex, $offset, $pack->getPackChunk($offset, self::PACK_CHUNK_SIZE)));

		return true;
	}
}
