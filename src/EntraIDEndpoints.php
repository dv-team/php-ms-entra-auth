<?php

namespace DvTeam\Microsoft\EntraID;

readonly class EntraIDEndpoints {
	public function __construct(
		public string $authorizationEndpoint,
		public string $tokenEndpoint,
		public string $graphMeEndpoint = 'https://graph.microsoft.com/v1.0/me',
	) {}
}