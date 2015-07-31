<?php namespace maxlen\parser\helpers;

use Yii;
use maxlen\proxy\helpers\Proxy;
use yii\data\ActiveDataProvider;
use common\helpers\ProxyHelpers;

class Parser
{

    const FILETYPE_PDF = 'pdf';
    const TYPE_NOT_PARSED = 0;
    const TYPE_PARSED = 1;
    const TYPE_DESIRED = 3;

    public static function getFromSite($site, $params = [])
    {
        $exceptions = ['mailto:', '#'];

        $resLinks = [];

        $site = self::cleanUrl($site);

        $domain = self::getDomain($site, true);
        $site = self::cleanDomain($site, true);
        $site = "http://" . $site;

        $resLinks[$site] = self::TYPE_NOT_PARSED;

        do {
            if ($resLinks[$site] == self::TYPE_NOT_PARSED) {
                print_r($resLinks);
                var_dump(count($resLinks));

                $cleanSite = $site;

                $site = self::cleanUrl($site);

                $domain = self::getDomain($site, true);
                $site = self::cleanDomain($site, true);
                $site = "http://" . $site;

                if (isset($resLinks[$site])) {
                    $isParse = false;
                    if (isset($params['exts']) && self::isHtml($site, $params['exts'], true)) {
                        $resLinks[$site] = self::TYPE_DESIRED;
                    } elseif (self::isHtml($site)) {
                        $resLinks[$site] = self::TYPE_NOT_PARSED;
                        $isParse = true;
                    }
                } else {
                    $isParse = true;
                }

                if (isset($params['exts']) && self::isHtml($site, $params['exts'], true)) {
                    $resLinks[$site] = self::TYPE_DESIRED;
                } else {
                    if (self::isHtml($site) && $isParse) {
                        $result = ProxyHelpers::getHtmlByUrl($site, ['getInfo' => true, 'content_type' => ['html']]);

                        $addDomain = true;

                        if ($result !== FALSE && in_array($result['info']['http_code'], [404])) {
                            unset($resLinks[$site]);
                            unset($resLinks[$cleanSite]);
                            continue;
                        }

                        if (!$result || in_array($result['info']['http_code'], [301])) {
                            unset($resLinks[$site]);
                            unset($resLinks[$cleanSite]);
                            $site = $result['info']['redirect_url'];
                            $resLinks[$site] = self::TYPE_NOT_PARSED;
                            $result = ProxyHelpers::getHtmlByUrl($site, ['getInfo' => true, 'content_type' => ['html']]);

                            if ($result) {
                                $domain = self::getDomain($site, false, true);
                            }

                            $addDomain = false;
                        }

                        if ($result) {
                            $parseDom = parse_url($site);

                            if ($parseDom['host'] != '') {
                                $siteDomain = $parseDom['host'];
                            }

                            \phpQuery::newDocument($result['page']);
                            $links = pq('a');

                            foreach ($links as $link) {

                                $href = pq($link)->attr('href');

                                if (in_array($href, $exceptions)) {
                                    continue;
                                }

                                $hrefDomain = self::getDomain($href);
                                if (!is_null($hrefDomain) && self::cleanDomain($hrefDomain) != self::cleanDomain($domain)) {
                                    continue;
                                }

                                if (is_null($hrefDomain) && isset($siteDomain)) {
                                    if (is_null($hrefDomain)/* && strpos($href, $domain) */) {
                                        $href = (strpos($href, '/') !== FALSE && strpos($href, '/') == 0) ? "{$siteDomain}{$href}" : "{$siteDomain}/{$href}";
                                    } else {
                                        $href = self::cleanDomain($href);
                                    }

                                    $href = "http://" . $href;
                                }

                                $href = self::cleanUrl($href);

                                if (!isset($resLinks[$href]) && !isset($resLinks[rtrim($href, '/')])) {
                                    $approved = true;
                                    foreach ($exceptions as $exception) {
                                        if (stripos($href, $exception)) {
                                            $approved = false;
                                            break;
                                        }
                                    }

                                    if ($approved) {
                                        $resLinks[$href] = self::TYPE_NOT_PARSED;
                                    }
                                }
                            }

                            $resLinks[$site] = self::TYPE_PARSED;
                        } else {
                            unset($resLinks[$site]);
                            unset($resLinks[$cleanSite]);
                        }
                    } else {
                        unset($resLinks[$site]);
                        unset($resLinks[$cleanSite]);
                    }
                }
            }
        } while ($site = array_search(self::TYPE_NOT_PARSED, $resLinks));


        return $resLinks;
    }

    public static function getDomain($url, $isDom = false, $saveWww = false)
    {

        if ($isDom) {
            $parse = parse_url($url);
            $domain = (isset($parse['host']) && !is_null($parse['host'])) ? $parse['host'] : rtrim($url, '/');
        } else {
            $parse = parse_url($url);
            $domain = (isset($parse['host']) && !is_null($parse['host'])) ? $parse['host'] : null;
        }

        if (!is_null($domain)) {
            $domain = self::cleanDomain($domain, $saveWww);
        }

        return $domain;
    }

