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

use Ampache\Config\AmpConfig;
use Ampache\Repository\Model\User;
use Ampache\Module\Api\Api5;
use Ampache\Module\Api\Json5_Data;
use Ampache\Module\Api\Xml5_Data;
use Ampache\Repository\UserFollowerRepositoryInterface;

/**
 * Class Following5Method
 */
final class Following5Method
{
    public const ACTION = 'following';

    /**
     * following
     * MINIMUM_API_VERSION=380001
     * CHANGED_IN_API_VERSION=400004
     *
     * Get users followed by the user
     * Error when user not found or no followers
     *
     * @param array $input
     * username = (string) $username
     * @return boolean
     */
    public static function following(array $input): bool
    {
        if (!AmpConfig::get('sociable')) {
            Api5::error(T_('Enable: sociable'), '4703', self::ACTION, 'system', $input['api_format']);

            return false;
        }
        if (!Api5::check_parameter($input, array('username'), self::ACTION)) {
            return false;
        }
        $username = $input['username'];

        $user     = User::get_from_username($username);
        if (!$user->id) {
            debug_event(self::class, 'User `' . $username . '` cannot be found.', 1);
            /* HINT: Requested object string/id/type ("album", "myusername", "some song title", 1298376) */
            Api5::error(sprintf(T_('Not Found: %s'), $username), '4704', self::ACTION, 'username', $input['api_format']);

            return false;
        }

        $users = static::getUserFollowerRepository()->getFollowing($user->getId());

        if (empty($users)) {
            Api5::empty('user', $input['api_format']);

            return false;
        }

        ob_end_clean();
        switch ($input['api_format']) {
            case 'json':
                echo Json5_Data::users($users);
                break;
            default:
                echo Xml5_Data::users($users);
        }

        return true;
    }

    /**
     * @deprecated Inject by constructor
     */
    private static function getUserFollowerRepository(): UserFollowerRepositoryInterface
    {
        global $dic;

        return $dic->get(UserFollowerRepositoryInterface::class);
    }
}