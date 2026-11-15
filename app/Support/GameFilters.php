<?php

namespace App\Support;

use App\Models\Event;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GameFilters
{
    public function __construct(
        public ?string $sport,
        public string $when,
        public ?string $where,
        public ?string $setting,
        public ?string $level,
        public ?string $category,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $sport = $request->query('sport');
        $sport = is_string($sport) && array_key_exists($sport, Event::SPORTS) ? $sport : null;

        $when = $request->query('when');
        $when = is_string($when) && in_array($when, ['today', 'weekend', 'week'], true) ? $when : 'all';

        $where = $request->query('where');
        $where = is_string($where) ? trim($where) : '';
        $where = $where === '' ? null : mb_substr($where, 0, 80);

        $setting = $request->query('setting');
        $setting = is_string($setting) && in_array($setting, ['indoor', 'outdoor'], true) ? $setting : null;

        $level = $request->query('level');
        $level = is_string($level) && (array_key_exists($level, Event::LEVELS) || $level === 'all') ? $level : null;

        $category = $request->query('category');
        $category = is_string($category) && array_key_exists($category, Event::CATEGORIES) ? $category : null;

        return new self($sport, $when, $where, $setting, $level, $category);
    }

    public function active(): bool
    {
        return $this->sport !== null
            || $this->when !== 'all'
            || $this->where !== null
            || $this->setting !== null
            || $this->level !== null
            || $this->category !== null;
    }

    /**
     * @param  array{sport?: ?string, when?: string, where?: ?string, setting?: ?string, level?: ?string, category?: ?string}  $overrides
     * @return array<string, string>
     */
    public function parameters(array $overrides = []): array
    {
        $sport = array_key_exists('sport', $overrides) ? $overrides['sport'] : $this->sport;
        $when = array_key_exists('when', $overrides) ? $overrides['when'] : $this->when;
        $where = array_key_exists('where', $overrides) ? $overrides['where'] : $this->where;
        $setting = array_key_exists('setting', $overrides) ? $overrides['setting'] : $this->setting;
        $level = array_key_exists('level', $overrides) ? $overrides['level'] : $this->level;
        $category = array_key_exists('category', $overrides) ? $overrides['category'] : $this->category;

        return array_filter([
            'sport' => $sport,
            'when' => $when === 'all' ? null : $when,
            'where' => $where,
            'setting' => $setting,
            'level' => $level,
            'category' => $category,
        ], fn ($value) => filled($value));
    }

    public function apply(Builder $query): Builder
    {
        $query->public()->upcoming();

        if ($this->sport !== null) {
            $sport = $this->sport;
            $query->where(function (Builder $query) use ($sport) {
                $query->where('sport', $sport)
                    ->orWhere(function (Builder $query) use ($sport) {
                        $query->where(function (Builder $query) {
                            $query->whereNull('sport')->orWhere('sport', '');
                        })->where('sport_type', $sport);
                    });
            });
        }

        if ($this->category !== null) {
            $slugs = Event::slugsInCategory($this->category);
            $query->where(function (Builder $query) use ($slugs) {
                $query->whereIn('sport', $slugs)
                    ->orWhere(function (Builder $query) use ($slugs) {
                        $query->where(function (Builder $query) {
                            $query->whereNull('sport')->orWhere('sport', '');
                        })->whereIn('sport_type', $slugs);
                    });
            });
        }

        if ($this->setting !== null) {
            $query->where('setting', $this->setting);
        }

        if ($this->level === 'all') {
            $query->whereNull('level');
        } elseif ($this->level !== null) {
            $query->where('level', $this->level);
        }

        if ($this->where !== null) {
            $query->where('location', 'like', '%'.addcslashes($this->where, '%_\\').'%');
        }

        $now = now();

        if ($this->when === 'today') {
            $query->whereBetween('event_date', [$now->copy()->startOfDay(), $now->copy()->endOfDay()]);
        } elseif ($this->when === 'weekend') {
            [$start, $end] = self::weekendRange($now);
            $query->whereBetween('event_date', [$start, $end]);
        } elseif ($this->when === 'week') {
            $query->whereBetween('event_date', [
                $now->copy()->startOfWeek(CarbonInterface::MONDAY),
                $now->copy()->endOfWeek(CarbonInterface::SUNDAY),
            ]);
        }

        return $query;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function weekendRange(CarbonInterface $moment): array
    {
        $moment = $moment->copy();

        if ($moment->isSunday()) {
            return [$moment->copy()->subDay()->startOfDay(), $moment->copy()->endOfDay()];
        }

        if ($moment->isSaturday()) {
            return [$moment->copy()->startOfDay(), $moment->copy()->addDay()->endOfDay()];
        }

        $saturday = $moment->copy()->next(CarbonInterface::SATURDAY)->startOfDay();

        return [$saturday, $saturday->copy()->addDay()->endOfDay()];
    }
}
