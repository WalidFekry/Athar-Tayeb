<?php
/**
 * Arabic Text Shaping and BiDi Support for Image Generation
 * Simplified implementation for proper Arabic text rendering
 * 
 * This class handles:
 * - Arabic letter shaping (initial, medial, final, isolated forms)
 * - Basic bidirectional text support
 * - RTL text direction
 * - Arabic-Indic numeral conversion
 */

class ArabicGlyphs
{
    /**
     * Arabic letter forms mapping
     * Format: [isolated, final, initial, medial]
     */
    private static $arabicForms = [
        // Alef
        0x0627 => [0xFE8D, 0xFE8E, null, null],
        // Beh
        0x0628 => [0xFE8F, 0xFE90, 0xFE91, 0xFE92],
        // Teh
        0x062A => [0xFE95, 0xFE96, 0xFE97, 0xFE98],
        // Theh
        0x062B => [0xFE99, 0xFE9A, 0xFE9B, 0xFE9C],
        // Jeem
        0x062C => [0xFE9D, 0xFE9E, 0xFE9F, 0xFEA0],
        // Hah
        0x062D => [0xFEA1, 0xFEA2, 0xFEA3, 0xFEA4],
        // Khah
        0x062E => [0xFEA5, 0xFEA6, 0xFEA7, 0xFEA8],
        // Dal
        0x062F => [0xFEA9, 0xFEAA, null, null],
        // Thal
        0x0630 => [0xFEAB, 0xFEAC, null, null],
        // Reh
        0x0631 => [0xFEAD, 0xFEAE, null, null],
        // Zain
        0x0632 => [0xFEAF, 0xFEB0, null, null],
        // Seen
        0x0633 => [0xFEB1, 0xFEB2, 0xFEB3, 0xFEB4],
        // Sheen
        0x0634 => [0xFEB5, 0xFEB6, 0xFEB7, 0xFEB8],
        // Sad
        0x0635 => [0xFEB9, 0xFEBA, 0xFEBB, 0xFEBC],
        // Dad
        0x0636 => [0xFEBD, 0xFEBE, 0xFEBF, 0xFEC0],
        // Tah
        0x0637 => [0xFEC1, 0xFEC2, 0xFEC3, 0xFEC4],
        // Zah
        0x0638 => [0xFEC5, 0xFEC6, 0xFEC7, 0xFEC8],
        // Ain
        0x0639 => [0xFEC9, 0xFECA, 0xFECB, 0xFECC],
        // Ghain
        0x063A => [0xFECD, 0xFECE, 0xFECF, 0xFED0],
        // Feh
        0x0641 => [0xFED1, 0xFED2, 0xFED3, 0xFED4],
        // Qaf
        0x0642 => [0xFED5, 0xFED6, 0xFED7, 0xFED8],
        // Kaf
        0x0643 => [0xFED9, 0xFEDA, 0xFEDB, 0xFEDC],
        // Lam
        0x0644 => [0xFEDD, 0xFEDE, 0xFEDF, 0xFEE0],
        // Meem
        0x0645 => [0xFEE1, 0xFEE2, 0xFEE3, 0xFEE4],
        // Noon
        0x0646 => [0xFEE5, 0xFEE6, 0xFEE7, 0xFEE8],
        // Heh
        0x0647 => [0xFEE9, 0xFEEA, 0xFEEB, 0xFEEC],
        // Waw
        0x0648 => [0xFEED, 0xFEEE, null, null],
        // Yeh
        0x064A => [0xFEF1, 0xFEF2, 0xFEF3, 0xFEF4],
        // Teh Marbuta
        0x0629 => [0xFE93, 0xFE94, null, null],
        // Alef Maksura
        0x0649 => [0xFEEF, 0xFEF0, null, null],
        // Hamza
        0x0621 => [0xFE80, null, null, null],
        // Alef with Madda
        0x0622 => [0xFE81, 0xFE82, null, null],
        // Alef with Hamza above
        0x0623 => [0xFE83, 0xFE84, null, null],
        // Waw with Hamza above
        0x0624 => [0xFE85, 0xFE86, null, null],
        // Alef with Hamza below
        0x0625 => [0xFE87, 0xFE88, null, null],
        // Yeh with Hamza above
        0x0626 => [0xFE89, 0xFE8A, 0xFE8B, 0xFE8C],
    ];

    /**
     * Characters that don't connect to the following letter
     */
    private static $nonConnectors = [
        0x0627, 0x062F, 0x0630, 0x0631, 0x0632, 0x0648, 0x0622, 0x0623, 0x0624, 0x0625, 0x0629
    ];

    /**
     * Convert Arabic-Indic digits to Western digits
     */
    private static $arabicDigits = [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'
    ];

