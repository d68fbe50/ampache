<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
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

namespace Ampache\Module\Application\Stats;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Config\ConfigurationKeyEnum;
use Ampache\Module\System\Core;
use Ampache\Repository\Model\ModelFactoryInterface;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\Statistics\Stats;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class NewestAlbumAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'newest_album';

    private UiInterface $ui;

    private ModelFactoryInterface $modelFactory;

    private ConfigContainerInterface $configContainer;

    public function __construct(
        UiInterface $ui,
        ModelFactoryInterface $modelFactory,
        ConfigContainerInterface $configContainer
    ) {
        $this->ui              = $ui;
        $this->modelFactory    = $modelFactory;
        $this->configContainer = $configContainer;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        $thresh_value = $this->configContainer->get(ConfigurationKeyEnum::STATS_THRESHOLD);
        $limit        = $this->configContainer->get(ConfigurationKeyEnum::OFFSET_LIMIT);

        $this->ui->showHeader();
        $this->ui->show('show_form_newest.inc.php');

        define('TABLE_RENDERED', 1);

        // Temporary workaround to avoid sorting on custom base requests
        define('NO_BROWSE_SORTING', true);

        $user    = Core::get_global('user');
        $objects = Stats::get_newest('album', $limit, 0, 0, $user->id);
        $browse  = $this->modelFactory->createBrowse();
        $browse->set_threshold($thresh_value);
        $browse->set_type('album');
        $browse->show_objects($objects);
        $browse->store();

        $this->ui->showBoxBottom();

        show_table_render(false, true);

        $this->ui->showQueryStats();
        $this->ui->showFooter();

        return null;
    }
}