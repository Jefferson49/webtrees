<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2023 webtrees development team
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

namespace Fisharebest\Webtrees\Schema;

use Fisharebest\Webtrees\DB;

/**
 * Populate the gedcom table
 */
class SeedGedcomTable implements SeedInterface
{
    /**
     *  Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        // Add a "default" tree, to store default settings

        if (DB::driverName() === 'sqlsrv') {
            DB::connection()->unprepared('SET IDENTITY_INSERT [' . DB::prefix() . 'gedcom] ON');
        }

        DB::table('gedcom')->updateOrInsert([
            'gedcom_id'   => -1,
        ], [
            'gedcom_name'  => 'DEFAULT_TREE',
        ]);

        if (DB::driverName() === 'sqlsrv') {
            DB::connection()->unprepared('SET IDENTITY_INSERT [' . DB::prefix() . 'gedcom] OFF');
        }
    }
}
