<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * A from–to date range read from the query string; defaults to the current month.
 */
final readonly class ReportPeriod
{
    public function __construct(public Carbon $from, public Carbon $to) {}

    public static function fromRequest(Request $request): self
    {
        $from = rescue(fn () => Carbon::parse($request->string('from')->value()), report: false);
        $to = rescue(fn () => Carbon::parse($request->string('to')->value()), report: false);

        $from = $request->filled('from') && $from ? $from->startOfDay() : today()->startOfMonth();
        $to = $request->filled('to') && $to ? $to->endOfDay() : today()->endOfDay();

        return $from->lte($to) ? new self($from, $to) : new self($to->copy()->startOfDay(), $from->copy()->endOfDay());
    }

    /**
     * Limit a query to dates inside the period (date columns are stored with a time part).
     *
     * @template TQuery of \Illuminate\Database\Eloquent\Builder
     *
     * @param  TQuery  $query
     * @return TQuery
     */
    public function constrain($query, string $column)
    {
        return $query->whereDate($column, '>=', $this->from->toDateString())->whereDate($column, '<=', $this->to->toDateString());
    }

    /**
     * @return array{from: string, to: string}
     */
    public function query(): array
    {
        return ['from' => $this->from->toDateString(), 'to' => $this->to->toDateString()];
    }
}
