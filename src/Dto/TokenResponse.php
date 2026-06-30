<?php

namespace Foutraz\Withings\Dto;

final readonly class TokenResponse
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public int $expiresAt,
        public int $expiresIn,
        public string $tokenType,
        public int $userid,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $body = $data['body'] ?? $data;

        $expiresIn = (int) ($body['expires_in'] ?? 0);

        return new self(
            (string) ($body['access_token'] ?? ''),
            (string) ($body['refresh_token'] ?? ''),
            time() + $expiresIn,
            $expiresIn,
            (string) ($body['token_type'] ?? 'Bearer'),
            (int) ($body['userid'] ?? 0),
        );
    }
}
