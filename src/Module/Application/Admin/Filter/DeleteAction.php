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
 *
 */

declare(strict_types=0);

namespace Ampache\Module\Application\Admin\Filter;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Config\ConfigurationKeyEnum;
use Ampache\Repository\Model\ModelFactoryInterface;
use Ampache\Module\Util\UiInterface;
use Ampache\Repository\Model\Catalog;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DeleteAction extends AbstractFilterAction
{
    public const REQUEST_KEY = 'delete';

    private UiInterface $ui;

    public function __construct(
        UiInterface $ui,
        ConfigContainerInterface $configContainer
    ) {
        $this->ui              = $ui;
        $this->configContainer = $configContainer;
    }

    protected function handle(ServerRequestInterface $request): ?ResponseInterface
    {
        if ($this->configContainer->isFeatureEnabled(ConfigurationKeyEnum::DEMO_MODE) === true) {
            return null;
        }

        $this->ui->showHeader();

        $filter_id   = (int) $request->getQueryParams()['filter_id'] ?? 0;
        $filter_name = $request->getQueryParams()['filter_name'];
        $this->ui->showConfirmation(
            T_('Are You Sure?'),
            sprintf(T_('This will permanently delete the catalog filter "%s"<br>All users assigned to that catalog filter will be re-assgined to the DEFAULT filter.'), $filter_name),
            sprintf(
                'admin/filter.php?action=confirm_delete&amp;filter_id=%s&amp;filter_name=%s',
                $filter_id,
                $filter_name
            ),
            1,
            'delete_user'
        );

        $this->ui->showQueryStats();
        $this->ui->showFooter();

        return null;
    }
}