<?php

namespace common\components;

/**
 * Sorted, merged IP ranges stored as fixed-size binary records, looked up by
 * binary search with fseek: no list is loaded into memory per request.
 *
 * Files (written by build()):
 *   <name>.v4.bin  8-byte records: start, end (uint32 big-endian)
 *   <name>.v6.bin 32-byte records: start, end (16-byte packed addresses)
 */
final class IpRangeSet
{
    public function __construct(private readonly string $basePath)
    {
    }

    public function contains(string $ip): bool
    {
        $packed = @inet_pton($ip);
        if ($packed === false) {
            return false;
        }
        $isV4 = strlen($packed) === 4;
        $file = $this->basePath . ($isV4 ? '.v4.bin' : '.v6.bin');
        $size = $isV4 ? 4 : 16;
        if (!is_file($file) || ($fh = @fopen($file, 'rb')) === false) {
            return false;
        }

        $count = intdiv((int)filesize($file), 2 * $size);
        $lo = 0;
        $hi = $count - 1;
        $found = false;
        while ($lo <= $hi) {
            $mid = ($lo + $hi) >> 1;
            fseek($fh, $mid * 2 * $size);
            $rec = fread($fh, 2 * $size);
            if ($rec === false || strlen($rec) !== 2 * $size) {
                break;
            }
            $start = substr($rec, 0, $size);
            $end = substr($rec, $size);
            if (strcmp($packed, $start) < 0) {
                $hi = $mid - 1;
            } elseif (strcmp($packed, $end) > 0) {
                $lo = $mid + 1;
            } else {
                $found = true;
                break;
            }
        }
        fclose($fh);

        return $found;
    }

    /**
     * @param string[] $cidrs "1.2.3.0/24", "2001:db8::/32" or single addresses
     * @return array{v4: int, v6: int} number of merged ranges written
     */
    public static function build(string $basePath, array $cidrs): array
    {
        $ranges = ['v4' => [], 'v6' => []];
        foreach ($cidrs as $cidr) {
            $range = self::cidrToRange(trim($cidr));
            if ($range !== null) {
                $ranges[strlen($range[0]) === 4 ? 'v4' : 'v6'][] = $range;
            }
        }

        $written = [];
        foreach ($ranges as $family => $list) {
            usort($list, static fn($a, $b) => strcmp($a[0], $b[0]));
            $merged = [];
            foreach ($list as [$start, $end]) {
                $last = count($merged) - 1;
                if ($last >= 0 && strcmp($start, self::increment($merged[$last][1])) <= 0) {
                    if (strcmp($end, $merged[$last][1]) > 0) {
                        $merged[$last][1] = $end;
                    }
                } else {
                    $merged[] = [$start, $end];
                }
            }
            $tmp = $basePath . '.' . $family . '.bin.tmp';
            file_put_contents($tmp, implode('', array_map(static fn($r) => $r[0] . $r[1], $merged)));
            rename($tmp, $basePath . '.' . $family . '.bin');
            $written[$family] = count($merged);
        }

        return $written;
    }

    /** @return array{0: string, 1: string}|null packed [start, end] */
    private static function cidrToRange(string $cidr): ?array
    {
        if ($cidr === '' || $cidr[0] === '#') {
            return null;
        }
        [$addr, $bits] = array_pad(explode('/', $cidr, 2), 2, null);
        $packed = @inet_pton($addr);
        if ($packed === false) {
            return null;
        }
        $len = strlen($packed) * 8;
        $bits = $bits === null ? $len : (int)$bits;
        if ($bits < 0 || $bits > $len) {
            return null;
        }
        $start = '';
        $end = '';
        for ($i = 0; $i < strlen($packed); $i++) {
            $keep = max(0, min(8, $bits - $i * 8));
            $mask = $keep === 0 ? 0 : (0xFF << (8 - $keep)) & 0xFF;
            $byte = ord($packed[$i]);
            $start .= chr($byte & $mask);
            $end .= chr(($byte & $mask) | (~$mask & 0xFF));
        }

        return [$start, $end];
    }

    /** Packed address + 1 (saturating), used to merge adjacent ranges. */
    private static function increment(string $packed): string
    {
        for ($i = strlen($packed) - 1; $i >= 0; $i--) {
            $b = ord($packed[$i]);
            if ($b < 255) {
                return substr($packed, 0, $i) . chr($b + 1) . str_repeat("\0", strlen($packed) - $i - 1);
            }
        }

        return $packed;
    }
}
