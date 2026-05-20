<?php

function normalizeFormValue(?string $value): string
{
    $value = trim((string) $value);
    return preg_replace('/\s+/u', ' ', $value) ?? $value;
}

function isValidPersonName(string $value): bool
{
    return preg_match("/^[\p{L}\p{M}]+(?:[ '\-][\p{L}\p{M}]+)*$/u", $value) === 1;
}

function isValidCityName(string $value): bool
{
    return preg_match("/^[\p{L}\p{M}]+(?:[ '\-][\p{L}\p{M}]+)*$/u", $value) === 1;
}

function isValidFrenchPostalCode(string $value): bool
{
    return preg_match('/^\d{5}$/', $value) === 1;
}

function isValidFrenchPhoneNumber(string $value): bool
{
    return preg_match('/^(?:\+33|0)[1-9](?:[\s.-]?\d{2}){4}$/', $value) === 1;
}
