<?php

/**
 * khjh
 */

namespace maxlen\parser\helpers;

use Yii;
use maxlen\proxy\helpers\Proxy;
use yii\data\ActiveDataProvider;
use common\helpers\ProxyHelpers;

class ParserL {

    const FILETYPE_PDF = 'pdf';
    
    const TYPE_NOT_PARSED = 0;
    const TYPE_PARSED = 1;
    const TYPE_DESIRED = 3;

    public static function getFromSite($site, $params = []) {
        if (!empty($params)) {
//            ['filetypes']
        }

        print_r(self::grabSite($site, $params, []));
    }

    public static function grabSite($site, $params = [], $resLinks = []) {
        $exceptions = ['mailto:', '#'];
        
        $domain = self::getDomain($site, true);

        $site = self::cleanDomain($site, true);
        $site = "http://" . $site;
        
        if (isset($resLinks[$site])) {
            $isParse = false;
            if(isset($params['exts']) && self::isHtml($site, $params['exts'], true)) {
                $resLinks[$site] = self::TYPE_DESIRED;
            }
            elseif(self::isHtml($site)) {
                $resLinks[$site] = self::TYPE_NOT_PARSED;
                $isParse = true;
            }
        }
        else {
            $isParse = true;
        }

//        echo PHP_EOL.PHP_EOL.' MAIN DOMAIN: ' . $domain.PHP_EOL;
        
        if(isset($params['exts']) && self::isHtml($site, $params['exts'], true)) {
            $resLinks[$site] = self::TYPE_DESIRED;
        }
        else {
//        print_r(count($resLinks));
            if(self::isHtml($site) && $isParse) {
                $result = ProxyHelpers::getHtmlByUrl($site, ['getInfo' => true, 'content_type' => ['html']]);
                
                $addDomain = true;
                if(!$result || $result['info']['http_code'] == 301) {
                    
                    unset($resLinks[$site]);
                    $site = $result['info']['redirect_url'];
                    $resLinks[$site] = self::TYPE_NOT_PARSED;
//                    die($site);
                    $result = ProxyHelpers::getHtmlByUrl($site, ['getInfo' => true, 'content_type' => ['html']]);
                    $domain = self::addWww($site);
                    $addDomain = false;
                }

                if($result) {
                    $parseDom = parse_url($site);
                    $siteDomain = $parseDom['host'];
                            
                    \phpQuery::newDocument($result['page']);
                    $links = pq('a');
                    foreach ($links as $link) {
                        $hrefTmp = pq($link)->attr('href');

                        if(in_array($hrefTmp, $exceptions)) {
                            continue;
                        }

                        $href = $hrefTmp;

//                        echo PHP_EOL. ' URL: ' . $href;

                        $hrefDomain = self::getDomain($href);
                        if(!is_null($hrefDomain) && self::cleanDomain($hrefDomain) != self::cleanDomain($domain)) {
                            continue;
                        }

//                        echo ' URL DOMAIN: ' . $hrefDomain;
//                        echo ' URL BEFORE: ' . $href;
                        
                        if($addDomain) {
                            if(is_null($hrefDomain)/* && strpos($href, $domain)*/) {
                                $href = (strpos($href, '/') == 0) ? "{$siteDomain}{$href}" : "{$siteDomain}/{$href}";
                            }
                            else {
                                $href = self::cleanDomain($href);
                            }

                            $href = "http://" . $href;
                        }
//                        echo ' URL AFTER: ' . $href;

                        if(!isset($resLinks[$href]) && !isset($resLinks[rtrim($href, '/')])) {
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
                    
//                    var_dump($result['info']);
                    print_r($resLinks);
//                    die();
                }
                else {
                    unset($resLinks[$site]);
                }
            }
            else {
                unset($resLinks[$site]);
            }
        }
        
//        print_r($resLinks);

        foreach ($resLinks as $link => $isFinished) {
            if ($isFinished == self::TYPE_NOT_PARSED) {
                return self::grabSite($link, $params, $resLinks);
            }
        }

        return $resLinks;
    }
    
    public static function getDomain($url, $isDom = false) {
        
        if($isDom) {
            $parse = parse_url($url);
            $domain = (isset($parse['host']) && !is_null($parse['host'])) ? $parse['host'] : rtrim($url, '/');
        }
        else {
            $parse = parse_url($url);
            $domain = (isset($parse['host']) && !is_null($parse['host'])) ? $parse['host'] : null;
        }
        
        if(!is_null($domain)) {
            $domain = self::cleanDomain($domain);
        }
        
        return $domain;
    }
    
    public static function cleanDomain($url, $saveWww = false) {
        if(strpos($url, 'http://') == 0)
            $url = str_replace ('http://', '', $url);

        if(!$saveWww && strpos($url, 'www.') == 0)
            $url = str_replace ('www.', '', $url);
        
        return $url;
    }
    
    public static function isHtml($url, $extensions = [], $yes = false) {
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        
        if(empty($extensions)) {
            return in_array($extension, ['jpg', 'bmp', 'png', 'gif', 'iso', 'avi', 'mov', 'doc', 'docx', 'pdf', 'txt', 'rtf']) ? false : true;
        }
        else {
            if($yes) {
                return in_array($ext, $extensions) ? true : false;
            }
            else {
                return !in_array($ext, $extensions) ? true : false;
            }
        }
    }
    
    public static function addWww($url) {
        
        if(!strpos($url, 'www.')) {
            $url = 'http://www.' . str_replace ('http://', '', $url);
        }
        
        return $url;
    }

}
