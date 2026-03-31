<?php

namespace App\Services\UserTargeting;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserSegment
{
    public static function apply(Builder $query, array $segment): Builder
    {
        $op = strtoupper((string) ($segment['operator'] ?? 'AND'));
        $rules = $segment['rules'] ?? [];
        if (!is_array($rules)) {
            $rules = [];
        }

        if ($op === 'OR') {
            return $query->where(function (Builder $q) use ($rules) {
                foreach ($rules as $rule) {
                    self::applyRule($q, $rule, true);
                }
            });
        }

        foreach ($rules as $rule) {
            self::applyRule($query, $rule, false);
        }

        return $query;
    }

    private static function applyRule(Builder $query, mixed $rule, bool $asOr): void
    {
        if (!is_array($rule)) {
            return;
        }

        if (isset($rule['group']) && is_array($rule['group'])) {
            $group = $rule['group'];
            $method = $asOr ? 'orWhere' : 'where';
            $query->{$method}(function (Builder $q) use ($group) {
                self::apply($q, $group);
            });
            return;
        }

        $field = (string) ($rule['field'] ?? '');
        $op = strtolower((string) ($rule['op'] ?? ''));
        $value = $rule['value'] ?? null;

        $method = $asOr ? 'orWhere' : 'where';

        if ($field === 'reseller_id') {
            if ($op === 'eq') {
                $query->{$method}('reseller_id', '=', (string) $value);
            } elseif ($op === 'in' && is_array($value)) {
                $vals = array_values(array_filter(array_map('strval', $value), fn ($v) => $v !== ''));
                if (!empty($vals)) {
                    $query->{$asOr ? 'orWhereIn' : 'whereIn'}('reseller_id', $vals);
                }
            }
            return;
        }

        if ($field === 'user_status') {
            if ($op === 'eq') {
                $query->{$method}('user_status', '=', (string) $value);
            } elseif ($op === 'in' && is_array($value)) {
                $vals = array_values(array_filter(array_map('strval', $value), fn ($v) => $v !== ''));
                if (!empty($vals)) {
                    $query->{$asOr ? 'orWhereIn' : 'whereIn'}('user_status', $vals);
                }
            }
            return;
        }

        if ($field === 'signup_date') {
            if ($op === 'between' && is_array($value) && isset($value['from'], $value['to'])) {
                $from = (string) $value['from'];
                $to = (string) $value['to'];
                $query->{$method}(function (Builder $q) use ($from, $to) {
                    $q->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
                });
            } elseif (in_array($op, ['gte', 'lte'], true) && is_string($value)) {
                $cmp = $op === 'gte' ? '>=' : '<=';
                $query->{$method}(function (Builder $q) use ($cmp, $value) {
                    $q->whereDate('created_at', $cmp, (string) $value);
                });
            }
            return;
        }

        if ($field === 'wallet_balance') {
            $sub = DB::table('account_balances')
                ->selectRaw('1')
                ->whereColumn('account_balances.email', 'users.email');

            if (in_array($op, ['gt', 'gte', 'lt', 'lte'], true) && is_numeric($value)) {
                $cmp = match ($op) {
                    'gt' => '>',
                    'gte' => '>=',
                    'lt' => '<',
                    default => '<=',
                };
                $sub->where('account_balances.user_balance', $cmp, (float) $value);
            } elseif ($op === 'between' && is_array($value) && isset($value['min'], $value['max'])) {
                $min = (float) $value['min'];
                $max = (float) $value['max'];
                $sub->whereBetween('account_balances.user_balance', [$min, $max]);
            } else {
                return;
            }

            if ($asOr) {
                $query->orWhereExists($sub);
            } else {
                $query->whereExists($sub);
            }
            return;
        }
    }
}

