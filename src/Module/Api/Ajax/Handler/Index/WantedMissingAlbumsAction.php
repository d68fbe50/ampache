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

namespace Ampache\Module\Api\Ajax\Handler\Index;

use Ampache\Config\AmpConfig;
use Ampache\Module\Api\Ajax\Handler\ActionInterface;
use Ampache\Module\Util\Ui;
use Ampache\Repository\Model\ModelFactoryInterface;
use Ampache\Repository\Model\User;
use Ampache\Repository\Model\Wanted;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class WantedMissingAlbumsAction implements ActionInterface
{
    private ModelFactoryInterface $modelFactory;

    public function __construct(
        ModelFactoryInterface $modelFactory
    ) {
        $this->modelFactory = $modelFactory;
    }

    public function handle(
        ServerRequestInterface $request,
        ResponseInterface $response,
        User $user
    ): array {
        $results = [];

        if (AmpConfig::get('wanted') && (isset($_REQUEST['artist']) || isset($_REQUEST['artist_mbid']))) {
            if (isset($_REQUEST['artist'])) {
                $artist = $this->modelFactory->createArtist((int) $_REQUEST['artist']);
                $artist->format();
                if ($artist->mbid) {
                    $walbums = Wanted::get_missing_albums($artist);
                } else {
                    debug_event('index.ajax', 'Cannot get missing albums: MusicBrainz ID required.', 3);
                }
            } else {
                $walbums = Wanted::get_missing_albums(null, $_REQUEST['artist_mbid']);
            }

            ob_start();
            require_once Ui::find_template('show_missing_albums.inc.php');
            $results['missing_albums'] = ob_get_clean();
        }

        return $results;
    }
}