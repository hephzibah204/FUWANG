<?php

namespace App\Services\UserTargeting;

use Illuminate\Validation\ValidationException;

class UserSegmentValidator
{
    private const ALLOWED_FIELDS = [
        'reseller_id',
        'user_status',
        'signup_date',
        'wallet_balance',
    ];

    public static function validateAndNormalize(?string $json): ?array
    {
        $json = trim((string) $json);
        if ($json === '') {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw ValidationException::withMessages(['segment' => __('errors.invalid_segment_json')]);
        }

        return self::normalizeGroup($data);
    }

    private static function normalizeGroup(array $group): array
    {
        $operator = strtoupper((string) ($group['operator'] ?? 'AND'));
        if (!in_array($operator, ['AND', 'OR'], true)) {
            throw ValidationException::withMessages(['segment' => 'Segment operator must be AND or OR.']);
        }

        $rules = $group['rules'] ?? null;
        if (!is_array($rules)) {
            throw ValidationException::withMessages(['segment' => 'Segment rules must be an array.']);
        }

        $outRules = [];
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }

            if (isset($rule['group']) && is_array($rule['group'])) {
                $outRules[] = ['group' => self::normalizeGroup($rule['group'])];
                continue;
            }

            $field = (string) ($rule['field'] ?? '');
            $op = strtolower((string) ($rule['op'] ?? ''));
            $value = $rule['value'] ?? null;

            if (!in_array($field, self::ALLOWED_FIELDS, true)) {
                throw ValidationException::withMessages(['segment' => 'Unsupported segment field: ' . $field]);
            }

            if ($field === 'reseller_id') {
                if (!in_array($op, ['eq', 'in'], true)) {
                    throw ValidationException::withMessages(['segment' => 'Invalid reseller_id operator.']);
                }
                if ($op === 'eq' && (!is_string($value) || trim($value) === '')) {
                    throw ValidationException::withMessages(['segment' => 'reseller_id eq requires a string value.']);
                }
                if ($op === 'in' && !is_array($value)) {
                    throw ValidationException::withMessages(['segment' => 'reseller_id in requires an array of values.']);
                }
            }

            if ($field === 'user_status') {
                if (!in_array($op, ['eq', 'in'], true)) {
                    throw ValidationException::withMessages(['segment' => 'Invalid user_status operator.']);
                }
                $allowed = ['active', 'inactive', 'suspended'];
                if ($op === 'eq') {
                    if (!is_string($value) || !in_array($value, $allowed, true)) {
                        throw ValidationException::withMessages(['segment' => 'user_status must be active, inactive, or suspended.']);
                    }
                } else {
                    if (!is_array($value) || empty($value)) {
                        throw ValidationException::withMessages(['segment' => 'user_status in requires an array of values.']);
                    }
                    foreach ($value as $v) {
                        if (!is_string($v) || !in_array($v, $allowed, true)) {
                            throw ValidationException::withMessages(['segment' => 'user_status list contains invalid value.']);
                        }
                    }
                }
            }

            if ($field === 'signup_date') {
                if (!in_array($op, ['between', 'gte', 'lte'], true)) {
                    throw ValidationException::withMessages(['segment' => 'Invalid signup_date operator.']);
                }
                if ($op === 'between') {
                    if (!is_array($value) || empty($value['from']) || empty($value['to'])) {
                        throw ValidationException::withMessages(['segment' => 'signup_date between requires {from,to}.']);
                    }
                } else {
                    if (!is_string($value) || trim($value) === '') {
                        throw ValidationException::withMessages(['segment' => 'signup_date requires a date string.']);
                    }
                }
            }

            if ($field === 'wallet_balance') {
                if (!in_array($op, ['gt', 'gte', 'lt', 'lte', 'between'], true)) {
                    throw ValidationException::withMessages(['segment' => 'Invalid wallet_balance operator.']);
                }
                if ($op === 'between') {
                    if (!is_array($value) || !isset($value['min'], $value['max'])) {
                        throw ValidationException::withMessages(['segment' => 'wallet_balance between requires {min,max}.']);
                    }
                    if (!is_numeric($value['min']) || !is_numeric($value['max'])) {
                        throw ValidationException::withMessages(['segment' => 'wallet_balance min/max must be numeric.']);
                    }
                } else {
                    if (!is_numeric($value)) {
                        throw ValidationException::withMessages(['segment' => 'wallet_balance comparison requires a numeric value.']);
                    }
                }
            }

            $outRules[] = [
                'field' => $field,
                'op' => $op,
                'value' => $value,
            ];
        }

        if (empty($outRules)) {
            throw ValidationException::withMessages(['segment' => 'Segment rules cannot be empty.']);
        }

        return [
            'operator' => $operator,
            'rules' => $outRules,
        ];
    }
}

