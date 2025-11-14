@extends('layouts.app')
@section('title', 'JSH Standards')

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

@section('content')
  <div class="page-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">

          <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">JSH Standards</h4>
            <div class="page-title-right">
              <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="{{ route('greensand.index') }}">JSH LINE</a></li>
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

          <form method="POST" action="{{ route('greensand.standards.update') }}">
            @csrf

            @foreach($groups as $groupName => $items)
              <div class="card mb-4">
                <div class="card-header"><strong>{{ $groupName }}</strong></div>
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
                        @foreach($items as $item)
                          <tr>
                            <td class="std-param">{{ $item['label'] }}</td>
                            <td>
                              <input type="text" inputmode="decimal" lang="en" pattern="^-?\d+([.,]\d+)?$"
                                class="form-control form-control-sm std-num text-center" name="{{ $item['key'] }}_min"
                                value="{{ $item['min'] }}">
                            </td>
                            <td>
                              <input type="text" inputmode="decimal" lang="en" pattern="^-?\d+([.,]\d+)?$"
                                class="form-control form-control-sm std-num text-center" name="{{ $item['key'] }}_max"
                                value="{{ $item['max'] }}">
                            </td>
                            <td class="text-center">
                              @if(($item['min'] ?? null) !== null || ($item['max'] ?? null) !== null)
                                <strong>{{ $item['min'] ?? '-' }} ~ {{ $item['max'] ?? '-' }}</strong>
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
            @endforeach

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-success mb-2">
                <i class="ri-checkbox-circle-line mr-1"></i> Submit
              </button>
            </div>
          </form>

          <div class="mb-4"></div>

        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/standard.js') }}" defer></script>
@endpush