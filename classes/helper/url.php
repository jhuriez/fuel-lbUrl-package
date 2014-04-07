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
     * @param  string  $urlTarget  [description]
     * @param  string  $slug       [description]
     * @param  string  $prefix     [description]
     * @param  string  $suffix     [description]
     * @param  string  $code       [description]
     * @param  string  $randomType [description]
     * @param  int     $length     [description]
     * @return [type]              [description]
     */
    public static function generate($urlTarget, $slug = false, $prefix = false, $suffix = false, $code = '302', $method = 'location', $randomType = false, $length = false)
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
        );

        $url = self::forge($data);
        $url = self::manage($url);

        return $url;
    }

    public static function randomSlug($randomType = false, $length = false)
    {
        // Default value
        $randomType = ($randomType) ? : \Config::get('url.generate.type');
        $length = ($length) ? : \Config::get('url.generate.length');

        // Slug
        $slug = \Str::random($randomType, $length);

        return $slug;
    }

    public static function redirect($url)
    {
        (is_numeric($url) or is_string($url)) and $url = self::find($url, true);

        if ($url === false) return false;

        $uri = self::getUrl($url);

        // Increment hit
        $url->hits++;
        $url = self::manage($url);

        \Response::redirect($uri, $url->method, $url->code);
    }

    public static function toggleActive($url)
    {
        (is_numeric($url) or is_string($url)) and $url = self::find($url, false, false);

        if ($url === false) return false;

        $url->active = !$url->active;

        return self::manage($url);
    }

    public static function getUrl($url, $slug = false)
    {
        $regex = '/^([a-zA-Z0-9]+(\.[a-zA-Z0-9]+)+.*)$/';

        if ($slug)
        {
            $val = \Router::get('module_url_redirect', array('slug' => $url->slug));
        }
        else
        {
            $val = $url->url_target;
        }

        $isUrl = (preg_match($regex, $val) || filter_var($val, FILTER_VALIDATE_URL));

        $uri = ($isUrl) ? $val : \Uri::base() . $val;
        return $uri;
    }

    /**
     * ALL helper functions for manage the Url model
     */

    public static function forge($data = array())
    {
        return \LbUrl\Model_Url::forge($data);
    }

    public static function getAllUrls($getMaster = true, $active = false)
    {
        $urls = \LbUrl\Model_Url::query()->where('id_url_master', '=', NULL);
        $active and $urls->where('active', true);

        return $urls->get();
    }

    public static function find($id, $active = false, $getMaster = true, $strict = false)
    {
        // Find object
        $url = (is_numeric($id)) ? \LbUrl\Model_Url::find($id) : \LbUrl\Model_Url::query()->where('slug', $id)->get_one();

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
     * @param Object $url  [description]
     * @param array $data [description]
     */
    protected static function setData($url, $data)
    {
        // Set value
        foreach($data as $attr => $value)
        {
            if ($attr == 'slug')
            {
                $value = \Inflector::friendly_title($value, '-');
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