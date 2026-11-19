<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace wolfcore\network\mcpe\protocol;

use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ShowStoreOfferPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::SHOW_STORE_OFFER_PACKET;

	public string $offerId;
	public bool $showAll = false; // legacy 589..622
	public int $redirectType = 0; // 630: 0 marketplace, 1 dressing room, 2 server page

	/**
	 * @generate-create-func
	 */
	public static function create(string $offerId, bool $showAll) : self{
		$result = new self;
		$result->offerId = $offerId;
		$result->showAll = $showAll;
		return $result;
	}

    /** New 630 factory; legacy create(string,bool) is unchanged. */
    public static function createWithRedirect(UuidInterface $offerId, int $redirectType) : self{
        if($redirectType < 0 || $redirectType > 2){
            throw new \InvalidArgumentException('Invalid store offer redirect type');
        }
        $pk = new self();
        $pk->offerId = $offerId->toString();
        $pk->redirectType = $redirectType;
        return $pk;
    }

    protected function decodePayload(PacketSerializer $in) : void{
        if($in->getProtocolVersion() >= 630){
            $this->offerId = $in->getUUID()->toString();
            $this->redirectType = $in->getByte();
            if($this->redirectType > 2){
                throw new PacketDecodeException('Invalid store offer redirect type ' . $this->redirectType);
            }
        }else{
            $this->offerId = $in->getString();
            $this->showAll = $in->getBool();
        }
    }

    protected function encodePayload(PacketSerializer $out) : void{
        if($out->getProtocolVersion() >= 630){
            if($this->redirectType < 0 || $this->redirectType > 2){
                throw new \InvalidArgumentException('Invalid store offer redirect type');
            }
            if(!Uuid::isValid($this->offerId)){
                throw new \InvalidArgumentException(
                    'Protocol 630 ShowStoreOfferPacket requires a UUID offer ID; use createWithRedirect() with a UUID, not a legacy string offer ID'
                );
            }
            $out->putUUID(Uuid::fromString($this->offerId));
            $out->putByte($this->redirectType);
        }else{
            $out->putString($this->offerId);
            $out->putBool($this->showAll);
        }
    }

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleShowStoreOffer($this);
	}
}
