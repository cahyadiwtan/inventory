<?php

foreach (App\Models\User::all() as $u) {
    echo $u->email.' | roles: '.$u->getRoleNames()->join(', ').PHP_EOL;
}
echo 'Roles: '.Spatie\Permission\Models\Role::count().PHP_EOL;
