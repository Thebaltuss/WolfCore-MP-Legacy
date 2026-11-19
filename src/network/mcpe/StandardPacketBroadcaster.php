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

use wolfcore\event\server\DataPacketSendEvent;
use wolfcore\network\mcpe\protocol\serializer\PacketBatch;
use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;
use wolfcore\network\mcpe\protocol\serializer\PacketSerializerContext;
use wolfcore\Server;
use wolfcore\timings\Timings;
use wolfcore\utils\BinaryStream;
use function count;
use function log;
use function spl_object_id;
use function strlen;

final class StandardPacketBroadcaster implements PacketBroadcaster{
	public function __construct(
		private Server $server,
		private PacketSerializerContext $protocolContext
	){}

	public function broadcastPackets(array $recipients, array $packets) : void{
		//TODO: this shouldn't really be called here, since the broadcaster might be replaced by an alternative
		//implementation that doesn't fire events
		$ev = new DataPacketSendEvent($recipients, $packets);
		$ev->call();
		if($ev->isCancelled()){
			return;
		}
		$packets = $ev->getPackets();

		$compressors = [];

		/** @var NetworkSession[][] $targetsByCompressor */
		$targetsByCompressor = [];
		foreach($recipients as $recipient){

			//TODO: different compressors might be compatible, it might not be necessary to split them up by object
			$compressor = $recipient->getCompressor();
            $key = spl_object_id($compressor) . ":" . $recipient->getProtocolVersion();
            $compressors[$key] = $compressor;
            $targetsByCompressor[$key][] = $recipient;
		}

        foreach($targetsByCompressor as $compressorId => $compressorTargets){
            $compressor = $compressors[$compressorId];
            $protocol = $compressorTargets[0]->getProtocolVersion();
            $totalLength = 0;
            $packetBuffers = [];
            foreach($packets as $packet){
                if($packet instanceof \wolfcore\network\mcpe\protocol\AvailableCommandsPacket){
                    $packet = clone $packet;
                    $packet->targetProtocol = $protocol;
                }
                $buffer = NetworkSession::encodePacketTimed(
                    PacketSerializer::encoder($compressorTargets[0]->getPacketSerializerContext()),
                    ProtocolPacketAdapter::adapt($packet, $protocol, true)
                );
                $totalLength += (((int) log(strlen($buffer), 128)) + 1) + strlen($buffer);
                $packetBuffers[] = $buffer;
            }

			$threshold = $compressor->getCompressionThreshold();
			if(count($compressorTargets) > 1 && $threshold !== null && $totalLength >= $threshold){
				//do not prepare shared batch unless we're sure it will be compressed
				$stream = new BinaryStream();
				PacketBatch::encodeRaw($stream, $packetBuffers);
				$batchBuffer = $stream->getBuffer();

				$promise = $this->server->prepareBatch($batchBuffer, $compressor, timings: Timings::$playerNetworkSendCompressBroadcast);
				foreach($compressorTargets as $target){
					$target->queueCompressed($promise);
				}
			}else{
				foreach($compressorTargets as $target){
					foreach($packetBuffers as $packetBuffer){
						$target->addToSendBuffer($packetBuffer);
					}
				}
			}
		}
	}
}
