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

namespace wolfcore\network\mcpe;

use wolfcore\network\mcpe\compression\CompressBatchPromise;
use wolfcore\network\mcpe\compression\Compressor;
use wolfcore\network\mcpe\convert\TypeConverter;
use wolfcore\network\mcpe\protocol\ProtocolVersions;
use wolfcore\network\mcpe\protocol\LevelChunkPacket;
use wolfcore\network\mcpe\protocol\serializer\PacketBatch;
use wolfcore\network\mcpe\protocol\serializer\PacketSerializerContext;
use wolfcore\network\mcpe\protocol\types\ChunkPosition;
use wolfcore\network\mcpe\serializer\ChunkSerializer;
use wolfcore\scheduler\AsyncTask;
use wolfcore\thread\NonThreadSafeValue;
use wolfcore\utils\BinaryStream;
use wolfcore\world\format\Chunk;
use wolfcore\world\format\io\FastChunkSerializer;

class ChunkRequestTask extends AsyncTask{
	private const TLS_KEY_PROMISE = "promise";

	protected string $chunk;
	protected int $chunkX;
	protected int $chunkZ;
	/** @phpstan-var NonThreadSafeValue<Compressor> */
	protected NonThreadSafeValue $compressor;
    private string $tiles;
    private int $protocol;

    public function __construct(int $chunkX, int $chunkZ, Chunk $chunk, CompressBatchPromise $promise, Compressor $compressor, int $protocol = ProtocolVersions::BASE_PROTOCOL){
        $this->protocol = $protocol;
		$this->compressor = new NonThreadSafeValue($compressor);

		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);
		$this->chunkX = $chunkX;
		$this->chunkZ = $chunkZ;
		$this->tiles = ChunkSerializer::serializeTiles($chunk);

		$this->storeLocal(self::TLS_KEY_PROMISE, $promise);
	}

	public function onRun() : void{
		$chunk = FastChunkSerializer::deserializeTerrain($this->chunk);
		$subCount = ChunkSerializer::getSubChunkCount($chunk);
        $converter = TypeConverter::getForProtocol($this->protocol);
		$encoderContext = new PacketSerializerContext($converter->getItemTypeDictionary(), $this->protocol);
		$payload = ChunkSerializer::serializeFullChunk($chunk, $converter->getBlockTranslator(), $encoderContext, $this->tiles);

		$stream = new BinaryStream();
		PacketBatch::encodePackets($stream, $encoderContext, [LevelChunkPacket::create(new ChunkPosition($this->chunkX, $this->chunkZ), $subCount, false, null, $payload)]);
		$this->setResult($this->compressor->deserialize()->compress($stream->getBuffer()));
	}

	public function onCompletion() : void{
		/** @var CompressBatchPromise $promise */
		$promise = $this->fetchLocal(self::TLS_KEY_PROMISE);
		$promise->resolve($this->getResult());
	}
}
