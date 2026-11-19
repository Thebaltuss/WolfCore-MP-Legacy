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
use wolfcore\network\mcpe\protocol\types\login\JwtChain;
use wolfcore\utils\BinaryStream;
use function is_object;
use function json_decode;
use function json_encode;
use function json_last_error_msg;
use function strlen;
use const JSON_THROW_ON_ERROR;

class LoginPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::LOGIN_PACKET;

	// Legitimate Bedrock identity chains normally contain 1-3 JWTs. Allow
	// headroom for future formats while bounding parsing and signature checks.
	public const MAX_CHAIN_JWTS = 8;
	public const MAX_CHAIN_JWT_LENGTH = 16384;
	public const MAX_CHAIN_JWTS_TOTAL_LENGTH = 65536;

	public int $protocol;
	public JwtChain $chainDataJwt;
	public string $clientDataJwt;

	/**
	 * @generate-create-func
	 */
	public static function create(int $protocol, JwtChain $chainDataJwt, string $clientDataJwt) : self{
		$result = new self;
		$result->protocol = $protocol;
		$result->chainDataJwt = $chainDataJwt;
		$result->clientDataJwt = $clientDataJwt;
		return $result;
	}

	public function canBeSentBeforeLogin() : bool{
		return true;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->protocol = $in->getInt();
		$this->decodeConnectionRequest($in->getString());
	}

	protected function decodeConnectionRequest(string $binary) : void{
		$connRequestReader = new BinaryStream($binary);

		$chainDataJsonLength = $connRequestReader->getLInt();
		// Limit memory consumption before reading or decoding client-controlled JSON.
		if($chainDataJsonLength <= 0 || $chainDataJsonLength > 1048576){
			throw new PacketDecodeException("Invalid chain data JSON length");
		}
		$chainDataJson = json_decode($connRequestReader->get($chainDataJsonLength));
		if(!is_object($chainDataJson)){
			throw new PacketDecodeException("Failed decoding chain data JSON: " . json_last_error_msg());
		}

		// Only the 'chain' property is part of the chainData schema. Validate
		// keys BEFORE JsonMapper sees them: its exceptions otherwise contain
		// the entire attacker-controlled property name.
		$properties = get_object_vars($chainDataJson);
		if(count($properties) > 16){
			throw new PacketDecodeException("Too many chain data JSON properties");
		}
		foreach($properties as $propertyName => $_){
			if(strlen($propertyName) > 80 || $propertyName !== "chain"){
				throw new PacketDecodeException("Unexpected chain data JSON property");
			}
		}

		if(!isset($chainDataJson->chain) || !is_array($chainDataJson->chain) || count($chainDataJson->chain) < 1 || count($chainDataJson->chain) > self::MAX_CHAIN_JWTS){
			throw new PacketDecodeException("Invalid JWT chain in chain data JSON");
		}
		// Reject oversized chain JWTs before JsonMapper, JWT parsing and signature
		// verification. A bounded total also limits cryptographic input volume.
		$totalJwtLength = 0;
		foreach($chainDataJson->chain as $jwt){
			if(!is_string($jwt)){
				throw new PacketDecodeException("Invalid JWT chain entry: expected string");
			}
			$jwtLength = strlen($jwt);
			if($jwtLength === 0 || $jwtLength > self::MAX_CHAIN_JWT_LENGTH || $totalJwtLength > self::MAX_CHAIN_JWTS_TOTAL_LENGTH - $jwtLength){
				throw new PacketDecodeException("Invalid JWT chain entry length");
			}
			$totalJwtLength += $jwtLength;
		}
		$mapper = new \JsonMapper;
		$mapper->bExceptionOnMissingData = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		try{
			$chainData = $mapper->map($chainDataJson, new JwtChain);
		}catch(\JsonMapper_Exception){
			// Do not chain the JsonMapper exception: chained exceptions can still
			// expose unsanitized values in error reports or stack traces.
			throw new PacketDecodeException("Invalid chain data JSON structure");
		}

		$this->chainDataJwt = $chainData;
		$clientDataJwtLength = $connRequestReader->getLInt();
		if($clientDataJwtLength <= 0){
			//technically this is always positive; the problem results because getLInt() is implicitly signed
			//this is inconsistent with many other methods, but we can't do anything about that for now
			throw new PacketDecodeException("Length of clientData JWT must be positive");
		}
		$this->clientDataJwt = $connRequestReader->get($clientDataJwtLength);
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putInt($this->protocol);
		$out->putString($this->encodeConnectionRequest());
	}

	protected function encodeConnectionRequest() : string{
		$connRequestWriter = new BinaryStream();

		$chainDataJson = json_encode($this->chainDataJwt, JSON_THROW_ON_ERROR);
		$connRequestWriter->putLInt(strlen($chainDataJson));
		$connRequestWriter->put($chainDataJson);

		$connRequestWriter->putLInt(strlen($this->clientDataJwt));
		$connRequestWriter->put($this->clientDataJwt);

		return $connRequestWriter->getBuffer();
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleLogin($this);
	}
}
