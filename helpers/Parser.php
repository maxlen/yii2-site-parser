<?php namespace maxlen\parser\helpers;

use Yii;
use maxlen\proxy\helpers\Proxy;
use yii\data\ActiveDataProvider;
use common\helpers\ProxyHelpers;
use maxlen\parser\models\ParserLinks;

class Parser
{

    const FILETYPE_PDF = 'pdf';
    const TYPE_NOT_PARSED = 0;
    const TYPE_PROCESS = 1;
    const TYPE_PARSED = 2;
    const TYPE_DESIRED = 3;

    /**
     * 
     * @param string $site - domain (example: pdffiller.com)
     * @param array $params - addition parameters
     *                  'exts' - (array) file extensions wich must be grabbed (example: ['pdf', 'doc'])
     *                  'parseSubdomains' - (bool) is parse links from subdomains (default = true)
     * @return array - links
     */
    public static function getFromDomain($site, $params = [])
    {   
//        require_once 'vendor/maxlen/yii2-site-parser/models/ParserLinks.php'; // must be deleted
        
        $exceptions = ['mailto:', '#'];
        $params['exceptions'] = $exceptions;

        $parseSubdomains = true;
        if(isset($params['parseSubdomains'])) {
            $parseSubdomains = $params['parseSubdomains'];
        }
        
        $resLinks = [];

        $site = self::cleanUrl($site);

        $domain = self::getDomain($site, true);
        $site = self::cleanDomain($site, true);
        $params['domain'] = $site;
        $site = "http://" . $site;
        
        $newLink = new ParserLinks;
        $newLink->link = $site;
        
        if($newLink->save()) {
//            self::parseByLink($params);
        }
    }
    
    public static function parseByLink($params)
    {
        require_once 'vendor/maxlen/yii2-site-parser/models/ParserLinks.php'; // must be deleted

        $links = ParserLinks::find()->where(['status' => self::TYPE_NOT_PARSED])->all();
//        var_dump($links);
        
//        foreach ($links as $link) {
//            $link->status = self::TYPE_PROCESS;
//            $link->save();
//        }
        
        foreach ($links as $link) {
            $link->status = self::TYPE_PROCESS;
            $link->save();
            
            $command = "php yii spider/spider-site-parse/parse-test {$params['domain']} {$link->id}";
//            $command = "php yii spider/spider-site-parse/parse-test {$params['domain']} {$link->id}";
//            $command = "php yii spider/spider-site-parse/parse-test {$params['domain']} {$link->id} > /dev/null 2>&1";
            echo $command;
            echo exec($command);
//            self::grabLinks($link, $params);
        }
    }
    
    public static function grabLinks($site, $params) {
        
        require_once 'vendor/maxlen/yii2-site-parser/models/ParserLinks.php'; // must be deleted
        
        $result = ProxyHelpers::getHtmlByUrl($site->link, ['getInfo' => true, 'content_type' => ['html']]);

        if ($result !== FALSE && in_array($result['info']['http_code'], [404])) {
            $site->delete();
            return;
        }

        if (!$result || in_array($result['info']['http_code'], [301])) {
            $site->link = $result['info']['redirect_url'];
            
            if(!$site->save()) {
                return;
            }

            $result = ProxyHelpers::getHtmlByUrl($site->link, ['getInfo' => true, 'content_type' => ['html']]);
        }

        if ($result) {
            $parseDom = parse_url($site->link);

            if ($parseDom['host'] != '') {
                $siteDomain = $parseDom['host'];
            }

            \phpQuery::newDocument($result['page']);
            $links = pq('a');

            foreach ($links as $link) {

                $href = pq($link)->attr('href');

                if (in_array($href, $params['exceptions'])) {
                    continue;
                }

                $hrefDomain = self::getDomain($href);

                if (!is_null($hrefDomain)) {
                    $isSource = strpos(self::cleanDomain($hrefDomain), self::cleanDomain($params['domain']));
                    if($isSource !== false) {
                        if(!$parseSubdomains && $isSource != 0) {
                            continue;
                        }
                    }
                    else {
                        continue;
                    }
                }

                if (is_null($hrefDomain) && isset($siteDomain)) {
                    if (is_null($hrefDomain)) {
                        $href = (strpos($href, '/') !== FALSE && strpos($href, '/') == 0) ? "{$siteDomain}{$href}" : "{$siteDomain}/{$href}";
                    } else {
                        $href = self::cleanDomain($href);
                    }

                    $href = "http://" . $href;
                }

                $href = self::cleanUrl($href);
                
                $approved = true;
                foreach ($params['exceptions'] as $exception) {
                    if (stripos($href, $exception)) {
                        $approved = false;
                        break;
                    }
                }

                if ($approved) {
                    $newLink = new ParserLinks;
                    $newLink->link = $href;
                    $newLink->save();
                }
            }

            $site->status = self::TYPE_PARSED;
            $site->save();
        }
        else {
            $site->delete();
        }
    }
    
