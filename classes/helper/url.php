<?php

namespace LbUrl;


/**
 *  This class contains some helping functions for the package
 */
class Helper_Url
{

    protected static $method = array('location', 'refresh');

    /**
     * Generate rapidly a short url
     * 
     * @param  string  $urlTarget 
     * @param  string  $slug      
     * @param  string  $prefix    
     * @param  string  $suffix    
     * @param  string  $code      
     * @param  string  $randomType
     * @param  int     $length    
     * @return Model_Url             
     */
    public static function generate($urlTarget, $slug = false, $prefix = false, $suffix = false, $code = '302', $method = 'location', $randomType = false, $length = false, $isDownload = false, $expiredAt = null)
    {
        // Slug
        $slug = ($slug) ? : self::randomSlug($randomType, $length);
        $slug = (($prefix) ? : \Config::get('url.prefix')) . $slug;  
        $slug = $slug . (($suffix) ? : \Config::get('url.suffix') ? : '');

        $data = array(
            'url_target' => $urlTarget,
            'slug' => $slug,
            'code' => $code,
            'method' => $method,
            'active' => true,
            'is_download' => $isDownload,
            'expired_at' => $expiredAt
        );

        $url = self::forge($data);
        $url = self::manage($url);

        return $url;
    }

    /**
     * Get a random Slug
     * @param  mixed $randomType
     * @param  mixed $length    
     * @return string           
     */
    public static function randomSlug($randomType = false, $length = false)
    {
        // Default value
        $randomType = ($randomType) ? : \Config::get('url.generate.type');
        $length = ($length) ? : \Config::get('url.generate.length');

        // Slug
        $slug = \Str::random($randomType, $length);

        return $slug;
    }

    /**
     * Do the redirection
     * @param  Model_Url $url 
     */
    public static function redirect($url)
    {
        (is_numeric($url) or is_string($url)) and $url = self::find($url, true);
        if ($url === false) return false;

        // Check if DOCROOT in URL
        $urlTargetCompare = str_replace(['/', '\\'], ['', ''], $url->url_target);
        $docrootCompare = str_replace(['/', '\\'], ['', ''], DOCROOT);
        if (strstr($urlTargetCompare, $docrootCompare))
        {
            $uri = $url->url_target;
        }
        else
        {
            // If IsDownload and same domain, we replace domain with the DOCROOT for download
            if ($url->is_download && strstr($url->url_target, \Config::get('domains.main.host')))
            {
                $uri = $url->url_target;
                $uri = str_replace(\Config::get('domains.main.host'), DOCROOT, $uri);
                $uri = str_replace(['http://', 'https://'], ['', ''], $uri);
            }
            // Normal URL
            else
            {
                $uri = self::getUrl($url);
            }
        }

        // Increment hit
        $url->hits++;
        $url = self::manage($url);

        // Check le Expired at
        if ($url->expired_at)
        {
            if (time() > $url->expired_at)
            {
                die('URL expired');
            }
        }


        if ($url->is_download)
        {
            \File::download($uri, null, null, null, false, 'inline');
            die();
        }
        else
        {
            \Response::redirect($uri, $url->method, $url->code);
        }
    }

    /**
     * Toggle active property 
     * @param  Model_Url $url 
     * @return Model_Url      
     */
    public static function toggleActive($url)
    {
        (is_numeric($url) or is_string($url)) and $url = self::find($url, false, false);

        if ($url === false) return false;

        $url->active = !$url->active;

        return self::manage($url);
    }

    /**
     * Return the Long URL OR the Short URL
     * @param  Model_Url  $url  
     * @param  boolean $slug 
     * @return string        
     */
    public static function getUrl($url, $slug = false)
    {
        $regex = '/^([a-zA-Z0-9]+(\.[a-zA-Z0-9]+)+.*)$/';

        if ($slug)
        {
            $val = \Router::get('module_url_redirect', array('slug' => $url->slug));
        }
        else
        {
            $val = str_replace(' ', '%20', $url->url_target);
        }
        
        $isUrl = (preg_match($regex, $val) || filter_var($val, FILTER_VALIDATE_URL));
        $uri = ($isUrl) ? \Lb\Tool::url($val) : \Uri::base() . $val;
        return $uri;
    }

    /**
     * ALL helper functions for manage the Url model
     */

    public static function forge($data = array())
    {
        return \LbUrl\Model_Url::forge($data);
    }

    /**
     * Return all urls
     * @param  boolean $getMaster Only master
     * @param  boolean $active    Only active
     * @return array             
     */
    public static function getAllUrls($getMaster = true, $active = false)
    {
        $urls = \LbUrl\Model_Url::query()->where('id_url_master', '=', NULL);
        $active and $urls->where('active', true);

        return $urls->get();
    }

