<?php

namespace DvTeam\Microsoft\EntraID;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class EntraIDAuthFactory {
	public function __construct(
		private readonly EntraIDEndpoints $endpoints,
		private readonly RequestFactoryInterface $requestFactory,
		private readonly UriFactoryInterface $uriFactory,
		private readonly ClientInterface $client,
		private readonly string $landingPageUri
	) {}
	
	public function createAuthClient(string $clientId, string $clientSecret): EntryIDAuthClient {
		return new EntryIDAuthClient(
			endpoints: $this->endpoints,
			requestFactory: $this->requestFactory,
			uriFactory: $this->uriFactory,
			client: $this->client,
			clientId: $clientId,
			clientSecret: $clientSecret,
			landingPageUri: $this->landingPageUri,
		);
	}
}