<?php

declare(strict_types=1);

namespace TLabsCo\CommandScheduler\Core\Scheduling\Domain\ValueObjects;

use Cron\CronExpression;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Lorisleiva\CronTranslator\CronTranslator;

/**
 * @implements Arrayable<string, mixed>
 */
class CronExpressionData implements \Stringable, Arrayable, JsonSerializable
{
    private readonly bool $valid;

    private string $human;

    private ?string $nextRun;

    private ?string $previousRun;

    public function __construct(private readonly string $expression)
    {
        $this->valid = CronExpression::isValidExpression($this->expression);

        if ($this->valid) {
            $cron = new CronExpression($this->expression);
            $this->human = CronTranslator::translate($this->expression);
            $this->nextRun = $cron
                ->getNextRunDate()
                ->format('Y-m-d H:i:s');
            $this->previousRun = $cron
                ->getPreviousRunDate()
                ->format('Y-m-d H:i:s');
        } else {
            $this->human = 'Invalid expression';
            $this->nextRun = null;
            $this->previousRun = null;
        }
    }

    public static function make(string $expression): self
    {
        return new self($expression);
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getHuman(): string
    {
        return $this->human;
    }

    public function getNextRun(): ?string
    {
        return $this->nextRun;
    }

    public function getPreviousRun(): ?string
    {
        return $this->previousRun;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'expression' => $this->expression,
            'human' => $this->human,
            'valid' => $this->valid,
            'next_run' => $this->nextRun,
            'previous_run' => $this->previousRun,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return $this->expression;
    }
}
