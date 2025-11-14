<thead class="table-dark">
<tr>
    <th class="text-center align-middle" rowspan="5" style="min-width:120px;">Action</th>
    <th class="text-center align-middle" rowspan="5" style="min-width:80px;">No</th>
    <th class="text-center align-middle" rowspan="5" style="min-width:100px;">Date</th>
    <th class="text-center align-middle" rowspan="5" style="min-width:120px;">PIC</th>
    <th class="text-center align-middle" rowspan="5" style="min-width:100px;">Shift</th>
    <th class="text-center align-middle" rowspan="5" style="min-width:140px;">Type Product</th>
    <th class="text-center align-middle" rowspan="5" style="min-width:120px;">Sample Start</th>
    <th class="text-center align-middle" rowspan="5" style="min-width:120px;">Sample Finish</th>
    <th colspan="16" class="text-center">BF11</th>
    <th colspan="6" class="text-center">Additive Additional</th>
    <th colspan="4" class="text-center">Pengecekan BC13</th>
  </tr>

  @php
    use Illuminate\Support\Facades\DB;

    $__std = DB::table('tb_greensand_std_ace')->first();

    $__fmt = function ($v) {
      if ($v === null || $v === '')
        return '';
      if (!is_numeric($v))
        return (string) $v;
      $n = (float) $v;
      if (floor($n) == $n)
        return (string) intval($n);
      return number_format($n, 2, ',', '');
    };

    $LABEL = [
      'p' => 'P',
      'c' => 'C',
      'gt' => 'G.T',
      'cb_lab' => 'Cb Lab',
      'moisture' => 'Moisture',
      'machine_no' => 'Nomor Mesin',
      'bakunetsu' => 'Bakunetsu',
      'ac' => 'AC',
      'tc' => 'TC',
      'vsd' => 'VSD',
      'ig' => 'IG',
      'cb_weight' => 'CB Weight',
      'tp50_height' => 'TP 50 Height',
      'tp50_weight' => 'TP 50 Weight',
      'ssi' => 'SSI',
      'most' => 'MOIST',
      'dw29_vas' => 'DW29 VAS',
      'dw29_debu' => 'DW29 Debu',
      'dw31_vas' => 'DW31 VAS',
      'dw31_id' => 'DW31 ID',
      'dw31_moldex' => 'DW31 Moldex',
      'dw31_sc' => 'DW31 SC',
      'no_mix' => 'NO Mix',
      'bc13_cb' => 'BC13 CB',
      'bc13_c' => 'BC13 C',
      'bc13_m' => 'BC13 M',
    ];

    $STD = [
      'p' => ['unit' => 'g / Cm²', 'freq' => 'min 4x/shift'],
      'c' => ['unit' => 'Mpa', 'freq' => 'min 4x/shift'],
      'gt' => ['unit' => 'g / Cm²', 'freq' => 'min 2x/shift'],
      'cb_lab' => ['unit' => '%', 'freq' => 'min 4x/shift'],
      'moisture' => ['unit' => '%', 'freq' => 'min 4x/shift'],
      'bakunetsu' => ['unit' => '%', 'freq' => 'min 1x/shift'],
      'ac' => ['unit' => '%', 'freq' => 'min 1x/shift'],
      'tc' => ['unit' => '%', 'freq' => 'min 1x/shift'],
      'vsd' => ['unit' => '%', 'freq' => 'min 1x/shift'],
      'ig' => ['unit' => '%', 'freq' => 'min 1x/shift'],
      'cb_weight' => ['unit' => 'g', 'freq' => 'min 4x/shift'],
      'tp50_height' => ['unit' => '-', 'freq' => 'min 1x/shift'],
      'tp50_weight' => ['unit' => 'g', 'freq' => 'min 4x/shift'],
      'ssi' => ['unit' => '%', 'freq' => 'min 1x/shift'],
      'bc13_cb' => ['unit' => 'Lab', 'freq' => 'min 6x/shift'],
      'bc13_c' => ['unit' => 'Mpa', 'freq' => 'min 6x/shift'],
      'bc13_m' => ['unit' => '%', 'freq' => 'min 6x/shift'],
    ];

    $COL_MM = ['p', 'c', 'gt', 'cb_lab', 'moisture', 'machine_no', 'bakunetsu', 'ac', 'tc', 'vsd', 'ig', 'cb_weight', 'tp50_height', 'tp50_weight', 'ssi', 'most'];
    $COL_ADD = ['dw29_vas', 'dw29_debu', 'dw31_vas', 'dw31_id', 'dw31_moldex', 'dw31_sc'];
    $COL_BC13 = ['no_mix', 'bc13_cb', 'bc13_c', 'bc13_m'];

    $ROWSPAN4 = ['machine_no', 'most', 'dw29_vas', 'dw29_debu', 'dw31_vas', 'dw31_id', 'dw31_moldex', 'dw31_sc', 'no_mix'];

    $range = function ($k) use ($__std, $__fmt) {
      if (!$__std)
        return '-';
      $min = $__std->{$k . '_min'} ?? null;
      $max = $__std->{$k . '_max'} ?? null;
      if (is_null($min) && is_null($max))
        return '-';
      if (!is_null($min) && !is_null($max))
        return $__fmt($min) . ' ~ ' . $__fmt($max);
      return !is_null($min) ? '≥ ' . $__fmt($min) : '≤ ' . $__fmt($max);
    };

    $unit = fn($k) => $STD[$k]['unit'] ?? '-';
    $freq = fn($k) => $STD[$k]['freq'] ?? '-';
  @endphp

  <tr>
    @foreach($COL_MM as $k)
      @if(in_array($k, $ROWSPAN4))
        <th class="text-center align-middle" rowspan="4" style="min-width:120px;">{{ $LABEL[$k] ?? strtoupper($k) }}</th>
      @else
        <th class="text-center" style="min-width:120px;">{{ $LABEL[$k] ?? strtoupper($k) }}</th>
      @endif
    @endforeach
    @foreach($COL_ADD as $k)
      @if(in_array($k, $ROWSPAN4))
        <th class="text-center align-middle" rowspan="4" style="min-width:120px;">{{ $LABEL[$k] ?? strtoupper($k) }}</th>
      @else
        <th class="text-center" style="min-width:120px;">{{ $LABEL[$k] ?? strtoupper($k) }}</th>
      @endif
    @endforeach
    @foreach($COL_BC13 as $k)
      @if(in_array($k, $ROWSPAN4))
        <th class="text-center align-middle" rowspan="4" style="min-width:120px;">{{ $LABEL[$k] ?? strtoupper($k) }}</th>
      @else
        <th class="text-center" style="min-width:120px;">{{ $LABEL[$k] ?? strtoupper($k) }}</th>
      @endif
    @endforeach
  </tr>

  <tr class="std-row">
    @foreach($COL_MM as $k)
      @if(!in_array($k, $ROWSPAN4))
        <th class="text-center">{{ $range($k) }}</th>
      @endif
    @endforeach
    @foreach($COL_ADD as $k)
      @if(!in_array($k, $ROWSPAN4))
        <th class="std-spacer"></th>
      @endif
    @endforeach
    @foreach($COL_BC13 as $k)
      @if(!in_array($k, $ROWSPAN4))
        <th class="text-center">{{ $range($k) }}</th>
      @endif
    @endforeach
  </tr>

  <tr class="std-row">
    @foreach($COL_MM as $k)
      @if(!in_array($k, $ROWSPAN4))
        <th class="text-center">{{ $unit($k) }}</th>
      @endif
    @endforeach
    @foreach($COL_ADD as $k)
      @if(!in_array($k, $ROWSPAN4))
        <th class="std-spacer"></th>
      @endif
    @endforeach
    @foreach($COL_BC13 as $k)
      @if(!in_array($k, $ROWSPAN4))
        <th class="text-center">{{ $unit($k) }}</th>
      @endif
    @endforeach
  </tr>

  <tr class="std-row">
    @foreach($COL_MM as $k)
      @if(!in_array($k, $ROWSPAN4))
        <th class="text-center">{{ $freq($k) }}</th>
      @endif
    @endforeach
    @foreach($COL_ADD as $k)
      @if(!in_array($k, $ROWSPAN4))
        <th class="std-spacer"></th>
      @endif
    @endforeach
    @foreach($COL_BC13 as $k)
      @if(!in_array($k, $ROWSPAN4))
        <th class="text-center">{{ $freq($k) }}</th>
      @endif
    @endforeach
  </tr>
</thead>