    /**
     * 
     * @param string $site - domain (example: pdffiller.com)
     * @param array $params - addition parameters
     *                  'exts' - (array) file extensions wich must be grabbed (example: ['pdf', 'doc'])
     *                  'parseSubdomains' - (bool) is parse links from subdomains (default = true)
     * @return array - links
     */
    public static function getFromSite($site, $params = [])
    {
        require_once 'vendor/maxlen/yii2-site-parser/models/ParserLinks.php'; // must be deleted
        
        $exceptions = ['mailto:', '#'];
        $params['exceptions'] = $exceptions;

        $parseSubdomains = true;
        if(isset($params['parseSubdomains'])) {
            $parseSubdomains = $params['parseSubdomains'];
        }
        
        $resLinks = [];

        $site = self::cleanUrl($site);

        $domain = self::getDomain($site, true);
        $site = self::cleanDomain($site, true);
        $site = "http://" . $site;

        $resLinks[$site] = self::TYPE_NOT_PARSED;
        
        $newLink = new ParserLinks;
        $newLink->link = $site;
//        var_dump(ParserLinks::addNew($site));

        $continueParsing = false;
        
        if($newLink->save()) {

            do {
                if ($resLinks[$site] == self::TYPE_NOT_PARSED) {
                    ParserLinks::updateAll(['status' => self::TYPE_PROCESS], ['link' => $site]);
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
                            // save to pdf-forms
                            $resLinks[$site] = self::TYPE_DESIRED;
                        } elseif (self::isHtml($site)) {
                            $resLinks[$site] = self::TYPE_NOT_PARSED;
                            
                            $newLink = new ParserLinks;
                            $newLink->link = $site;
                            $newLink->save();
                            
                            $isParse = true;
                        }
                    } else {
                        $isParse = true;
                    }

                    if (isset($params['exts']) && self::isHtml($site, $params['exts'], true)) {
                        // save to pdf-forms
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
                                
                                $newLink = new ParserLinks;
                                $newLink->link = $site;
                                $newLink->save();
                                
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
    //                                if(!is_null($hrefDomain) && self::cleanDomain($hrefDomain) != self::cleanDomain($domain)) {
    ////                                    echo 'NOT SUB ';
    //                                    continue;
    //                                }

                                    if (!is_null($hrefDomain)) {
    //                                    var_dump(strpos(self::cleanDomain($hrefDomain), self::cleanDomain($domain)));
                                        $isSource = strpos(self::cleanDomain($hrefDomain), self::cleanDomain($domain));
                                        if($isSource !== false) {
                                            if(!$parseSubdomains && $isSource != 0) {
                                                continue;
                                            }
                                        }
                                        else {
                                            continue;
                                        }
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
                                            
                                            $newLink = new ParserLinks;
                                            $newLink->link = $site;
                                            $newLink->save();
                                        }
                                    }
                                }

                                $resLinks[$site] = self::TYPE_PARSED;
                                ParserLinks::updateAll(['status' => self::TYPE_PARSED], ['link' => $site]);
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
                
                
                
                if(count($resLinks) > 20) {
                    $continueParsing = true;
                    break;
                }
                    
            } while ($site = array_search(self::TYPE_NOT_PARSED, $resLinks));
            
            if($continueParsing) {
                self::addLinksToDb($links, $params);
                echo 'OK!';
            }
        }


        return $resLinks;
    }
    
    /**
     * 
     * @param string $site - domain (example: pdffiller.com)
     * @param array $params - addition parameters
     *                  'exts' - (array) file extensions wich must be grabbed (example: ['pdf', 'doc'])
     *                  'parseSubdomains' - (bool) is parse links from subdomains (default = true)
     * @return array - links
     */
    public static function getFromSite2($site, $params = [])
    {
        $exceptions = ['mailto:', '#'];

        $parseSubdomains = true;
        if(isset($params['parseSubdomains'])) {
            $parseSubdomains = $params['parseSubdomains'];
        }
        
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

                                if (!is_null($hrefDomain)) {
                                    $isSource = strpos(self::cleanDomain($hrefDomain), self::cleanDomain($domain));
                                    if($isSource !== false) {
                                        if(!$parseSubdomains && $isSource != 0) {
                                            continue;
                                        }
                                    }
                                    else {
                                        continue;
                                    }
                                }

                                if (is_null($hrefDomain) && isset($siteDomain)) {
                                    if (is_null($hrefDomain)) {
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
            }
            
            return !in_array($ext, $extensions) ? true : false;
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
    
    public static function addLinksToDb($links, $params = []) {
        
    }
}
