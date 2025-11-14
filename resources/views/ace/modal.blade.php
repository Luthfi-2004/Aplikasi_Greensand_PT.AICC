<div class="modal fade" id="modal-ace" tabindex="-1" role="dialog" aria-labelledby="aceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <form id="aceForm" class="modal-content" autocomplete="off">
      @csrf
      <input type="hidden" name="id" id="ace_id">
      <input type="hidden" name="form_mode" id="ace_mode" value="create">
      <input type="hidden" name="date" id="mDate">
      <input type="hidden" name="shift" id="mShift">

      <div class="modal-header">
        <h5 class="modal-title" id="aceModalLabel">
          <span id="aceModalMode">Add</span> ACE LINE
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <div id="aceFormAlert" class="alert alert-danger d-none mb-3"></div>

        <div class="card mb-3">
          <div class="card-body">
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label mb-1">Type Product</label>
                <select id="productSelectModal" name="product_type_id" class="form-control" style="width:100%"
                  data-selected-id="{{ $data->product_type_id ?? '' }}"
                  data-selected-text="{{ $data->product_type_name ?? '' }}">
                </select>
                <input type="hidden" name="product_type_name" id="productTypeName">
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label mb-1">Sample Start</label>
                <input type="time" name="sample_start" id="mStart" class="form-control"
                  placeholder="Input Sample Start">
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label mb-1">Sample Finish</label>
                <input type="time" name="sample_finish" id="mFinish" class="form-control"
                  placeholder="Input Sample Finish">
              </div>
            </div>
          </div>
        </div>

        <ul class="nav nav-tabs mb-3" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-mm" role="tab"><i
                class="ri-flask-line mr-1"></i> Pengecekan BF13</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-add" role="tab"><i
                class="ri-pie-chart-2-line mr-1"></i> Additive Additional</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-bc13" role="tab"><i
                class="ri-alert-line mr-1"></i> Pengecekan BC13</a></li>
        </ul>

        <div class="tab-content">
          {{-- MM SAMPLE: semua angka --}}
          <div class="tab-pane fade show active" id="tab-mm" role="tabpanel">
            <div class="row">
              @php
                $mmFields = [
                  ['p', 'P'],
                  ['c', 'C'],
                  ['gt', 'G.T'],
                  ['cb_lab', 'Cb Lab'],
                  ['moisture', 'Moisture'],
                  ['machine_no', 'Nomor Mesin'],
                  ['bakunetsu', 'Bakunetsu'],
                  ['ac', 'AC'],
                  ['tc', 'TC'],
                  ['vsd', 'VSD'],   
                  ['ig', 'IG'],
                  ['cb_weight', 'CB Weight'],
                  ['tp50_height', 'TP 50 Height'],
                  ['tp50_weight', 'TP 50 Weight'],
                  ['ssi', 'SSI'],
                  ['most', 'MOIST'],
                ];
              @endphp
              @foreach($mmFields as [$name, $label])
                @php $labelText = str_replace('_', ' ', $label); @endphp
                <div class="col-md-3 mb-3">
                  <label class="mb-1">{{ $labelText }}</label>
                  <input type="text" inputmode="decimal" pattern="^-?[0-9]*[\,\.]?[0-9]+$" name="{{ $name }}"
                    id="m_{{ $name }}" class="form-control js-num" placeholder="Input Sample {{ $labelText }}">
                </div>
              @endforeach
            </div>
          </div>

          {{-- ADDITIVE ADDITIONAL: semua angka --}}
          <div class="tab-pane fade" id="tab-add" role="tabpanel">
            <div class="row">
              @php
                $addFields = [
                  ['dw29_vas', 'DW29_VAS'],
                  ['dw29_debu', 'DW29_Debu'],
                  ['dw31_vas', 'DW31_VAS'],
                  ['dw31_id', 'DW31_ID'],
                  ['dw31_moldex', 'DW31_Moldex'],
                  ['dw31_sc', 'DW31_SC'],
                ];
              @endphp
              @foreach($addFields as [$name, $label])
                @php $labelText = str_replace('_', ' ', $label); @endphp
                <div class="col-md-3 mb-3">
                  <label class="mb-1">{{ $labelText }}</label>
                  <input type="text" inputmode="decimal" pattern="^-?[0-9]*[\,\.]?[0-9]+$" name="{{ $name }}"
                    id="m_{{ $name }}" class="form-control js-num" placeholder="Input Sample {{ $labelText }}">
                </div>
              @endforeach
            </div>
          </div>

          {{-- BC13: semua angka --}}
          <div class="tab-pane fade" id="tab-bc13" role="tabpanel">
            <div class="row">
              <div class="col-md-3 mb-3">
                <label class="mb-1">NO Mix</label>
                <input type="text" inputmode="numeric" pattern="^[0-9]+$" name="no_mix" id="mNoMix"
                  class="form-control js-num-int" placeholder="Input Sample NO Mix">
              </div>
              @php
                $bc13Fields = [
                  ['bc13_cb', 'BC13_CB'],
                  ['bc13_c', 'BC13_C'],
                  ['bc13_m', 'BC13_M'],
                ];
              @endphp
              @foreach($bc13Fields as [$name, $label])
                @php $labelText = str_replace('_', ' ', $label); @endphp
                <div class="col-md-3 mb-3">
                  <label class="mb-1">{{ $labelText }}</label>
                  <input type="text" inputmode="decimal" pattern="^-?[0-9]*[\,\.]?[0-9]+$" name="{{ $name }}"
                    id="m_{{ $name }}" class="form-control js-num" placeholder="Input Sample {{ $labelText }}">
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer d-flex justify-content-end">
        <button type="button" class="btn btn-outline-secondary mr-2 d-flex align-items-center" data-dismiss="modal">
          <i class="ri-close-line me-1"></i> Cancel
        </button>
        <button type="submit" class="btn btn-success mr-2 d-flex align-items-center" id="aceSubmitBtn">
          <i class="ri-checkbox-circle-line me-1"></i> Submit
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
  <script>
    (function () {
      // ===== Select2 product (tetap) =====
      function initProductSelectModal() {
        var $el = $('#productSelectModal');
        if ($el.data('select2')) return;
        $el.select2({
          width: '100%',
          placeholder: 'Pilih product',
          dropdownParent: $('#modal-ace'),
          ajax: {
            url: '{{ route("lookup.products") }}',
            dataType: 'json',
            delay: 200,
            data: function (params) { return { q: params.term || '', page: params.page || 1 }; },
            processResults: function (data, params) {
              params.page = params.page || 1;
              return {
                results: Array.isArray(data.results) ? data.results : [],
                pagination: { more: !!(data.pagination && data.pagination.more) }
              };
            },
            cache: true
          },
          minimumInputLength: 0,
          templateResult: function (item) { return item.text || ''; },
          templateSelection: function (item) { return item.text || item.id || ''; }
        });
        $el.on('select2:select', function (e) {
          const data = e.params?.data || {};
          $('#productTypeName').val(data.text || data.name || '');
        });
        $el.on('select2:clear', function () { $('#productTypeName').val(''); });
      }

      // ===== Filter huruf + normalisasi koma/titik =====
      function allowNumericInput(el, allowDecimal) {
        el.addEventListener('input', function () {
          let v = el.value;
          // Hanya angka, koma, titik, minus
          v = v.replace(/[^0-9,.\-]/g, '');
          // Minus hanya di depan
          v = v.replace(/(?!^)-/g, '');
          // Untuk decimal: biarkan banyak koma/titik saat ketik, normalisasi saat submit
          if (!allowDecimal) {
            v = v.replace(/[.,]/g, '');
          }
          el.value = v;
        });
      }

      function normalizeNumbers($form) {
        // .js-num => desimal, .js-num-int => integer
        $form.find('.js-num').each(function () {
          const v = (this.value || '').trim();
          if (v === '') return;
          this.value = v.replace(',', '.'); // koma -> titik
        });
        $form.find('.js-num-int').each(function () {
          const v = (this.value || '').trim();
          if (v === '') return;
          this.value = v.replace(/[^0-9\-]/g, ''); // hanya integer
        });
      }

      $('#modal-ace').on('shown.bs.modal', function () {
        initProductSelectModal();

        // Prefill select saat edit
        var $el = $('#productSelectModal');
        var mode = (($('#ace_mode').val() || 'create') + '').toLowerCase();
        if (mode === 'update') {
          var id = $el.data('selected-id');
          var text = $el.data('selected-text');
          if (id && text) {
            $el.empty();
            var opt = new Option(text, id, true, true);
            $el.append(opt).trigger('change.select2');
            $('#productTypeName').val(text);
          }
        } else {
          $el.val(null).trigger('change');
          $('#productTypeName').val('');
          // Buka dropdown dan trigger pencarian kosong untuk load awal
          $el.select2('open');
          setTimeout(function () {
            var $field = $('.select2-container--open .select2-search__field');
            $field.val(' ').trigger('input');
            setTimeout(function () { $field.val(''); }, 10);
          }, 50);
        }

        // Pasang filter untuk semua input angka
        document.querySelectorAll('#modal-ace .js-num').forEach(function (inp) {
          allowNumericInput(inp, true);
        });
        document.querySelectorAll('#modal-ace .js-num-int').forEach(function (inp) {
          allowNumericInput(inp, false);
        });
      });

      $('#modal-ace').on('hide.bs.modal', function () {
        var $el = $('#productSelectModal');
        if ($el.data('select2')) { $el.select2('close'); }
      });

      // Normalisasi sebelum submit
      $('#aceForm').on('submit', function (e) {
        normalizeNumbers($(this));
        // Optional: validasi cepat
        var bad = [];
        $(this).find('.js-num').each(function () {
          const v = (this.value || '').trim();
          if (v && !/^-?\d+(\.\d+)?$/.test(v.replace(',', '.'))) bad.push(this.name);
        });
        $(this).find('.js-num-int').each(function () {
          const v = (this.value || '').trim();
          if (v && !/^-?\d+$/.test(v)) bad.push(this.name);
        });
        if (bad.length) {
          e.preventDefault();
          $('#aceFormAlert').removeClass('d-none').text('Data tidak valid pada field: ' + bad.join(', '));
        } else {
          $('#aceFormAlert').addClass('d-none').text('');
        }
      });

      // In case modal sudah terbuka
      if ($('#modal-ace').is(':visible')) {
        document.querySelectorAll('#modal-ace .js-num').forEach(function (inp) { allowNumericInput(inp, true); });
        document.querySelectorAll('#modal-ace .js-num-int').forEach(function (inp) { allowNumericInput(inp, false); });
      }
    })();
  </script>
@endpush