<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2020 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=0);

namespace Ampache\Module\Util;

use Ampache\Config\AmpConfig;
use Ampache\Module\Api\Xml_Data;
use DOMDocument;

final class XmlWriter implements XmlWriterInterface
{
    public function writeXml(
        string $string,
        string $type = '',
        ?string $title = null,
        bool $full_xml = true
    ): string {
        $xml = '';
        if ($full_xml) {
            $xml .= self::header($type, $title);
        }
        $xml .= $this->cleanUtf8($string);
        if ($full_xml) {
            $xml .= self::footer($type);
        }
        // return formatted xml when asking for full_xml
        if ($full_xml) {
            $dom = new DOMDocument;
            // format the string
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($xml);
            $dom->formatOutput = true;

            return $dom->saveXML();
        }

        return $xml;
    }

    public function writePlainXml(
        string $string,
        string $type = '',
        ?string $title = null,
        bool $full_xml = true
    ): string {
        $xml = "";
        if ($full_xml) {
            $xml .= self::header($type, $title);
        }
        $xml .= $string;
        if ($full_xml) {
            $xml .= self::footer($type);
        }

        return $xml;
    }

    /**
     * this returns a standard header, there are a few types
     * so we allow them to pass a type if they want to
     */
    private function header(
        string $type,
        ?string $title = null
    ): string {
        switch ($type) {
            case 'xspf':
                $header = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n" . "<playlist version = \"1\" xmlns=\"http://xspf.org/ns/0/\">\n" . "<title>" . ($title ?: T_("Ampache XSPF Playlist")) . "</title>\n" . "<creator>" . scrub_out(AmpConfig::get('site_title')) . "</creator>\n" . "<annotation>" . scrub_out(AmpConfig::get('site_title')) . "</annotation>\n" . "<info>" . AmpConfig::get('web_path') . "</info>\n" . "<trackList>\n";
                break;
            case 'itunes':
                $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
                    "<!-- XML Generated by Ampache v." . AmpConfig::get('version') . " -->\n";
                break;
            case 'rss':
                $header = "<?xml version=\"1.0\" encoding=\"" . AmpConfig::get('site_charset') . "\" ?>\n " . "<!-- RSS Generated by Ampache v." . AmpConfig::get('version') . " on " . date("r",
                        time()) . "-->\n" . "<rss version=\"2.0\">\n<channel>\n";
                break;
            default:
                $header = "<?xml version=\"1.0\" encoding=\"" . AmpConfig::get('site_charset') . "\" ?>\n<root>\n";
                break;
        }

        return $header;
    }

    /**
     * this returns the footer for this document, these are pretty boring
     */
    private function footer(
        string $type
    ): string {
        switch ($type) {
            case 'itunes':
                $footer = "\t\t</dict>\t\n</dict>\n</plist>\n";
                break;
            case 'xspf':
                $footer = "</trackList>\n</playlist>\n";
                break;
            case 'rss':
                $footer = "\n</channel>\n</rss>\n";
                break;
            default:
                $footer = "\n</root>\n";
                break;
        }

        return $footer;
    }

    /**
     * Removes characters that aren't valid in XML (which is a subset of valid
     * UTF-8, but close enough for our purposes.)
     * See http://www.w3.org/TR/2006/REC-xml-20060816/#charsets
     * @param string $string
     * @return string
     */
    private function cleanUtf8(string $string): string
    {
        if ($string) {
            $clean = preg_replace(
                '/[^\x{9}\x{a}\x{d}\x{20}-\x{d7ff}\x{e000}-\x{fffd}\x{10000}-\x{10ffff}]|[\x{7f}-\x{84}\x{86}-\x{9f}\x{fdd0}-\x{fddf}\x{1fffe}-\x{1ffff}\x{2fffe}-\x{2ffff}\x{3fffe}-\x{3ffff}\x{4fffe}-\x{4ffff}\x{5fffe}-\x{5ffff}\x{6fffe}-\x{6ffff}\x{7fffe}-\x{7ffff}\x{8fffe}-\x{8ffff}\x{9fffe}-\x{9ffff}\x{afffe}-\x{affff}\x{bfffe}-\x{bffff}\x{cfffe}-\x{cffff}\x{dfffe}-\x{dffff}\x{efffe}-\x{effff}\x{ffffe}-\x{fffff}\x{10fffe}-\x{10ffff}]/u',
                '',
                $string
            );

            if ($clean) {
                return rtrim((string)$clean);
            }

            debug_event(self::class, 'Charset cleanup failed, something might break', 1);
        }

        return '';
    }

