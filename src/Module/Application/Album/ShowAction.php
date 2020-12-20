<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 *  LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
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
 *
 */

declare(strict_types=0);

namespace Ampache\Module\Application\Album;

use Ampache\Model\ModelFactoryInterface;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\System\LegacyLogger;
use Ampache\Module\Util\Ui;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ShowAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'show';
    
    private ModelFactoryInterface $modelFactory;

    private UiInterface $ui;

    private LoggerInterface $logger;

    public function __construct(
        ModelFactoryInterface $modelFactory,
        UiInterface $ui,
        LoggerInterface $logger
    ) {
        $this->modelFactory = $modelFactory;
        $this->ui           = $ui;
        $this->logger       = $logger;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        require_once Ui::find_template('header.inc.php');
        
        $album = $this->modelFactory->createAlbum((int) $_REQUEST['album']);
        $album->format();
        if (!$album->id) {
            $this->logger->warning(
                'Requested an album that does not exist',
                [LegacyLogger::CONTEXT_TYPE => __CLASS__]
            );
            echo T_("You have requested an Album that does not exist.");
        // allow single disks to not be shown as multi's
        } elseif (count($album->album_suite) <= 1) {
            require Ui::find_template('show_album.inc.php');
        } else {
            require Ui::find_template('show_album_group_disks.inc.php');
        }

        // Show the Footer
        $this->ui->showQueryStats();
        $this->ui->showFooter();
        
        return null;
    }
}