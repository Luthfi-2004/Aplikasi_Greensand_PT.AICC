@extends('layouts.app')
@section('title', 'Green Sand Check')

@push('styles')
<style>
  .welcome-card {
    border-radius: 12px;
  }
  .stat-card {
    border-radius: 10px;
    min-height: 96px;
  }
  .stat-value {
    font-size: 1.35rem;
    font-weight: 600;
  }
  .stat-label {
    font-size: .85rem;
    color: #6b7280;
  }
  .quick-action .btn {
    min-width: 140px;
  }
  /* table */
  .recent-table .card-body { padding: .75rem; }
  .table-search { max-width: 380px; }
  .status-badge.ok { background:#10b981; color:#fff; padding:4px 8px; border-radius:6px; font-weight:600; font-size:.85rem; }
  .status-badge.ng { background:#ef4444; color:#fff; padding:4px 8px; border-radius:6px; font-weight:600; font-size:.85rem; }
  /* responsive spacing */
  @media (max-width: 576px) {
    .card-body.d-flex { flex-direction: column; gap: .75rem; }
  }
</style>
@endpush

@section('content')
<div class="page-content">
  <div class="container-fluid">

    {{-- Row Title --}}
    <div class="row mt-3">
      <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
          <h4 class="mb-0">Dashboard</h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    {{-- Welcome + Actions --}}
    <div class="row">
      <div class="col-lg-8 col-md-12 mb-3">
        <div class="card shadow-sm border-0 welcome-card">
          <div class="card-body d-flex align-items-center">
            <div class="flex-grow-1">
              <h4 class="card-title mb-1">
                Hai, {{ optional(Auth::user())->name ?? 'User' }} — <span id="greetingTime"></span>
              </h4>
              <p class="card-text text-muted mb-2" style="font-size: 15px;">
                Selamat datang di sistem Green Sand. Di dashboard ini kamu bisa melihat ringkasan cepat performa, memasukkan data baru, dan meninjau rekap harian.
              </p>

              <div class="d-flex flex-wrap quick-action">
                <a href="{{ route('greensand.index') }}" class="btn btn-primary btn-sm mr-2 mb-2 d-flex align-items-center">
                  <i class="ri-database-2-line mr-1"></i> Proses Greensand
                </a>
                <a href="{{ route('ace.index') }}" class="btn btn-success btn-sm mr-2 mb-2 d-flex align-items-center">
                  <i class="ri-database-2-line mr-1"></i> Proses Aceline
                </a>
                <a href="{{ route('jshgfn.index') }}" class="btn btn-outline-primary btn-sm mr-2 mb-2 d-flex align-items-center">
                  <i class="ri-bar-chart-line mr-1"></i> GFN (JSH)
                </a>
                <a href="{{ route('acelinegfn.index') }}" class="btn btn-outline-success btn-sm mr-2 mb-2 d-flex align-items-center">
                  <i class="ri-line-chart-line mr-1"></i> GFN (ACE)
                </a>
              </div>
            </div>

            <div class="ml-4 d-none d-md-block" style="width:160px;">
              {{-- small illustration or icon --}}
              <div class="text-center">
                <div style="width:120px;height:120px;border-radius:12px;background:linear-gradient(135deg,#6ee7b7,#60a5fa);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">
                  GSF
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Stats --}}
      <div class="col-lg-4 col-md-12 mb-3">
        <div class="row">
          <div class="col-12 mb-2">
            <div class="card stat-card shadow-sm border-0">
              <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                  <div class="stat-value" id="stat-today-count">—</div>
                  <div class="stat-label">Data Hari Ini</div>
                </div>
                <div class="ml-3 text-center">
                  <i class="ri-checkbox-circle-line" style="font-size:28px;color:#10b981;"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-6 mb-2">
            <div class="card stat-card shadow-sm border-0">
              <div class="card-body text-center">
                <div class="stat-value" id="stat-week">—</div>
                <div class="stat-label">Minggu Ini</div>
              </div>
            </div>
          </div>

          <div class="col-6 mb-2">
            <div class="card stat-card shadow-sm border-0">
              <div class="card-body text-center">
                <div class="stat-value" id="stat-month">—</div>
                <div class="stat-label">Bulan Ini</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Charts / Rekap --}}
    <div class="row">
      <div class="col-lg-8 mb-3">
        <div class="card shadow-sm border-0">
          <div class="card-header">
            <strong>Grafik Ringkasan</strong>
          </div>
          <div class="card-body">
            <div id="chart-summary" style="height:320px;">
              {{-- placeholder: inject chart via JS (Flot / Chart.js) --}}
              <div class="h-100 d-flex align-items-center justify-content-center text-muted">
                Grafik belum di-load — hubungkan data di assets/js.
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Recent activity / notes --}}
      <div class="col-lg-4 mb-3">
        <div class="card shadow-sm border-0">
          <div class="card-header">
            <strong>Aktivitas Terakhir</strong>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mb-0">
              <li class="py-2 border-bottom">
                <div class="small text-muted">12:10 — Input GFN (JSH)</div>
                <div>Data jsh_gfn oleh <strong>Operator A</strong></div>
              </li>
              <li class="py-2 border-bottom">
                <div class="small text-muted">09:30 — Update standar</div>
                <div>Standar Greensand diperbarui</div>
              </li>
              <li class="py-2">
                <div class="small text-muted">Kemarin — Export</div>
                <div>Export rekap mingguan</div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    {{-- Recent Processes Table (Nazox-like) --}}
    <div class="row">
      <div class="col-12">
        <div class="card recent-table shadow-sm border-0">
          <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Recent Processes</strong>
            <div class="d-flex align-items-center">
              <input id="tableSearch" class="form-control form-control-sm table-search mr-2" placeholder="Search...">
              <a href="{{ route('jshgfn.index') }}" class="btn btn-outline-secondary btn-sm">View all</a>
            </div>
          </div>

          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0" id="recent-table">
                <thead class="thead-light">
                  <tr>
                    <th style="min-width:120px;">Tanggal</th>
                    <th style="min-width:70px;">Waktu</th>
                    <th>Proses</th>
                    <th>Operator</th>
                    <th>Shift</th>
                    <th>Ringkasan</th>
                    <th style="min-width:110px;">Status</th>
                    <th style="min-width:120px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  {{-- use $recentProcesses passed from controller, else fallback example rows --}}
                  @if(!empty($recentProcesses) && count($recentProcesses))
                    @foreach($recentProcesses as $proc)
                      <tr>
                        <td>{{ \Carbon\Carbon::parse($proc->gfn_date ?? $proc['date'] ?? now())->format('Y-m-d') }}</td>
                        <td>{{ $proc->time ?? ($proc['time'] ?? date('H:i')) }}</td>
                        <td>{{ $proc->type ?? ($proc['type'] ?? 'GFN') }}</td>
                        <td>{{ $proc->operator ?? ($proc['operator'] ?? '—') }}</td>
                        <td>{{ $proc->shift ?? ($proc['shift'] ?? '—') }}</td>
                        <td>{{ $proc->summary ?? ($proc['summary'] ?? '—') }}</td>
                        <td>
                          @php $st = strtolower($proc->status ?? ($proc['status'] ?? 'ok')); @endphp
                          <span class="status-badge {{ $st === 'ok' ? 'ok' : 'ng' }}">{{ strtoupper($st) }}</span>
                        </td>
                        <td>
                          <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                          <a href="#" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                      </tr>
                    @endforeach
                  @else
                    {{-- fallback demo rows --}}
                    @php
                      $demo = [
                        ['date' => now()->subHours(2)->toDateString(), 'time' => now()->subHours(2)->format('H:i'), 'type' => 'GFN (JSH)', 'operator' => 'Operator A', 'shift' => 'D', 'summary' => 'Total 1000g, GFN 5.2', 'status' => 'OK'],
                        ['date' => now()->subDays(1)->toDateString(), 'time' => '09:30', 'type' => 'Standards Update', 'operator' => 'Supervisor', 'shift' => 'S', 'summary' => 'Update standar MM', 'status' => 'OK'],
                        ['date' => now()->subDays(2)->toDateString(), 'time' => '16:12', 'type' => 'GFN (ACE)', 'operator' => 'Operator B', 'shift' => 'N', 'summary' => 'Total 950g, GFN 4.8', 'status' => 'NG'],
                      ];
                    @endphp

                    @foreach($demo as $d)
                      <tr>
                        <td>{{ $d['date'] }}</td>
                        <td>{{ $d['time'] }}</td>
                        <td>{{ $d['type'] }}</td>
                        <td>{{ $d['operator'] }}</td>
                        <td>{{ $d['shift'] }}</td>
                        <td>{{ $d['summary'] }}</td>
                        <td>
                          <span class="status-badge {{ strtolower($d['status']) === 'ok' ? 'ok' : 'ng' }}">{{ $d['status'] }}</span>
                        </td>
                        <td>
                          <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                          <a href="#" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
            </div> {{-- /.table-responsive --}}
          </div>
        </div>
      </div>
    </div>

    {{-- Footer spacer --}}
    <div class="row">
      <div class="col-12 mb-5"></div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    // Greeting based on hour
    const hour = new Date().getHours();
    const greetingEl = document.getElementById('greetingTime');
    let greet = 'Selamat Datang';
    if (hour >= 4 && hour < 11) greet = 'Selamat Pagi';
    else if (hour >= 11 && hour < 15) greet = 'Selamat Siang';
    else if (hour >= 15 && hour < 18) greet = 'Selamat Sore';
    else greet = 'Selamat Malam';
    if (greetingEl) greetingEl.textContent = greet + '!';

    // Placeholder: set stat values (replace with data from server via blade or ajax)
    document.getElementById('stat-today-count').textContent = "{{ $statToday ?? '0' }}";
    document.getElementById('stat-week').textContent = "{{ $statWeek ?? '0' }}";
    document.getElementById('stat-month').textContent = "{{ $statMonth ?? '0' }}";

    // Simple client-side search for recent table
    const searchInput = document.getElementById('tableSearch');
    const table = document.getElementById('recent-table');
    if (searchInput && table) {
      searchInput.addEventListener('input', function (e) {
        const q = (e.target.value || '').trim().toLowerCase();
        const rows = table.tBodies[0].rows;
        for (let i = 0; i < rows.length; i++) {
          const rowText = rows[i].textContent.toLowerCase();
          rows[i].style.display = rowText.indexOf(q) === -1 ? 'none' : '';
        }
      });
    }
  })();
</script>
@endpush
