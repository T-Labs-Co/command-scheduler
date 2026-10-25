<?php

namespace TLabsCo\CommandScheduler\Models\Traits;

use Carbon\CarbonImmutable;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\ValueObjects\CronExpressionData;

/**
 * @property-read string $human_cron
 * @property-read ?CarbonImmutable $next_run
 * @property-read ?CarbonImmutable $previous_run
 */
trait WithCronExpressionDescriptor
{
    public function getCronExpressionData(): CronExpressionData
    {
        return new CronExpressionData($this->expression);
    }

    public function getHumanCronAttribute(): string
    {
        return $this->getCronExpressionData()->getHuman();
    }

    public function getNextRunAttribute(): ?CarbonImmutable
    {
        return CarbonImmutable::parse($this->getCronExpressionData()->getNextRun());
    }

    public function getPreviousRunAttribute(): ?CarbonImmutable
    {
        return CarbonImmutable::parse($this->getCronExpressionData()->getPreviousRun());
    }
}
