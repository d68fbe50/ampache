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
 */

declare(strict_types=0);

namespace Ampache\Module\Api\Ajax\Handler\DemocraticPlayback;

use Ampache\Module\Api\Ajax\Handler\ActionInterface;
use Ampache\Module\Authorization\Access;
use Ampache\Repository\Model\Browse;
use Ampache\Repository\Model\Democratic;
use Ampache\Repository\Model\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ClearPlaylistAction implements ActionInterface
{
    public function handle(
        ServerRequestInterface $request,
        ResponseInterface $response,
        User $user
    ): array {
        $democratic = Democratic::get_current_playlist();
        $democratic->set_parent();

        $results     = array();

        if (!Access::check('interface', 100)) {
            return ['rfc3514' => '0x1'];
        }

        $democratic = new Democratic($_REQUEST['democratic_id']);
        $democratic->set_parent();
        $democratic->clear();

        ob_start();
        $object_ids = $democratic->get_items();
        $browse     = new Browse();
        $browse->set_type('democratic');
        $browse->set_static_content(false);
        $browse->show_objects($object_ids);
        $browse->store();
        $results[$browse->get_content_div()] = ob_get_contents();
        ob_end_clean();

        return $results;
    }
}
