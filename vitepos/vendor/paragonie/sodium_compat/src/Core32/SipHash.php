<?php

if (class_exists('ParagonIE_Sodium_Core32_SipHash', false)) {
    return;
}

/**
 * Class ParagonIE_SodiumCompat_Core32_SipHash
 *
 * Only uses 32-bit arithmetic, while the original SipHash used 64-bit integers
 */
class ParagonIE_Sodium_Core32_SipHash extends ParagonIE_Sodium_Core32_Util
{
    /**
     * @internal You should not use this directly from another application
     *
     * @param array<int, ParagonIE_Sodium_Core32_Int64> $v
     * @return array<int, ParagonIE_Sodium_Core32_Int64>
     */
    public static function sipRound(array $v)
    {
                $v[0] = $v[0]->addInt64($v[1]);

                $v[1] = $v[1]->rotateLeft(13);

                $v[1] = $v[1]->xorInt64($v[0]);

                $v[0] = $v[0]->rotateLeft(32);

                $v[2] = $v[2]->addInt64($v[3]);

                $v[3] = $v[3]->rotateLeft(16);

                $v[3] = $v[3]->xorInt64($v[2]);

                $v[0] = $v[0]->addInt64($v[3]);

                $v[3] = $v[3]->rotateLeft(21);

                $v[3] = $v[3]->xorInt64($v[0]);

                $v[2] = $v[2]->addInt64($v[1]);

                $v[1] = $v[1]->rotateLeft(17);

                $v[1] = $v[1]->xorInt64($v[2]);

                $v[2] = $v[2]->rotateLeft(32);

        return $v;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param string $in
     * @param string $key
     * @return string
     * @throws SodiumException
     * @throws TypeError
     */
    public static function sipHash24($in, $key)
    {
        $inlen = self::strlen($in);

                                                $v = array(
            new ParagonIE_Sodium_Core32_Int64(
                array(0x736f, 0x6d65, 0x7073, 0x6575)
            ),
            new ParagonIE_Sodium_Core32_Int64(
                array(0x646f, 0x7261, 0x6e64, 0x6f6d)
            ),
            new ParagonIE_Sodium_Core32_Int64(
                array(0x6c79, 0x6765, 0x6e65, 0x7261)
            ),
            new ParagonIE_Sodium_Core32_Int64(
                array(0x7465, 0x6462, 0x7974, 0x6573)
            )
        );

                        $k = array(
            ParagonIE_Sodium_Core32_Int64::fromReverseString(
                self::substr($key, 0, 8)
            ),
            ParagonIE_Sodium_Core32_Int64::fromReverseString(
                self::substr($key, 8, 8)
            )
        );

                $b = new ParagonIE_Sodium_Core32_Int64(
            array(($inlen << 8) & 0xffff, 0, 0, 0)
        );

                $v[3] = $v[3]->xorInt64($k[1]);
                $v[2] = $v[2]->xorInt64($k[0]);
                $v[1] = $v[1]->xorInt64($k[1]);
                $v[0] = $v[0]->xorInt64($k[0]);

        $left = $inlen;
                while ($left >= 8) {
                        $m = ParagonIE_Sodium_Core32_Int64::fromReverseString(
                self::substr($in, 0, 8)
            );

                        $v[3] = $v[3]->xorInt64($m);

                                    $v = self::sipRound($v);
            $v = self::sipRound($v);

                        $v[0] = $v[0]->xorInt64($m);

            $in = self::substr($in, 8);
            $left -= 8;
        }

                                                                                                switch ($left) {
            case 7:
                $b = $b->orInt64(
                    ParagonIE_Sodium_Core32_Int64::fromInts(
                        0, self::chrToInt($in[6]) << 16
                    )
                );
            case 6:
                $b = $b->orInt64(
                    ParagonIE_Sodium_Core32_Int64::fromInts(
                        0, self::chrToInt($in[5]) << 8
                    )
                );
            case 5:
                $b = $b->orInt64(
                    ParagonIE_Sodium_Core32_Int64::fromInts(
                        0, self::chrToInt($in[4])
                    )
                );
            case 4:
                $b = $b->orInt64(
                    ParagonIE_Sodium_Core32_Int64::fromInts(
                        self::chrToInt($in[3]) << 24, 0
                    )
                );
            case 3:
                $b = $b->orInt64(
                    ParagonIE_Sodium_Core32_Int64::fromInts(
                        self::chrToInt($in[2]) << 16, 0
                    )
                );
            case 2:
                $b = $b->orInt64(
                    ParagonIE_Sodium_Core32_Int64::fromInts(
                        self::chrToInt($in[1]) << 8, 0
                    )
                );
            case 1:
                $b = $b->orInt64(
                    ParagonIE_Sodium_Core32_Int64::fromInts(
                        self::chrToInt($in[0]), 0
                    )
                );
            case 0:
                break;
        }

                $v[3] = $v[3]->xorInt64($b);

                        $v = self::sipRound($v);
        $v = self::sipRound($v);

                $v[0] = $v[0]->xorInt64($b);

                        $v[2]->limbs[3] ^= 0xff;

                                        $v = self::sipRound($v);
        $v = self::sipRound($v);
        $v = self::sipRound($v);
        $v = self::sipRound($v);

                        return $v[0]
            ->xorInt64($v[1])
            ->xorInt64($v[2])
            ->xorInt64($v[3])
            ->toReverseString();
    }
}
