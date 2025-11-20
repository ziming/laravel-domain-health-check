<?php

use Illuminate\Support\Facades\Process;

it('can fetch whois of a domain', function () {

    $result = Process::run(['whois', 'google.com']);

    expect($result->output())
        ->toContain('Registry Expiry Date');

});


