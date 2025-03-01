<?php

namespace DvTeam\Microsoft\EntraID;

use DvTeam\Microsoft\EntraID\Common\UserData;
use DvTeam\Microsoft\EntraID\Exceptions\AuthCodeExpiredException;
use DvTeam\Microsoft\EntraID\Exceptions\AuthorizationCodeAlreadyRedeemedException;
use DvTeam\Microsoft\EntraID\Exceptions\EntraIDException;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class EntryIDAuthClient {
	public function __construct(
		private readonly EntraIDEndpoints $endpoints,
		private readonly RequestFactoryInterface $requestFactory,
		private readonly UriFactoryInterface $uriFactory,
		private readonly ClientInterface $client,
		private readonly string $clientId,
		private readonly string $clientSecret,
		private readonly string $landingPageUri
	) {}
	
	public function getRedirectionUrl(): string {
		$authEndpointUri = $this->uriFactory->createUri($this->endpoints->authorizationEndpoint);
		$query = $authEndpointUri->getQuery();
		$query = self::addQueryParam($query, 'client_id', $this->clientId);
		$query = self::addQueryParam($query, 'response_type', 'code');
		$query = self::addQueryParam($query, 'redirect_uri', $this->landingPageUri);
		$query = self::addQueryParam($query, 'scope', 'openid profile User.Read offline_access');
		$query = self::addQueryParam($query, 'response_mode', 'query');
		$authEndpointUri = $authEndpointUri->withQuery($query);
		return (string) $authEndpointUri;
	}
	
	public function handleCode(string $code): UserData {
		$accessToken = $this->getAccessToken(code: $code);
		return $this->getUserData($accessToken);
	}
	
	/**
	 * @param string $code
	 * @return string
	 */
	private function getAccessToken(string $code): string {
		try {
			$postData = [
				'client_id' => $this->clientId,
				'client_secret' => $this->clientSecret,
				'grant_type' => 'authorization_code',
				'code' => $code,
				'redirect_uri' => $this->landingPageUri
			];
			
			$request = $this->requestFactory->createRequest('POST', $this->endpoints->tokenEndpoint);
			$request->getBody()->write(self::encodeUriParams($postData));
			
			$response = $this->client->sendRequest($request);
			$responseContents = $response->getBody()->getContents();
			
			$responseData = json_decode($responseContents, true, 512, JSON_THROW_ON_ERROR);
		} catch(ClientExceptionInterface|JsonException $e) {
			throw new EntraIDException($e->getMessage(), $e->getCode(), $e);
		}
		
		if($responseData['error'] ?? null) {
			if(preg_match('{\\bAADSTS70008\\b}', $responseData['error_description'])) {
				throw new AuthCodeExpiredException($responseData['error_description'] ?? '');
			}
			if(preg_match('{\\bAADSTS54005\\b}', $responseData['error_description'])) {
				throw new AuthorizationCodeAlreadyRedeemedException($responseData['error_description'] ?? '');
			}
			throw new EntraIDException($responseData['error_description'] ?? '');
		}
		
		return $responseData['access_token'];
	}
	
	/**
	 * @param string $accessToken
	 * @return UserData
	 */
	private function getUserData(string $accessToken): UserData {
		try {
			$request = $this->requestFactory->createRequest('GET', $this->endpoints->graphMeEndpoint);
			$request = $request->withHeader('Content-Type', 'application/json');
			$request = $request->withHeader('Authorization', "Bearer {$accessToken}");
			$response = $this->client->sendRequest($request);
			$responseContents = $response->getBody()->getContents();
			$responseData = json_decode($responseContents, true, 512, JSON_THROW_ON_ERROR);
			return new UserData(
				id: $responseData['id'],
				userPrincipalName: $responseData['userPrincipalName'],
				displayName: $responseData['displayName'],
				mail: $responseData['mail'],
			);
		} catch(JsonException|ClientExceptionInterface $e) {
			throw new EntraIDException($e->getMessage(), $e->getCode(), $e);
		}
	}
	
	/**
	 * @param string $query
	 * @param string $key
	 * @param string|array<mixed, mixed> $value
	 * @return string
	 */
	private static function addQueryParam(string $query, string $key, string|array $value): string {
		parse_str($query, $params);
		$params[$key] = $value;
		return self::encodeUriParams($params);
	}
	
	private static function encodeUriParams(array $params): string {
		return http_build_query($params);
	}
}