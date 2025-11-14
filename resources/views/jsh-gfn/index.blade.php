{{-- resources/views/jsh-gfn/index.blade.php --}}
@extends('layouts.app')
@section('title', 'JSH GFN GREEN SAND')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">GFN</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">JSH LINE</a></li>
                            <li class="breadcrumb-item active">GFN</li>
                        </ol>
                    </div>
                </div>

                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert"
                        data-timeout="3000">
                        {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show auto-dismiss mb-2" role="alert"
                        data-timeout="5000">
                        {{ $errors->first() }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @php $isOpen = true; @endphp
                <div class="card mb-3">
                    <div id="filterHeader"
                        class="card-header bg-light d-flex justify-content-between align-items-center cursor-pointer"
                        data-toggle="collapse" data-target="#filterCollapse"
                        aria-expanded="{{ $isOpen ? 'true' : 'false' }}" aria-controls="filterCollapse">
                        <h5 class="font-size-14 mb-0"><i class="ri-filter-2-line align-middle mr-1"></i> Filter Data
                        </h5>
                        <i id="filterIcon" class="{{ $isOpen ? 'ri-subtract-line' : 'ri-add-line' }}"></i>
                    </div>
                    <div id="filterCollapse" class="collapse {{ $isOpen ? 'show' : '' }}">
                        <div class="card-body">
                            <form id="filterForm" class="row align-items-end" method="GET"
                                action="{{ route('jshgfn.index') }}">
                                <div class="col-xl-6 col-lg-4">
                                    <div class="form-group mb-2">
                                        <label for="fDate" class="form-label mb-1">Date</label>
                                        <div class="input-group">
                                            <input id="fDate" type="text" name="date" class="form-control gs-input"
                                                value="{{ $filters['date'] ?? '' }}" autocomplete="off"
                                                placeholder="YYYY-MM-DD" data-provide="datepicker"
                                                data-date-format="yyyy-mm-dd" data-date-autoclose="true">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-4 mb-2">
                                    <label class="form-label mb-1">Shift</label>
                                    <select class="form-control select2" name="shift"
                                        data-placeholder="-- Select Shift --" autocomplete="off">
                                        <option value=""></option>
                                        <option value="D" @selected(($filters['shift'] ?? '') === 'D')>D</option>
                                        <option value="S" @selected(($filters['shift'] ?? '') === 'S')>S</option>
                                        <option value="N" @selected(($filters['shift'] ?? '') === 'N')>N</option>
                                    </select>
                                </div>
                                <div class="col-xl-6 col-lg-12 mt-2">
                                    <div class="d-flex flex-wrap">
                                        <button type="submit" class="btn btn-primary btn-sm mr-2 mb-2">
                                            <i class="ri-search-line mr-1"></i> Search
                                        </button>
                                        <a href="{{ route('jshgfn.index') }}"
                                            class="btn btn-outline-primary btn-sm mr-2 mb-2">
                                            <i class="ri-refresh-line mr-1"></i> Refresh Filter
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center flex-wrap">
                            <button id="btn-add-greensand" type="button"
                                class="btn btn-success btn-sm btn-add-gs mr-2 mb-2" data-toggle="modal"
                                data-target="#modal-greensand">
                                <i class="ri-add-line"></i> Add Data
                            </button>

                            @if(!empty($displayRecap))
                            <button type="button" class="btn btn-outline-warning btn-sm mb-2 mr-2 btn-edit-gs">
                                <i class="ri-edit-2-line"></i> Edit Data
                            </button>

                            <button type="button" class="btn btn-outline-danger btn-sm mb-2 btn-delete-gs"
                                data-toggle="modal" data-target="#confirmDeleteModal"
                                data-gfn-date="{{ $displayRecap['gfn_date'] }}"
                                data-shift="{{ $displayRecap['shift'] }}">
                                <i class="fas fa-trash"></i> Delete Data
                            </button>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table id="datatable1" class="table table-bordered table-striped nowrap w-100 mt-2">
                                <thead class="bg-dark text-white text-center">
                                    <tr>
                                        <th>No</th>
                                        <th>Mesh</th>
                                        <th>Gram</th>
                                        <th>%</th>
                                        <th>Index</th>
                                        <th>%Index</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">
                                    @forelse(($displayRows ?? collect()) as $idx => $row)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>{{ $row->mesh }}</td>
                                            <td><b>{{ number_format($row->gram ?? 0, 2, ',', '.') }}</b></td>
                                            <td>{{ number_format($row->percentage ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ $row->index ?? 0 }}</td>
                                            <td>{{ number_format($row->percentage_index ?? 0, 1, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada data dalam 24 jam
                                                terakhir.</td>
                                        </tr>
                                    @endforelse

                                    @if(!empty($displayRecap))
                                        <tr>
                                            <th colspan="2" class="bg-dark text-white">TOTAL</th>
                                            <th class="bg-secondary text-white">
                                                <b>{{ number_format($displayRecap['total_gram'] ?? 0, 2, ',', '.') }}</b>
                                            </th>
                                            <th colspan="2">{{ $displayRecap['judge_overall'] ?? '' }}</th>
                                            <th class="bg-secondary text-white">
                                                {{ number_format($displayRecap['total_percentage_index'] ?? 0, 1, ',', '.') }}
                                            </th>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <div class="col-lg-6 d-flex flex-column">
                                <div class="table-responsive flex-grow-1" style="height:300px; overflow:auto;">
                                    <table class="table table-bordered table-striped mb-0 w-100 h-100 text-center">
                                        <thead class="bg-dark text-white">
                                            <tr>
                                                <td>Nilai GFN (Σ %Index / 100)</td>
                                                <td>
                                                    <b>{{ isset($displayRecap) ? number_format($displayRecap['nilai_gfn'], 2, ',', '.') : '-' }}</b>
                                                </td>
                                                <th>JUDGE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>% MESH 140 (STD : 3.5 ~ 8.0 %)</td>
                                                <td><b>{{ isset($displayRecap) ? number_format($displayRecap['mesh_total140'], 2, ',', '.') : '-' }}</b>
                                                </td>
                                                <td>{{ $displayRecap['judge_mesh_140'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td>Σ MESH 50, 70 & 100 (Min 64 %)</td>
                                                <td><b>{{ isset($displayRecap) ? number_format($displayRecap['mesh_total70'], 2, ',', '.') : '-' }}</b>
                                                </td>
                                                <td>{{ $displayRecap['judge_mesh_70'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td>% MESH 280 + PAN (STD : 0.00 ~ 1.40 %)</td>
                                                <td><b>{{ isset($displayRecap) ? number_format($displayRecap['meshpan'], 2, ',', '.') : '-' }}</b>
                                                </td>
                                                <td>{{ $displayRecap['judge_meshpan'] ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column">
                                        <h4 class="card-title mb-3 text-center">Grafik GFN Green Sand</h4>
                                        <div id="gfn-line" class="flot-charts flot-charts-height"
                                            style="height:300px; flex:1 1 auto;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                @include('jsh-gfn._form', ['meshes' => $meshes, 'indices' => $indices])

            </div>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteTitle">Confirm Delete</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <p id="confirmDeleteText" class="mb-0">Are you sure you want to delete this data?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light mr-2" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="{{ route('jshgfn.deleteToday') }}" method="POST" class="m-0 p-0">
                    @csrf
                    <input type="hidden" name="gfn_date" id="delDate">
                    <input type="hidden" name="shift" id="delShift">
                    <button type="submit" class="btn btn-danger" id="confirmDeleteYes">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Form Modal (standalone kalau tidak include via partial) --}}
<div class="modal fade" id="modal-greensand" tabindex="-1" role="dialog" aria-labelledby="modalGreensandLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form id="form-greensand" action="{{ route('jshgfn.store') }}" method="POST" class="modal-content"
            autocomplete="off">
            @csrf
            <div class="modal-header py-2">
                <h5 class="modal-title" id="modalGreensandLabel">Form Add Data GFN Green Sand</h5>
                <button type="button" class="close" data-dismiss="modal"
                    aria-label="Close"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <div id="gfnDupAlert" class="alert alert-danger d-none mb-2" role="alert"></div>

                <div class="row mb-3">
                    <div class="col-xl-6 col-lg-6 mb-2">
                        <label class="form-label mb-1">Tanggal</label>
                        <input id="gfnDate" type="text" name="gfn_date" class="form-control"
                            value="{{ old('gfn_date') }}" placeholder="YYYY-MM-DD" autocomplete="off">
                        @error('gfn_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-xl-6 col-lg-6 mb-2">
                        <label class="form-label mb-1">Shift</label>
                        <select class="form-control select2" name="shift" data-placeholder="Pilih Shift">
                            <option value="" hidden>Pilih Shift</option>
                            <option value="D" @selected(old('shift') === 'D')>D</option>
                            <option value="S" @selected(old('shift') === 'S')>S</option>
                            <option value="N" @selected(old('shift') === 'N')>N</option>
                        </select>
                        @error('shift') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-center align-middle mb-2">
                        <thead class="thead-light">
                            <tr>
                                <th width="60">NO</th>
                                <th width="100">MESH</th>
                                <th width="120">GRAM</th>
                                <th width="100">%</th>
                                <th width="100">INDEX</th>
                                <th width="120">% INDEX</th>
                            </tr>
                        </thead>
                        <tbody id="gfnBody">
                            @foreach($meshes as $i => $mesh)
                                @php $oldGram = old('grams.' . $i, '');
                                $idx = $indices[$i] ?? 0; @endphp
                                <tr data-row="{{ $i }}" data-index="{{ $idx }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $mesh }}</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="1000"
                                            class="form-control form-control-sm text-right gfn-gram" name="grams[]"
                                            value="{{ $oldGram }}">
                                    </td>
                                    <td class="gfn-percent">0,00</td>
                                    <td>{{ $idx }}</td>
                                    <td class="gfn-percent-index">0,0</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="2" class="text-right">TOTAL</td>
                                <td id="gfn-total-gram" class="text-right">0,00</td>
                                <td id="gfn-total-percent">100,00</td>
                                <td class="text-muted">—</td>
                                <td id="gfn-total-percent-index">0,0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @error('grams') <small class="text-danger d-block">{{ $message }}</small> @enderror
            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary mr-2 d-flex align-items-center"
                    data-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Cancel
                </button>
                <button id="gsSubmitBtn" type="submit" class="btn btn-success d-flex align-items-center">
                    <i class="ri-checkbox-circle-line me-1"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    @php
        $__rows = [];
        if (!empty($displayRows)) {
            foreach ($displayRows as $r) {
                $__rows[] = [
                    'mesh' => $r->mesh,
                    'percentage' => round(($r->percentage ?? 0), 2),
                    'percentage_index' => round(($r->percentage_index ?? 0), 1),
                    'index' => ($r->index ?? 0),
                ];
            }
        }
        $__recap = $displayRecap ?? null;
    @endphp

<script id="gfn-rows" type="application/json">
    @json($__rows ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
</script>
<script id="gfn-recap" type="application/json">
    @json($__recap ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
</script>

<script>
    // parse safely from DOM (avoids editor false-positives and script injection)
    try {
        const rows = JSON.parse(document.getElementById('gfn-rows').textContent || '[]');
        const recap = JSON.parse(document.getElementById('gfn-recap').textContent || 'null');

        window.gfnChartData = { rows: rows, recap: recap };

        window.jshRoutes = {
            gfnExists: "{{ route('jshgfn.check-exists') }}",
            gfnStore: "{{ route('jshgfn.store') }}",
            gfnUpdate: "{{ route('jshgfn.update') }}",
        };
    } catch (e) {
        // jika ada error parsing, tampilkan di console supaya nggak blank
        console.error('GFN JSON parse error:', e);
        window.gfnChartData = { rows: [], recap: null };
        window.jshRoutes = {
            gfnExists: "{{ route('jshgfn.check-exists') }}",
            gfnStore: "{{ route('jshgfn.store') }}",
            gfnUpdate: "{{ route('jshgfn.update') }}",
        };
    }
</script>
    @if(session('open_modal'))
        <script>window.openModalGFN = true;</script>
    @endif

    <script src="{{ asset('assets/libs/flot-charts/jquery.flot.js') }}"></script>
    <script src="{{ asset('assets/libs/flot-charts/jquery.flot.resize.js') }}"></script>
    <script src="{{ asset('assets/libs/flot-charts/jquery.flot.time.js') }}"></script>
    <script src="{{ asset('assets/libs/flot-charts/jquery.flot.pie.js') }}"></script>
    <script src="{{ asset('assets/libs/jquery.flot.tooltip/js/jquery.flot.tooltip.min.js') }}"></script>

    <script src="{{ asset('assets/js/jsh-gfn.js') }}" defer></script>

    <script>
        (function () {
            var $ = window.jQuery;
            if (!$) return;
            $(function () {
                $('.alert.auto-dismiss').each(function () {
                    var $el = $(this);
                    var ms = parseInt($el.attr('data-timeout'), 10);
                    if (!Number.isFinite(ms) || ms < 0) ms = 3000;
                    setTimeout(function () {
                        var hasBs = typeof $.fn.alert === 'function';
                        if (hasBs) { try { $el.alert('close'); return; } catch (e) { } }
                        $el.fadeOut(200, function () { $(this).remove(); });
                    }, ms);
                });
            });
        })();
    </script>
@endpush
@endsection