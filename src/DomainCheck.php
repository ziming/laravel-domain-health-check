<?php

declare(strict_types=1);

namespace Ziming\LaravelDomainHealthCheck;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Uri;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Rdap\CouldNotFindRdapServer;
use Spatie\Rdap\Facades\Rdap;

class DomainCheck extends Check
{
    private string $domain;

    private ?string $whoisOutput = null;

    private int $warningWhenLessThanDaysLeft = 28;

    private int $errorWhenLessThanDaysLeft = 7;

    public function __construct()
    {
        $this->domain = Uri::of(config('app.url'))
            ->host();

        parent::__construct();
    }

    /**
     * @throws CouldNotFindRdapServer
     */
    public function run(): Result
    {
        $domainSupportRdap = Rdap::domainIsSupported($this->domain);

        if ($domainSupportRdap === true) {

            $rdapDomainResponse = Rdap::domain($this->domain);

            $domainExpiryDateTime = $rdapDomainResponse
                ?->expirationDate();

            $domainRegistrationDateTime = $rdapDomainResponse
                ?->registrationDate();

            $domainUpdatedAtDatetime = $rdapDomainResponse
                ?->lastChangedDate();

        } else {
            $this->whoisOutput = Cache::remember('domain-whois-data:'.$this->domain, Carbon::now()->addWeek(), function (): string {
                return $this->fetchWhoisData();
            });

            $domainExpiryDateTime = $this->getWhoisDomainExpiryDateTime();

            $domainRegistrationDateTime = $this->getWhoisRegistrationDateTime();

            $domainUpdatedAtDatetime = $this->getWhoisUpdatedAtDateTime();
        }

        $daysLeft = (int) CarbonImmutable::now()
            ->diffInDays($domainExpiryDateTime);

        $domainExpiryDateTimeInDayDateTimeString = $domainExpiryDateTime
            ?->timezone($this->timezone)
            ?->toDayDateTimeString();

        $domainRegistrationDateTimeInDayDateTimeString = $domainRegistrationDateTime
            ?->timezone($this->timezone)
            ?->toDayDateTimeString();

        $domainUpdatedAtDateTimeInDayDateTimeString = $domainUpdatedAtDatetime
            ?->timezone($this->timezone)
            ?->toDayDateTimeString();

        $result = Result::make()
            ->meta([
                'domain' => $this->domain,
                'expiration_date' => $domainExpiryDateTimeInDayDateTimeString,
                'days_left' => $daysLeft,
                'registration_date' => $domainRegistrationDateTimeInDayDateTimeString,
                'last_changed_at' => $domainUpdatedAtDateTimeInDayDateTimeString,
            ]);

        if ($domainExpiryDateTime <= CarbonImmutable::now()->addDays($this->warningWhenLessThanDaysLeft)) {
            return $result->warning("{$this->domain} is expiring soon. {$daysLeft} days left.");
        }

        if ($domainExpiryDateTime <= CarbonImmutable::now()->addDays($this->errorWhenLessThanDaysLeft)) {
            return $result->warning("{$this->domain}} is expiring soon! {$daysLeft} days left!");
        }

        return $result->ok("{$this->domain} expires on {$domainExpiryDateTimeInDayDateTimeString}");
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

    private function fetchWhoisData(): string
    {
        $result = Process::run(['whois', $this->domain]);

        return $result->output();
    }

    public function getWhoisDomainExpiryDateTime(): ?CarbonInterface
    {
        // actually should I just use `whois domain.com | grep "Expiry Date"` instead?

        // Credits to: https://www.conroyp.com/articles/monitoring-domain-expiration-dates-using-laravels-process-facade

        if (! preg_match('/Registry Expiry Date: (.*)/', $this->whoisOutput, $matches)) {
            return null;
            // throw new RuntimeException('Cannot find domain expiry datetime from whois data.');
        }

        return new CarbonImmutable($matches[1]);
    }

    public function getWhoisRegistrationDateTime(): ?CarbonInterface
    {
        if (! preg_match('/Creation Date: (.*)/', $this->whoisOutput, $matches)) {
            return null;
        }

        return new CarbonImmutable($matches[1]);
    }

    public function getWhoisUpdatedAtDateTime(): ?CarbonInterface
    {
        if (! preg_match('/Updated Date: (.*)/', $this->whoisOutput, $matches)) {
            return null;
        }

        return new CarbonImmutable($matches[1]);
    }
}
