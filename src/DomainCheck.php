<?php

declare(strict_types=1);

namespace Ziming\LaravelDomainHealthCheck;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Process;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Illuminate\Support\Uri;

class DomainCheck extends Check
{
    private string $domain;

    private ?string $whoisOutput = null;

    private int $warningWhenLessThanDaysLeft = 28;

    private int $errorWhenLessThanDaysLeft = 7;

    public function __construct() {
        $this->domain = Uri::of(config('app.url'))
            ->host();

        parent::__construct();
    }

    public function run(): Result
    {
        $this->saveWhoisData();

        $domainExpiryDateTime = $this->getDomainExpiryDateTime();

        $daysLeft = (int) $domainExpiryDateTime
            ?->diffInDays(CarbonImmutable::now());

        $result = Result::make()
            ->meta([
                'domain_expiry_datetime' => $domainExpiryDateTime
                    ?->timezone($this->timezone)
                    ?->toDayDateTimeString(),
                'days_left' => $domainExpiryDateTime
                    ?->diffInDays(CarbonImmutable::now()),
            ]);

        if ($domainExpiryDateTime < CarbonImmutable::now()->addDays($this->warningWhenLessThanDaysLeft)) {
            return $result->warning("Domain is expiring soon. {$daysLeft} days left.");
        }

        if ($domainExpiryDateTime < CarbonImmutable::now()->addDays($this->errorWhenLessThanDaysLeft)) {
            return $result->warning("Domain is expiring soon! {$daysLeft} days left!");
        }

        return $result->ok();
    }

    public function warnWhenDaysLeftToDomainExpiry(int $daysLeft): self
    {
        $this->warningWhenLessThanDaysLeft = $daysLeft;

        return $this;
    }

    public function domain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function failWhenDaysLeftToDomainExpiry(int $daysLeft): self
    {
        $this->errorWhenLessThanDaysLeft = $daysLeft;

        return $this;
    }

    private function saveWhoisData(): void
    {
        if ($this->whoisOutput === null) {
            $result = Process::run(['whois', $this->domain]);
            $this->whoisOutput = $result->output();
        }
    }

    public function getDomainExpiryDateTime(): ?CarbonInterface
    {
        // Credits to: https://www.conroyp.com/articles/monitoring-domain-expiration-dates-using-laravels-process-facade

        // actually should I just use `whois domain.com | grep "Expiry Date"` instead?
        if (!preg_match('/Registry Expiry Date: (.*)/', $this->whoisOutput, $matches)) {
            return null;
            //throw new RuntimeException('Cannot find domain expiry datetime from whois data.');
        }

        return new CarbonImmutable($matches[1]);
    }
}
