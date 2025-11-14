@php
  $isDash = request()->routeIs('dashboard');

  $isJshIndex = request()->routeIs('greensand.index');
  $isJshGfn = request()->routeIs('jshgfn.*');
  $isJshStd = request()->routeIs('greensand.standards');
  $isJshOpen = $isJshIndex || $isJshGfn || $isJshStd;

  $isAceIndex = request()->routeIs('ace.index');
  $isAceGfn = request()->routeIs('acelinegfn.*');
  $isAceStd = request()->routeIs('ace.standards');
  $isAceOpen = $isAceIndex || $isAceGfn || $isAceStd;
@endphp
<div class="vertical-menu">
  <div data-simplebar class="h-100">
    <div id="sidebar-menu">
      <ul class="metismenu list-unstyled" id="side-menu">
        <li class="menu-title">Menu</li>

        <li class="{{ $isDash ? 'mm-active' : '' }}">
          <a href="{{ route('dashboard') }}" class="waves-effect {{ $isDash ? 'active' : '' }}">
            <i class="ri-dashboard-line"></i>
            <span>Dashboard</span>
          </a>
        </li>

        <li class="menu-title">Green Sand</li>

        <li class="{{ ($isJshIndex || $isJshGfn || $isJshStd) ? 'mm-active' : '' }}">
          <a href="javascript:void(0);" class="has-arrow waves-effect">
            <i class="ri-flask-line"></i>
            <span>JSH LINE</span>
          </a>
          <ul class="sub-menu" aria-expanded="{{ $isJshOpen ? 'true' : 'false' }}">
            <li class="{{ $isJshIndex ? 'mm-active' : '' }}">
              <a href="{{ route('greensand.index') }}" class="{{ $isJshIndex ? 'active' : '' }}">
                Daily Check
              </a>
            </li>
            <li class="{{ $isJshGfn ? 'mm-active' : '' }}">
              <a href="{{ route('jshgfn.index') }}" class="{{ $isJshGfn ? 'active' : '' }}">
                GFN
              </a>
            </li>
            <li class="{{ $isJshStd ? 'mm-active' : '' }}">
              <a href="{{ route('greensand.standards') }}" class="{{ $isJshStd ? 'active' : '' }}">
                Standards
              </a>
            </li>
          </ul>
        </li>

        <li class="{{ ($isAceIndex || $isAceGfn || $isAceStd) ? 'mm-active' : '' }}">
          <a href="javascript:void(0);" class="has-arrow waves-effect">
            <i class="ri-flask-line"></i>
            <span>ACE LINE</span>
          </a>
          <ul class="sub-menu" aria-expanded="{{ $isAceOpen ? 'true' : 'false' }}">
            <li class="{{ $isAceIndex ? 'mm-active' : '' }}">
              <a href="{{ route('ace.index') }}" class="{{ $isAceIndex ? 'active' : '' }}">
                Daily Check
              </a>
            </li>
            <li class="{{ $isAceGfn ? 'mm-active' : '' }}">
              <a href="{{ route('acelinegfn.index') }}" class="{{ $isAceGfn ? 'active' : '' }}">
                GFN
              </a>
            </li>
            <li class="{{ $isAceStd ? 'mm-active' : '' }}">
              <a href="{{ route('ace.standards') }}" class="{{ $isAceStd ? 'active' : '' }}">
                Standards
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</div>