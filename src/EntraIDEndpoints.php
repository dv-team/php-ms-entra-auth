<?php

namespace DvTeam\Microsoft\EntraID;

class EntraIDEndpoints {
	public function __construct(
		public readonly string $authorizationEndpoint,
		public readonly string $tokenEndpoint,
		public readonly string $graphMeEndpoint = 'https://graph.microsoft.com/v1.0/me',
	) {}
}