<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2025 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Fisharebest\Webtrees\Http\RequestHandlers;

use DateTimeImmutable;
use DateTimeZone;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\DB;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\Services\UserService;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function date;

/**
 * Show pending changes.
 */
class PendingChangesLogPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private TreeService $tree_service;

    private UserService $user_service;

    public function __construct(TreeService $tree_service, UserService $user_service)
    {
        $this->tree_service = $tree_service;
        $this->user_service = $user_service;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';

        $tree  = Validator::attributes($request)->tree();
        $trees = $this->tree_service->titles();
        $users = ['' => ''];

        foreach ($this->user_service->all() as $user) {
            $user_name         = $user->userName();
            $users[$user_name] = $user_name;
        }

        // First and last change in the database
        $earliest = DB::table('change')->min('change_time') ?? date('Y-m-d H:i:s');
        $latest   = DB::table('change')->max('change_time') ?? date('Y-m-d H:i:s');

        $earliest = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $earliest, new DateTimeZone('UTC'))
            ->setTimezone(new DateTimeZone(Auth::user()->getPreference(UserInterface::PREF_TIME_ZONE, 'UTC')))
            ->format('Y-m-d');

        $latest = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $latest, new DateTimeZone('UTC'))
            ->setTimezone(new DateTimeZone(Auth::user()->getPreference(UserInterface::PREF_TIME_ZONE, 'UTC')))
            ->format('Y-m-d');

        $from     = Validator::queryParams($request)->string('from', $earliest);
        $to       = Validator::queryParams($request)->string('to', $latest);
        $type     = Validator::queryParams($request)->string('type', '');
        $oldged   = Validator::queryParams($request)->string('oldged', '');
        $newged   = Validator::queryParams($request)->string('newged', '');
        $xref     = Validator::queryParams($request)->string('xref', '');
        $username = Validator::queryParams($request)->string('username', '');

        return $this->viewResponse('admin/changes-log', [
            'earliest' => $earliest,
            'from'     => $from,
            'latest'   => $latest,
            'newged'   => $newged,
            'oldged'   => $oldged,
            'statuses' => $this->changeStatuses(),
            'title'    => I18N::translate('Changes log'),
            'to'       => $to,
            'tree'     => $tree,
            'trees'    => $trees,
            'type'     => $type,
            'username' => $username,
            'users'    => $users,
            'xref'     => $xref,
        ]);
    }

    /**
     * Labels for the various statuses.
     *
     * @return array<string,string>
     */
    private function changeStatuses(): array
    {
        return [
            ''         => '',
            /* I18N: the status of an edit accepted/rejected/pending */
            'accepted' => I18N::translate('accepted'),
            /* I18N: the status of an edit accepted/rejected/pending */
            'rejected' => I18N::translate('rejected'),
            /* I18N: the status of an edit accepted/rejected/pending */
            'pending'  => I18N::translate('pending'),
        ];
    }
}
