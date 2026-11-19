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

namespace wolfcore\network\mcpe\protocol\types\login;

/**
 * Model class for LoginPacket JSON data for JsonMapper
 */
final class ClientData{

	/**
	 * @var ClientDataAnimationFrame[]
	 */
	public array $AnimatedImageData = [];

	public string $ArmSize = "wide";

	public string $CapeData = "";

	public string $CapeId = "";

	public int $CapeImageHeight = 0;

	public int $CapeImageWidth = 0;

	public bool $CapeOnClassicSkin = false;

	/** @required */
	public int $ClientRandomId;

	/** @required */
	public bool $CompatibleWithClientSideChunkGen;

	/** @required */
	public int $CurrentInputMode;

	/** @required */
	public int $DefaultInputMode;

	/** @required */
	public string $DeviceId;

	/** @required */
	public string $DeviceModel;

	/** @required */
	public int $DeviceOS;

	/** @required */
	public string $GameVersion;

	/** @required */
	public int $GuiScale;

	/** @required */
	public bool $IsEditorMode;

	/** @required */
	public string $LanguageCode;

	public bool $OverrideSkin;

	/**
	 * @var ClientDataPersonaSkinPiece[]
	 */
	public array $PersonaPieces = [];

	public bool $PersonaSkin = false;

	/**
	 * @var ClientDataPersonaPieceTintColor[]
	 */
	public array $PieceTintColors = [];

	/** @required */
	public string $PlatformOfflineId;

	/** @required */
	public string $PlatformOnlineId;

	public string $PlatformUserId = ""; //xbox-only, apparently

	public string $PlayFabId = "";

	public bool $PremiumSkin = false;

	/** @required */
	public string $SelfSignedId;

	/** @required */
	public string $ServerAddress;

	public string $SkinAnimationData = "";

	public string $SkinColor = "";

	public string $SkinData = "";

	public string $SkinGeometryData = "";

	public string $SkinGeometryDataEngineVersion = "";

	public string $SkinId = "";

	public int $SkinImageHeight = 0;

	public int $SkinImageWidth = 0;

	public string $SkinResourcePatch = "";

	/** @required */
	public string $ThirdPartyName;

	/** @required */
	public bool $ThirdPartyNameOnly;

	/** @required */
	public bool $TrustedSkin;

	/** @required */
	public int $UIProfile;
}
