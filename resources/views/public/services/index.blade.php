@extends('layouts.nexus')

@section('title', 'Explore Services | ' . config('app.name'))

@section('content')
<div class="container py-5">
    <div class="row align-items-end mb-4">
        <div class="col-lg-8">
            <h1 class="text-white font-weight-bold mb-2">Explore Services</h1>
            <p class="text-white-50 mb-0">Public service pages with previews. Actions unlock after login.</p>
        </div>
        <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
            <a href="{{ route('register') }}" class="btn btn-primary mr-2" data-cta="explore_primary">Create account</a>
            <a href="{{ route('login') }}" class="btn btn-outline-glass" data-cta="explore_login">Sign in</a>
        </div>
    </div>

    @foreach($categories as $cat)
        @php
            $key = $cat['key'];
            $items = $byCategory->get($key, collect());
        @endphp
        @if($items->count())
            <div class="mb-5">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h2 class="h5 text-white font-weight-bold mb-1">{{ $cat['label'] }}</h2>
                        <div class="text-white-50 small">{{ $cat['description'] }}</div>
                    </div>
                </div>
                <div class="row">
                    @foreach($items as $svc)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <a href="{{ url('/explore/' . $svc['slug']) }}" class="text-decoration-none d-block h-100" data-cta="service_card">
                                <div class="p-4 h-100 rounded-lg" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03);">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.10);">
                                            <i class="{{ $svc['icon'] ?? 'fa-solid fa-layer-group' }} text-white"></i>
                                        </div>
                                        <div>
                                            <div class="text-white font-weight-bold">{{ $svc['title'] }}</div>
                                            <div class="text-white-50 small">{{ $svc['tagline'] }}</div>
                                        </div>
                                    </div>
                                    <div class="text-white-50 small">{{ $svc['summary'] }}</div>
                                    <div class="mt-3 d-flex flex-wrap" style="gap: 8px;">
                                        @foreach(($svc['highlights'] ?? []) as $h)
                                            <span class="px-2 py-1 rounded-pill small" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.10); color: rgba(255,255,255,0.75);">{{ $h }}</span>
                                        @endforeach
                                    </div>
                                    <div class="mt-4 text-white font-weight-bold">View details <i class="fa-solid fa-arrow-right ml-1"></i></div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>
@endsection
