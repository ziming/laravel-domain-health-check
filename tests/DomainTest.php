<?php

use Illuminate\Support\Facades\Process;

it('can fetch whois of a domain', function () {

    $result = Process::run(['whois', 'google.com', 'Registry Expiry Date: ']);

    expect($result->output())
        ->toContain('Registry Expiry Date');

});
