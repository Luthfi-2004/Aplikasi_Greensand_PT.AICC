@extends('layouts.app')
@section('title', 'ACE Standards')

@section('content')
  <div class="page-content">
    <div class="container-fluid">

      <div class="page-title-box d-flex align-items-center justify-content-between">
        <h4 class="mb-0">ACE Standards</h4>
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="{{ route('ace.index') }}">ACE LINE</a></li>
            <li class="breadcrumb-item active">Standards</li>
          </ol>
        </div>
      </div>

      @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert" data-timeout="3000">
          {{ session('status') }}
          <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show auto-dismiss" role="alert" data-timeout="5000">
          <ul class="mb-0">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
          <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
      @endif

      @php
        $fmt = function ($v) {
          if ($v === null || $v === '')
            return null;
          $s = str_replace(',', '.', (string) $v);
          if (is_numeric($s))
            $s = rtrim(rtrim($s, '0'), '.');
          return $s;
        };
      @endphp

      <form method="POST" action="{{ route('ace.standards.update') }}">
        @csrf

        {{-- MM Sample --}}
        <div class="card mb-4">
          <div class="card-header"><strong>BF11</strong></div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle std-table">
                <thead class="thead-light">
                  <tr>
                    <th class="text-center" style="min-width:220px;">Parameter</th>
                    <th class="text-center" style="min-width:120px;">Min</th>
                    <th class="text-center" style="min-width:120px;">Max</th>
                    <th class="text-center" style="min-width:180px;">Range</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                    $fields = [
                      'p' => 'P',
                      'c' => 'C',
                      'gt' => 'G.T',
                      'cb_lab' => 'CB Lab',
                      'moisture' => 'Moisture',
                      'bakunetsu' => 'Bakunetsu',
                      'ac' => 'AC',
                      'tc' => 'TC',
                      'vsd' => 'VSD',
                      'ig' => 'IG',
                      'cb_weight' => 'CB Weight',
                      'tp50_height' => 'TP 50 Height',
                      'tp50_weight' => 'TP 50 Weight',
                      'ssi' => 'SSI',
                    ];
                  @endphp
                  @foreach($fields as $key => $label)
                    @php
                      $minRaw = old($key . '_min', $std->{$key . '_min'});
                      $maxRaw = old($key . '_max', $std->{$key . '_max'});
                      $min = $fmt($minRaw);
                      $max = $fmt($maxRaw);
                    @endphp
                    <tr>
                      <td class="std-param">{{ $label }}</td>
                      <td>
                        <input type="text" inputmode="decimal" lang="en" pattern="^-?\d+([.,]\d+)?$"
                          class="form-control form-control-sm std-num text-center" name="{{ $key }}_min" value="{{ $min }}">
                      </td>
                      <td>
                        <input type="text" inputmode="decimal" lang="en" pattern="^-?\d+([.,]\d+)?$"
                          class="form-control form-control-sm std-num text-center" name="{{ $key }}_max" value="{{ $max }}">
                      </td>
                      <td class="text-center">
                        @if($min !== null || $max !== null)
                          <strong>{{ $min ?? '-' }} ~ {{ $max ?? '-' }}</strong>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>

        {{-- BC13 --}}
        <div class="card mb-4">
          <div class="card-header"><strong>BC13</strong></div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle std-table">
                <thead class="thead-light">
                  <tr>
                    <th class="text-center" style="min-width:220px;">Parameter</th>
                    <th class="text-center" style="min-width:120px;">Min</th>
                    <th class="text-center" style="min-width:120px;">Max</th>
                    <th class="text-center" style="min-width:180px;">Range</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                    $bc = ['bc13_cb' => 'BC13 CB', 'bc13_c' => 'BC13 C', 'bc13_m' => 'BC13 M'];
                  @endphp
                  @foreach($bc as $key => $label)
                    @php
                      $minRaw = old($key . '_min', $std->{$key . '_min'});
                      $maxRaw = old($key . '_max', $std->{$key . '_max'});
                      $min = $fmt($minRaw);
                      $max = $fmt($maxRaw);
                    @endphp
                    <tr>
                      <td class="std-param">{{ $label }}</td>
                      <td>
                        <input type="text" inputmode="decimal" lang="en" pattern="^-?\d+([.,]\d+)?$"
                          class="form-control form-control-sm std-num text-center" name="{{ $key }}_min" value="{{ $min }}">
                      </td>
                      <td>
                        <input type="text" inputmode="decimal" lang="en" pattern="^-?\d+([.,]\d+)?$"
                          class="form-control form-control-sm std-num text-center" name="{{ $key }}_max" value="{{ $max }}">
                      </td>
                      <td class="text-center">
                        @if($min !== null || $max !== null)
                          <strong>{{ $min ?? '-' }} ~ {{ $max ?? '-' }}</strong>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-success mb-2">
            <i class="ri-checkbox-circle-line mr-1"></i> Submit
          </button>
        </div>
      </form>

      <div class="mb-4"></div>
    </div>
  </div>
@endsection

@push('styles')
  <base href="{{ url('/') }}/">
  <style>
    .std-table th,
    .std-table td {
      vertical-align: middle !important;
    }

    .std-param {
      text-align: center;
    }
  </style>
@endpush

@push('scripts')
  <script src="{{ asset('assets/js/standard.js') }}" defer></script>
@endpush