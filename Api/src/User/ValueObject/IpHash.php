<?php

declare(strict_types=1);

namespace Mush\User\ValueObject;

final readonly class IpHash
{
    private const IPV4_PREFIX_LENGTH = 32;
    private const IPV6_PREFIX_LENGTH = 64;

    public static function hashFor(string $ip, string $hmacKey): string
    {
        $cidr = self::createCidr($ip);

        return hash_hmac('sha256', $cidr, $hmacKey);
    }

    private static function createCidr(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return self::ipv6Prefix($ip, self::IPV6_PREFIX_LENGTH);
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return self::ipv4Prefix($ip, self::IPV4_PREFIX_LENGTH);
        }

        throw new \InvalidArgumentException('Invalid IP address');
    }

    private static function ipv4Prefix(string $ip, int $prefixLen): string
    {
        $ipLong = ip2long($ip);
        $mask = -1 << (32 - $prefixLen);
        $maskedIp = long2ip($ipLong & $mask);

        return \sprintf('%s/%d', $maskedIp, $prefixLen);
    }

    private static function ipv6Prefix(string $ip, int $prefixLen): string
    {
        $binaryIp = inet_pton($ip);
        if ($binaryIp === false) {
            throw new \InvalidArgumentException('Invalid IPv6 address');
        }

        $bytes = str_split($binaryIp);
        $remainingBits = $prefixLen;

        for ($i = 0; $i < 16; ++$i) {
            if ($remainingBits >= 8) {
                $remainingBits -= 8;

                continue;
            }
            if ($remainingBits > 0) {
                $bytes[$i] = \chr(\ord($bytes[$i]) & (0xFF << (8 - $remainingBits)));
                $remainingBits = 0;
            } else {
                $bytes[$i] = "\x00";
            }
        }

        $maskedBinary = implode('', $bytes);
        $maskedIp = inet_ntop($maskedBinary);

        return $maskedIp . "/{$prefixLen}";
    }
}
