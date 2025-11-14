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