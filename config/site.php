<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active theme
    |--------------------------------------------------------------------------
    |
    | Selects which Blade theme renders. When set, AppServiceProvider prepends
    | resources/views/themes/<theme> to the view finder, so any page, layout or
    | component placed there overrides the shared base in resources/views; views
    | the theme doesn't define fall back to base. Unset = pure base (the default).
    |
    | One codebase, deployed once per site (see deploy/ansible); each site's .env
    | sets SITE_THEME so the same code renders a different skin per domain. The
    | controllers and the data they pass never change — only the templates do.
    |
    */

    'theme' => env('SITE_THEME'),

];
