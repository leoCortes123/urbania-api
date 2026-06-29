<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Services;

use Illuminate\Support\Facades\Redis;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\JwtToken;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class PhpOpenSourceSaverJwtService implements JwtServiceInterface
{
    private const string ISSUER = 'https://api.urbania.com';

    private const int DEFAULT_TTL = 900;

    private const int CLOCK_SKEW_SECONDS = 30;

    private const string REDIS_BLACKLIST_PREFIX = 'jwt:blacklist:';

    /** @var list<string> */
    private const array AUDIENCE = ['api.urbania.com', 'web.urbania.com', 'app.urbania'];

    /** @var list<string> */
    private const array REQUIRED_CLAIMS = [
        'jti',
        'sub',
        'iss',
        'aud',
        'iat',
        'nbf',
        'exp',
        'role',
        'mfa_verified',
        'session_id',
        'device_fp',
        'org_id',
    ];

    private Configuration $config;

    public function __construct()
    {
        $privateKeyPath = base_path('storage/jwt/private.pem');
        $publicKeyPath = base_path('storage/jwt/public.pem');

        $privateKey = @file_get_contents($privateKeyPath);
        $publicKey = @file_get_contents($publicKeyPath);

        if ($privateKey === false || $publicKey === false || $privateKey === '' || $publicKey === '') {
            throw new \RuntimeException('JWT RSA keys not found');
        }

        $this->config = Configuration::forAsymmetricSigner(
            new Sha256,
            InMemory::plainText($privateKey),
            InMemory::plainText($publicKey),
        );
    }

    public function generateAccessToken(
        string $userId,
        string $role,
        bool $mfaVerified,
        SessionId $sessionId,
        string $deviceFingerprint,
        ?string $organizationId = null,
        ?string $scope = null,
        ?int $ttl = null,
    ): JwtToken {
        if ($userId === '' || $role === '') {
            throw new \InvalidArgumentException('User ID and role cannot be empty');
        }

        $now = new \DateTimeImmutable;
        $ttl = $ttl ?? self::DEFAULT_TTL;
        $expiresAt = $now->modify("+{$ttl} seconds");
        $jti = Uuid::v7()->toString();
        assert($jti !== '');

        $builder = $this->config->builder()
            ->identifiedBy($jti)
            ->relatedTo($userId)
            ->issuedBy(self::ISSUER)
            ->permittedFor(...self::AUDIENCE)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($expiresAt)
            ->withClaim('role', $role)
            ->withClaim('mfa_verified', $mfaVerified)
            ->withClaim('session_id', $sessionId->toString())
            ->withClaim('device_fp', $deviceFingerprint)
            ->withClaim('org_id', $organizationId ?? '');

        if ($scope !== null && $scope !== '') {
            $builder = $builder->withClaim('scope', $scope);
        }

        $token = $builder->getToken($this->config->signer(), $this->config->signingKey());

        return JwtToken::fromString($token->toString());
    }

    public function generateRefreshToken(): string
    {
        return bin2hex(random_bytes(64));
    }

    public function decode(string $token): array
    {
        if ($token === '') {
            throw new \InvalidArgumentException('Token cannot be empty');
        }

        $parsed = $this->config->parser()->parse($token);

        if (! $parsed instanceof UnencryptedToken) {
            throw new \RuntimeException('Token could not be decoded');
        }

        return $this->normalizeClaims($parsed->claims()->all());
    }

    public function validate(string $token): bool
    {
        if ($token === '') {
            return false;
        }

        try {
            $parsed = $this->config->parser()->parse($token);

            if (! $parsed instanceof UnencryptedToken) {
                return false;
            }

            $constraints = [
                new SignedWith($this->config->signer(), $this->config->verificationKey()),
            ];

            if (! $this->config->validator()->validate($parsed, ...$constraints)) {
                return false;
            }

            if (! $this->validateTimeClaims($parsed)) {
                return false;
            }

            $claims = $parsed->claims();
            foreach (self::REQUIRED_CLAIMS as $claim) {
                if (! $claims->has($claim)) {
                    return false;
                }
            }

            $jti = $claims->get('jti');
            if (! is_string($jti) || $jti === '' || $this->isBlacklisted($jti)) {
                return false;
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function revoke(string $jti): void
    {
        if ($jti === '') {
            throw new \InvalidArgumentException('JTI cannot be empty');
        }

        Redis::setex(self::REDIS_BLACKLIST_PREFIX.$jti, self::DEFAULT_TTL, '1');
    }

    public function isBlacklisted(string $jti): bool
    {
        if ($jti === '') {
            return false;
        }

        return Redis::get(self::REDIS_BLACKLIST_PREFIX.$jti) !== null;
    }

    private function validateTimeClaims(UnencryptedToken $parsed): bool
    {
        $now = new \DateTimeImmutable;
        $skew = new \DateInterval('PT'.self::CLOCK_SKEW_SECONDS.'S');

        $issuedAt = $parsed->claims()->get(RegisteredClaims::ISSUED_AT);
        $notBefore = $parsed->claims()->get(RegisteredClaims::NOT_BEFORE);
        $expiration = $parsed->claims()->get(RegisteredClaims::EXPIRATION_TIME);

        if (! $issuedAt instanceof \DateTimeImmutable
            || ! $notBefore instanceof \DateTimeImmutable
            || ! $expiration instanceof \DateTimeImmutable
        ) {
            return false;
        }

        if (! $parsed->hasBeenIssuedBefore($now->add($skew))) {
            return false;
        }

        if (! $parsed->isMinimumTimeBefore($now->add($skew))) {
            return false;
        }

        if ($parsed->isExpired($now->sub($skew))) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $claims
     * @return array<string, mixed>
     */
    private function normalizeClaims(array $claims): array
    {
        $normalized = [];
        foreach ($claims as $key => $value) {
            $normalized[$key] = $this->normalizeClaimValue($value);
        }

        return $normalized;
    }

    private function normalizeClaimValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value->getTimestamp();
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->normalizeClaimValue($item), $value);
        }

        return $value;
    }
}
