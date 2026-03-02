@props(['user', 'size' => 'md'])

@php
    $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
    $sizes = [
        'xs' => ['width' => '24px', 'height' => '24px', 'font' => '11px'],
        'sm' => ['width' => '32px', 'height' => '32px', 'font' => '14px'],
        'md' => ['width' => '48px', 'height' => '48px', 'font' => '18px'],
        'lg' => ['width' => '80px', 'height' => '80px', 'font' => '32px'],
        'xl' => ['width' => '120px', 'height' => '120px', 'font' => '48px'],
    ];
    $config = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="user-avatar user-avatar-{{ $size }}" 
     data-initials="{{ $initial }}"
     style="width: {{ $config['width'] }}; height: {{ $config['height'] }}; font-size: {{ $config['font'] }};"
     title="{{ $user->name }}"
     {{ $attributes }}>
    {{ $initial }}
</div>
