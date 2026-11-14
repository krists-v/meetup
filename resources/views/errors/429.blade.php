@extends('errors.layout')

@php
    $seconds = (int) (isset($exception) ? ($exception->getHeaders()['Retry-After'] ?? 60) : 60);

    if ($seconds >= 3600) {
        $wait = 'Try again in about an hour.';
    } elseif ($seconds > 90) {
        $minutes = (int) ceil($seconds / 60);
        $wait = 'Try again in '.$minutes.' minutes.';
    } else {
        $wait = 'Wait a minute, then try again.';
    }
@endphp

@section('title', 'Too many tries')
@section('message', $wait)