    /**
     * output_xml_from_array
     * This takes a one dimensional array and creates a XML document from it. For
     * use primarily by the ajax mojo.
     * @param  array   $array
     * @param  boolean $callback
     * @param  string  $type
     * @return string
     *
     * @todo implement as non-static method
     */
    public static function output_xml_from_array($array, $callback = false, $type = '')
    {
        $string = '';

        // If we weren't passed an array then return
        if (!is_array($array)) {
            return $string;
        }

        // The type is used for the different XML docs we pass
        switch ($type) {
            case 'itunes':
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $value = xoutput_from_array($value, true, $type);
                        $string .= "\t\t<$key>\n$value\t\t</$key>\n";
                    } else {
                        if ($key == "key") {
                            $string .= "\t\t<$key>$value</$key>\n";
                        } elseif (is_int($value)) {
                            $string .= "\t\t\t<key>$key</key><integer>$value</integer>\n";
                        } elseif ($key == "Date Added") {
                            $string .= "\t\t\t<key>$key</key><date>$value</date>\n";
                        } elseif (is_string($value)) {
                            /* We need to escape the value */
                            $string .= "\t\t\t<key>$key</key><string><![CDATA[$value]]></string>\n";
                        }
                    }
                } // end foreach

                return $string;
            case 'xspf':
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $value = xoutput_from_array($value, true, $type);
                        $string .= "\t\t<$key>\n$value\t\t</$key>\n";
                    } else {
                        if ($key == "key") {
                            $string .= "\t\t<$key>$value</$key>\n";
                        } elseif (is_numeric($value)) {
                            $string .= "\t\t\t<$key>$value</$key>\n";
                        } elseif (is_string($value)) {
                            /* We need to escape the value */
                            $string .= "\t\t\t<$key><![CDATA[$value]]></$key>\n";
                        }
                    }
                } // end foreach

                return $string;
            default:
                foreach ($array as $key => $value) {
                    // No numeric keys
                    if (is_numeric($key)) {
                        $key = 'item';
                    }

                    if (is_array($value)) {
                        // Call ourself
                        $value = xoutput_from_array($value, true);
                        $string .= "\t<content div=\"$key\">$value</content>\n";
                    } else {
                        /* We need to escape the value */
                        $string .= "\t<content div=\"$key\"><![CDATA[$value]]></content>\n";
                    }
                    // end foreach elements
                }
                if (!$callback) {
                    $string = '<?xml version="1.0" encoding="utf-8" ?>' . "\n<root>\n" . $string . "</root>\n";
                }

                return Xml_Data::clean_utf8($string);
        }
    }

    /**
     * This will build an xml document from a key'd array,
     *
     * @param array $array keyed array of objects (key => value, key => value)
     */
    public function buildKeyedArray(
        array $array
    ): string {
        $string = '';
        // Foreach it
        foreach ($array as $key => $value) {
            $attribute = '';
            // See if the key has attributes
            if (is_array($value) && isset($value['attributes'])) {
                $attribute = ' ' . $value['attributes'];
                $key       = $value['value'];
            }

            // If it's an array, run again
            if (is_array($value)) {
                $value = $this->buildKeyedArray($value);
                $string .= "<$key$attribute>\n$value\n</$key>\n";
            } else {
                $string .= "\t<$key$attribute><![CDATA[$value]]></$key>\n";
            }
        }

        return $string;
    }
}
