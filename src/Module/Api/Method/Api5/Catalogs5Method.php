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

use Ampache\Repository\Model\Catalog;
use Ampache\Module\Api\Api5;
use Ampache\Module\Api\Json5_Data;
use Ampache\Module\Api\Xml5_Data;
use Ampache\Module\System\Session;
use Ampache\Repository\Model\User;

/**
 * Class Catalogs5Method
 */
final class Catalogs5Method
{
    const ACTION = 'catalogs';

    /**
     * catalogs
     * MINIMUM_API_VERSION=420000
     *
     * Get information about catalogs this user is allowed to manage.
     *
     * @param array $input
     * filter = (string) set $filter_type //optional
     * offset = (integer) //optional
     * limit  = (integer) //optional
     * @return boolean
     */
    public static function catalogs(array $input): bool
    {
        // filter for specific catalog types
        $filter   = (isset($input['filter']) && in_array($input['filter'], array('music', 'clip', 'tvshow', 'movie', 'personal_video', 'podcast'))) ? $input['filter'] : '';
        $user     = User::get_from_username(Session::username($input['auth']));
        $catalogs = Catalog::get_catalogs($filter, $user->id);

        if (empty($catalogs)) {
            Api5::empty('catalog', $input['api_format']);

            return false;
        }

        ob_end_clean();
        switch ($input['api_format']) {
            case 'json':
                Json5_Data::set_offset($input['offset'] ?? 0);
                Json5_Data::set_limit($input['limit'] ?? 0);
                echo Json5_Data::catalogs($catalogs);
                break;
            default:
                Xml5_Data::set_offset($input['offset'] ?? 0);
                Xml5_Data::set_limit($input['limit'] ?? 0);
                echo Xml5_Data::catalogs($catalogs);
        }

        return true;
    }
}