    /**
     * Return a Model_Url by URL target (long url)
     * @param  string  $urlTarget 
     * @param  boolean $active     Only active
     * @param  boolean $getMaster  Only master
     * @param  boolean $strict    
     * @return Model_Url             
     */
    public static function findByUrl($urlTarget, $active = false, $getMaster = true, $strict = false)
    {
        $url = \LbUrl\Model_Url::query()->where('url_target', $urlTarget)->get_one();

        // Not found
        if ($url === null)
        {
            if ($strict)
            {
                throw new \Exception('Url '.$urlTarget.' not found');
            } 
            return false;
        }
        // Url active only
        else if ($active)
        {
            // If has master and it's active, return it
            if ($getMaster && $url->url_master && $url->url_master->active && $url->active)
            {
                    return $url->url_master;
            }

            return ($url->active) ? $url : false;
        }

        // If has master, return it
        return ($getMaster && $url->url_master) ? $url->url_master : $url;
    }

    /**
     * Return a Model_Url by id or slug
     * @param  mixed   $id        
     * @param  boolean $active    Only active
     * @param  boolean $getMaster Only master
     * @param  boolean $strict    
     * @return Model_Url             
     */
    public static function find($id, $active = false, $getMaster = true, $strict = false)
    {
        // Find object
        $url = \LbUrl\Model_Url::query()->where('slug', $id)->get_one();

        if ($url == null && is_numeric($id))
        {
            $url = \LbUrl\Model_Url::find($id);
        }

        // Not found
        if ($url === null)
        {
            if ($strict)
            {
                throw new \Exception('Url '.$id.' not found');
            } 
            return false;
        }
        // Url active only
        else if ($active)
        {
            // If has master and it's active, return it
            if ($getMaster && $url->url_master && $url->url_master->active && $url->active)
            {
                    return $url->url_master;
            }

            return ($url->active) ? $url : false;
        }

        // If has master, return it
        return ($getMaster && $url->url_master) ? $url->url_master : $url;
    }

    public static function delete($url)
    {
        (is_numeric($url) or is_string($url)) and $url = self::find($url, false, false);
        return $url->delete();
    }

    public static function manage($url)
    {
        \Config::load('url', true);
        $isUpdate = ($url->is_new()) ? false : true;
        $diff = $url->get_diff();
        $data = $diff[1];

        if ($isUpdate)
        {
            // If change slug
            if (isset($data['slug']))
            {
                $oldUrl = $url;

                // Get and remove associated urls from old url
                $associatedUrls = $oldUrl->associated_urls;
                $oldUrl->reset();
                $oldUrl->associated_urls = array();
                $oldUrl->save();

                $newUrl = clone $oldUrl;
                $newUrl->associated_urls = $associatedUrls;
                $newUrl->associated_urls[] = $oldUrl;

                $url = $newUrl;
            }

            // If change target
            if (isset($data['url_target']))
            {
                $url->url_target = $data['url_target'];
                foreach($url->associated_urls as $id => $associatedUrl)
                {
                    $url->associated_urls[$id]->url_target = $data['url_target'];
                }
            }
        }

        // Set data and save
        $url = self::setData($url, $data);
        $response = $url->save();

        // Slug must be unique
        if ($response)
        {
            $countDoublon = self::getOccurence($url->id, 'url_url', 'slug', $url->slug);
            if ($countDoublon > 1)
            {
                $url->slug .= '-'.($countDoublon);
                $url->save();
            }
        }


        // Return FALSE or the object
        return ($response) ? $url : $response;
    }

    /**
     * Function for set data
     * @param Object $url 
     * @param array $data 
     */
    protected static function setData($url, $data)
    {
        // Set value
        foreach($data as $attr => $value)
        {
            if ($attr == 'slug')
            {
                // $value = \Inflector::friendly_title($value, '-');
                $url->{$attr} = $value;
            }
            if ($attr == 'method')
            {
                in_array($value, self::$method) or $value = 'location';
                $url->{$attr} = $value;
            }
        }

        return $url;
    }

    /**
     * Get occurence in table
     * @param  int  $id       Identifiant de l'objet
     * @param  string  $table    Nom de la table
     * @param  string  $attribute Nom de l'attribut
     * @param  string  $value    La valeur unique
     * @param  integer $count    le nombre d'occurence
     * @return int            Retourne le nombre d'occurence
     */
    public static function getOccurence($id, $table, $attribute, $value, $count=0)
    {
        $whereAttribute = ($count > 1) ? $value.'-'.$count : $value;
        $res = \DB::select('*')->from($table)->where($attribute, '=', $whereAttribute)->where('id', '!=', $id)->execute()->as_array();
        
        if (!empty($res))
        {
            $count++;
            return self::getOccurence($id, $table, $attribute, $value, $count);
        }
        else
        {
            return $count;
        }
    }

}