    /**
     * Shape Arabic text for proper rendering
     * 
     * @param string $text UTF-8 Arabic text
     * @return string Shaped text ready for rendering
     */
    public static function shapeText($text)
    {
        if (empty($text)) {
            return $text;
        }

        // Convert Arabic-Indic digits to Western
        $text = strtr($text, self::$arabicDigits);

        // Convert to array of Unicode code points
        $chars = self::utf8ToUnicodeArray($text);
        $shaped = [];

        for ($i = 0; $i < count($chars); $i++) {
            $current = $chars[$i];
            $prev = $i > 0 ? $chars[$i - 1] : null;
            $next = $i < count($chars) - 1 ? $chars[$i + 1] : null;

            // Skip if not Arabic letter
            if (!isset(self::$arabicForms[$current])) {
                $shaped[] = $current;
                continue;
            }

            $forms = self::$arabicForms[$current];
            $canConnectToPrev = $prev && isset(self::$arabicForms[$prev]) && !in_array($prev, self::$nonConnectors);
            $canConnectToNext = $next && isset(self::$arabicForms[$next]) && !in_array($current, self::$nonConnectors);

            // Determine which form to use
            if ($canConnectToPrev && $canConnectToNext && $forms[3]) {
                // Medial form
                $shaped[] = $forms[3];
            } elseif ($canConnectToPrev && $forms[1]) {
                // Final form
                $shaped[] = $forms[1];
            } elseif ($canConnectToNext && $forms[2]) {
                // Initial form
                $shaped[] = $forms[2];
            } else {
                // Isolated form
                $shaped[] = $forms[0] ?: $current;
            }
        }

        return self::unicodeArrayToUtf8($shaped);
    }

    /**
     * Apply bidirectional algorithm (simplified)
     * Reverses Arabic text segments while preserving Latin text order
     */
    public static function applyBidi($text)
    {
        // Simple approach: reverse Arabic segments
        $segments = [];
        $currentSegment = '';
        $isArabic = false;

        $chars = self::utf8ToUnicodeArray($text);

        foreach ($chars as $char) {
            $charIsArabic = self::isArabicChar($char);

            if ($charIsArabic !== $isArabic) {
                if (!empty($currentSegment)) {
                    $segments[] = [
                        'text' => $currentSegment,
                        'isArabic' => $isArabic
                    ];
                }
                $currentSegment = '';
                $isArabic = $charIsArabic;
            }

            $currentSegment .= self::unicodeToUtf8($char);
        }

        if (!empty($currentSegment)) {
            $segments[] = [
                'text' => $currentSegment,
                'isArabic' => $isArabic
            ];
        }

        // Reverse Arabic segments
        $result = '';
        foreach ($segments as $segment) {
            if ($segment['isArabic']) {
                // Reverse the order of characters in Arabic segments
                $arabicChars = self::utf8ToUnicodeArray($segment['text']);
                $arabicChars = array_reverse($arabicChars);
                $result .= self::unicodeArrayToUtf8($arabicChars);
            } else {
                $result .= $segment['text'];
            }
        }

        return $result;
    }

    /**
     * Process Arabic text for rendering (shape + bidi)
     */
    public static function processText($text)
    {
        // First shape the text
        $shaped = self::shapeText($text);
        
        // Then apply bidirectional algorithm
        $bidi = self::applyBidi($shaped);
        
        return $bidi;
    }

    /**
     * Check if character is Arabic
     */
    private static function isArabicChar($codepoint)
    {
        return ($codepoint >= 0x0600 && $codepoint <= 0x06FF) ||
               ($codepoint >= 0xFE70 && $codepoint <= 0xFEFF) ||
               ($codepoint >= 0xFB50 && $codepoint <= 0xFDFF);
    }

    /**
     * Convert UTF-8 string to array of Unicode code points
     */
    private static function utf8ToUnicodeArray($str)
    {
        $unicode = [];
        $len = strlen($str);
        $i = 0;

        while ($i < $len) {
            $byte = ord($str[$i]);

            if ($byte < 0x80) {
                // ASCII
                $unicode[] = $byte;
                $i++;
            } elseif (($byte >> 5) === 0x06) {
                // 110xxxxx - 2 byte sequence
                $unicode[] = (($byte & 0x1F) << 6) | (ord($str[$i + 1]) & 0x3F);
                $i += 2;
            } elseif (($byte >> 4) === 0x0E) {
                // 1110xxxx - 3 byte sequence
                $unicode[] = (($byte & 0x0F) << 12) | ((ord($str[$i + 1]) & 0x3F) << 6) | (ord($str[$i + 2]) & 0x3F);
                $i += 3;
            } elseif (($byte >> 3) === 0x1E) {
                // 11110xxx - 4 byte sequence
                $unicode[] = (($byte & 0x07) << 18) | ((ord($str[$i + 1]) & 0x3F) << 12) | ((ord($str[$i + 2]) & 0x3F) << 6) | (ord($str[$i + 3]) & 0x3F);
                $i += 4;
            } else {
                // Invalid UTF-8
                $i++;
            }
        }

        return $unicode;
    }

    /**
     * Convert array of Unicode code points to UTF-8 string
     */
    private static function unicodeArrayToUtf8($unicode)
    {
        $str = '';
        foreach ($unicode as $codepoint) {
            $str .= self::unicodeToUtf8($codepoint);
        }
        return $str;
    }

    /**
     * Convert single Unicode code point to UTF-8
     */
    private static function unicodeToUtf8($codepoint)
    {
        if ($codepoint < 0x80) {
            return chr($codepoint);
        } elseif ($codepoint < 0x800) {
            return chr(0xC0 | ($codepoint >> 6)) . chr(0x80 | ($codepoint & 0x3F));
        } elseif ($codepoint < 0x10000) {
            return chr(0xE0 | ($codepoint >> 12)) . chr(0x80 | (($codepoint >> 6) & 0x3F)) . chr(0x80 | ($codepoint & 0x3F));
        } else {
            return chr(0xF0 | ($codepoint >> 18)) . chr(0x80 | (($codepoint >> 12) & 0x3F)) . chr(0x80 | (($codepoint >> 6) & 0x3F)) . chr(0x80 | ($codepoint & 0x3F));
        }
    }
}
?>
