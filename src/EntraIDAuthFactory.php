<?php

namespace DvTeam\Microsoft\EntraID;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

readonly class EntraIDAuthFactory {
	public function __construct(
		private EntraIdEndpoints $endpoints,
		private RequestFactoryInterface $requestFactory,
		private UriFactoryInterface $uriFactory,
		private ClientInterface $client,
		private string $landingPageUri
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