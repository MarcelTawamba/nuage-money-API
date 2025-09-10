<!-- need to remove -->
<li class="nav-item">
    <a href="{{ route('home') }}" class="nav-link {{ Request::is('admin') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Dashboard</p>
    </a>
</li>

@if(Auth::user()->is_admin)
    <li class="nav-item">
        <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('admin/users*') ? 'active' : '' }}">
            <i class="nav-icon fas  fa-users"></i>
            <p>Users</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('companies.index') }}" class="nav-link {{ Request::is('admin/companies*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-building"></i>
            <p>Companies </p>
        </a>
    </li>


    <li class="nav-item">
        <a href="{{ route('apps.index') }}" class="nav-link {{ Request::is('admin/apps*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-th-large"></i>
            <p>Apps</p>
        </a>
    </li>
@endif


<li class="nav-item">
    <a href="{{ route('wallets.index') }}" class="nav-link {{ Request::is('admin/wallets*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-wallet"></i>
        <p>Wallets</p>
    </a>
</li>
<li class="nav-item">

    <a href="{{ route('achats.index') }}" class="nav-link {{ Request::is('admin/achats*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-money-bill-wave"></i>
        <p>Achats</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('transactions.index') }}" class="nav-link {{ Request::is('admin/transactions*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-exchange-alt"></i>
        <p>Transactions</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('exchange-requests.index') }}" class="nav-link {{ Request::is('admin/exchange-requests*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-sync-alt"></i>
        <p>Convert Requests</p>
    </a>
</li>
@if(!Auth::user()->is_admin)
    @if(\Illuminate\Support\Facades\Auth::user()->account_type =="company")
        <li class="nav-item">
            <a href="{{route('doc')}}" target="_blank" class="nav-link">
                <i class="nav-icon fas fa-cog"></i>
                <p>Documentation</p>
            </a>
        </li>
    @endif
@endif

@if(Auth::user()->is_admin)

    <li class="nav-item">

        <a href="{{ route('system-legers.index') }}" class="nav-link {{ Request::is('admin/system-legers*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-university"></i>
            <p>System Legers</p>

        </a>
    </li>


    <li class="nav-item">
        <a href="{{ route('country-availlables.index') }}" class="nav-link {{ Request::is('admin/country-availlables*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-globe-africa"></i>
            <p>Country Availlables</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('currencies.index') }}" class="nav-link {{ Request::is('admin/currencies*') ? 'active' : '' }}">
            <i class="nav-icon fab fa-gg-circle"></i>
            <p>Currencies</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('fees.index') }}" class="nav-link {{ Request::is('admin/fees*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-layer-group"></i>
            <p>Operator</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('start-button-banks.index') }}" class="nav-link {{ Request::is('admin/start-button-banks*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-university"></i>
            <p>Start Button Banks</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('exchange-rate-margins.index') }}" class="nav-link {{ Request::is('admin/exchange-rate-margins*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-coins"></i>
            <p>Exchange Rate Margins</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('exchange-fee-margins.index') }}" class="nav-link {{ Request::is('admin/exchange-fee-margins*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-coins"></i>
            <p>Exchange Fee Margins</p>
        </a>
    </li>
{{--    <li class="nav-item">--}}
{{--        <a href="{{ route('fincra-banks.index') }}" class="nav-link {{ Request::is('admin/fincra-banks*') ? 'active' : '' }}">--}}
{{--            <i class="nav-icon fas fa-university"></i>--}}
{{--            <p>Fincra Banks</p>--}}

{{--        </a>--}}
{{--    </li>--}}
{{--    <li class="nav-item">--}}
{{--        <a href="{{ route('fincra-bank-accounts.index') }}" class="nav-link {{ Request::is('admin/fincra-bank-accounts*') ? 'active' : '' }}">--}}
{{--            <i class="nav-icon fas fa-university"></i>--}}
{{--            <p>Fincra Bank Accounts</p>--}}

{{--        </a>--}}
{{--    </li>--}}


@endif