    public static function cleanDomain($url, $saveWww = false)
    {
        if (strpos($url, 'http://') == 0)
            $url = str_replace('http://', '', $url);

        if (!$saveWww && strpos($url, 'www.') == 0)
            $url = str_replace('www.', '', $url);

        return $url;
    }

    public static function isHtml($url, $extensions = [], $yes = false)
    {
        $ext = pathinfo($url, PATHINFO_EXTENSION);

        if (empty($extensions)) {
            return in_array($ext, ['jpg', 'bmp', 'png', 'gif', 'iso', 'avi', 'mov', 'doc', 'docx', 'pdf', 'txt', 'rtf']) ? false : true;
        } else {
            if ($yes) {
                return in_array($ext, $extensions) ? true : false;
            } else {
                return !in_array($ext, $extensions) ? true : false;
            }
        }
    }

    public static function addWww($url)
    {

        if (!strpos($url, 'www.')) {
            $url = 'http://www.' . str_replace('http://', '', $url);
        }

        return $url;
    }

    public static function cleanUrl($url)
    {
        if (strpos($url, '../')) {
            $url = str_replace('../', '', $url);
        }

        return $url;
    }

    public static function getBaseDomain($url)
    {
        $debug = 0;
        $base_domain = '';

        $G_TLD = array(
            'biz', 'com', 'edu', 'gov', 'info', 'int', 'mil', 'name', 'net', 'org',
            'aero', 'asia', 'cat', 'coop', 'jobs', 'mobi', 'museum', 'pro', 'tel', 'travel',
            'arpa', 'root',
            'berlin', 'bzh', 'cym', 'gal', 'geo', 'kid', 'kids', 'lat', 'mail', 'nyc', 'post', 'sco', 'web', 'xxx',
            'nato',
            'example', 'invalid', 'localhost', 'test',
            'bitnet', 'csnet', 'ip', 'local', 'onion', 'uucp',
            'co'   // note: not technically, but used in things like co.uk
        );

        $C_TLD = array(
            // active
            'ac', 'ad', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'as', 'at', 'au', 'aw', 'ax', 'az',
            'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'bj', 'bm', 'bn', 'bo', 'br', 'bs', 'bt', 'bw', 'by', 'bz',
            'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cu', 'cv', 'cx', 'cy', 'cz',
            'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'ee', 'eg', 'er', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo',
            'fr', 'ga', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw',
            'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'io', 'iq', 'ir', 'is', 'it', 'je',
            'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk',
            'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'mg', 'mh', 'mk', 'ml', 'mm', 'mn', 'mo', 'mp', 'mq',
            'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'nc', 'ne', 'nf', 'ng', 'ni', 'nl', 'no', 'np',
            'nr', 'nu', 'nz', 'om', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pn', 'pr', 'ps', 'pt', 'pw', 'py', 'qa',
            're', 'ro', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sk', 'sl', 'sm', 'sn', 'sr', 'st',
            'sv', 'sy', 'sz', 'tc', 'td', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tr', 'tt', 'tv', 'tw',
            'tz', 'ua', 'ug', 'uk', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'ye', 'yu',
            'za', 'zm', 'zw',
            // inactive
            'eh', 'kp', 'me', 'rs', 'um', 'bv', 'gb', 'pm', 'sj', 'so', 'yt', 'su', 'tp', 'bu', 'cs', 'dd', 'zr'
        );


        // get domain
        if (!$full_domain = self::getUrlDomain($url)) {
            return $base_domain;
        }

        // now the fun
        // break up domain, reverse
        $DOMAIN = explode('.', $full_domain);
        if ($debug)
            print_r($DOMAIN);
        $DOMAIN = array_reverse($DOMAIN);
        if ($debug)
            print_r($DOMAIN);

        // first check for ip address
        if (count($DOMAIN) == 4 && is_numeric($DOMAIN[0]) && is_numeric($DOMAIN[3])) {
            return $full_domain;
        }

        // if only 2 domain parts, that must be our domain
        if (count($DOMAIN) <= 2)
            return $full_domain;

        if (in_array($DOMAIN[0], $C_TLD) && in_array($DOMAIN[1], $G_TLD) && $DOMAIN[2] != 'www') {
            $full_domain = $DOMAIN[2] . '.' . $DOMAIN[1] . '.' . $DOMAIN[0];
        } else {
            $full_domain = $DOMAIN[1] . '.' . $DOMAIN[0];
            ;
        }

        return $full_domain;
    }

    public static function getUrlDomain($url)
    {
        $domain = '';

        $_URL = parse_url($url);

        // sanity check
        if (empty($_URL) || empty($_URL['host'])) {
            $domain = '';
        } else {
            $domain = $_URL['host'];
        }

        return $domain;
    }
}
