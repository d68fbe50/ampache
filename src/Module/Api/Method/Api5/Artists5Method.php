<?php

/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 *  LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2022 Ampache.org
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

namespace Ampache\Module\Api\Method\Api5;

use Ampache\Repository\Model\User;
use Ampache\Module\Api\Api;
use Ampache\Module\Api\Api5;
use Ampache\Module\Api\Json5_Data;
use Ampache\Module\Api\Xml5_Data;
use Ampache\Module\System\Session;

/**
 * Class Artists5Method
 */
final class Artists5Method
{
    const ACTION = 'artists';

    /**
     * artists
     * MINIMUM_API_VERSION=380001
     *
     * This takes a collection of inputs and returns
     * artist objects. This function is deprecated!
     *
     * @param array $input
     * filter       = (string) Alpha-numeric search term //optional
     * exact        = (integer) 0,1, if true filter is exact rather then fuzzy //optional
     * add          = Api::set_filter(date) //optional
     * update       = Api::set_filter(date) //optional
     * include      = (array|string) 'albums', 'songs' //optional
     * album_artist = (integer) 0,1, if true filter for album artists only //optional
     * offset       = (integer) //optional
     * limit        = (integer) //optional
     * @return boolean
     */
    public static function artists(array $input): bool
    {
        $album_artist = (array_key_exists('album_artist', $input) && (int)$input['album_artist'] == 1);
        $browse       = Api::getBrowse();
        $browse->reset_filters();
        if ($album_artist) {
            $browse->set_type('album_artist');
        } else {
            $browse->set_type('artist');
        }
        $browse->set_sort('name', 'ASC');

        $method = (array_key_exists('exact', $input) && (int)$input['exact'] == 1) ? 'exact_match' : 'alpha_match';
        Api::set_filter($method, $input['filter'] ?? '', $browse);
        Api::set_filter('add', $input['add'] ?? '', $browse);
        Api::set_filter('update', $input['update'] ?? '', $browse);

        $artists = $browse->get_objects();
        if (empty($artists)) {
            Api5::empty('artist', $input['api_format']);

            return false;
        }

        ob_end_clean();
        $user    = User::get_from_username(Session::username($input['auth']));
        $include = [];
        if (array_key_exists('include', $input)) {
            $include = (is_array($input['include'])) ? $input['include'] : explode(',', (string)$input['include']);
        }
        switch ($input['api_format']) {
            case 'json':
                Json5_Data::set_offset($input['offset'] ?? 0);
                Json5_Data::set_limit($input['limit'] ?? 0);
                echo Json5_Data::artists($artists, $include, $user->id);
                break;
            default:
                Xml5_Data::set_offset($input['offset'] ?? 0);
                Xml5_Data::set_limit($input['limit'] ?? 0);
                echo Xml5_Data::artists($artists, $include, $user->id);
        }

        return true;
    }
}