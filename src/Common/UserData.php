<?php

namespace DvTeam\Microsoft\EntraID\Common;

class UserData {
	public function __construct(
		public readonly string $id,
		public readonly string $userPrincipalName,
		public readonly string $displayName,
		public readonly string $mail
	) {}
}