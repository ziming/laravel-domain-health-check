<?php

use Illuminate\Support\Facades\Process;
use Spatie\Rdap\Facades\Rdap;

it('can fetch whois of a domain', function () {

    $result = Process::run(['whois', 'google.com', 'Registry Expiry Date: ']);

    expect($result->output())
        ->toContain('Registry Expiry Date');

});

it('can determine if a domain support RDAP', function () {

    $domainSupportRdap = Rdap::domainIsSupported('google.com');

    expect($domainSupportRdap)->toBeTrue();

});
