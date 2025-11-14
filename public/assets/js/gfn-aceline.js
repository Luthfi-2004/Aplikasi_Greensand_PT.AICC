// public/assets/js/gfn-aceline.js
(function () {
    var $ = window.jQuery;
    if (!$) return;

    function getSubmitBtn() {
        var $btn = $("#gsSubmitBtn");
        if (!$btn.length) $btn = $("#form-greensand button[type='submit']");
        return $btn;
    }

    function lockSubmit() {
        var $btn = getSubmitBtn();
        if (!$btn.length || $btn.prop("disabled")) return;
        $btn.prop("disabled", true);
        $btn.data("orig", $btn.html());
        $btn.html('<span class="spinner-border spinner-border-sm mr-1"></span> Saving...');
    }

    function unlockSubmit() {
        var $btn = getSubmitBtn();
        if (!$btn.length) return;
        var orig = $btn.data("orig");
        if (orig) $btn.html(orig);
        $btn.prop("disabled", false);
    }

    var GFN_MODE = "create"; // create | edit

    function pad(n) { return String(n).padStart(2, "0"); }
    function today() {
        var d = new Date();
        return d.getFullYear()+"-"+pad(d.getMonth()+1)+"-"+pad(d.getDate());
    }
    function autoShift() {
        const h = new Date().getHours();
        return h >= 6 && h < 18 ? "D" : "N"; // hanya default; tidak mengunci
    }
    function fmt(n, d) {
        if (d === void 0) d = 2;
        if (!isFinite(n)) n = 0;
        return Number(n).toLocaleString("id-ID",{minimumFractionDigits:d,maximumFractionDigits:d});
    }

    function initSelect2() {
        if (!$.fn.select2) return;
        $(".select2").select2({
            autoclose: true,
            placeholder: function(){ return $(this).data("placeholder") || "-- Select --"; },
            width: "100%",
        });
    }

    // === CHANGE: datepicker tanpa batasan tanggal (boleh ke hari sebelumnya) ===
    function initDatepickers() {
        if (!$.fn.datepicker) return;

        var $f = $("#fDate");
        if ($f.length) {
            $f.datepicker({
                format: "yyyy-mm-dd",
                autoclose: true,
                orientation: "bottom",
            });
        }

        var $g = $("#gfnDate");
        if ($g.length) {
            $g.datepicker({
                format: "yyyy-mm-dd",
                autoclose: true,
                orientation: "bottom",
                container: "#modal-greensand",
                // no startDate / endDate -> bebas pilih tanggal lampau
            }).datepicker("setDate", new Date()); // default hari ini (boleh diubah)
        }
    }

    function seedFilter() {
        var $fd = $("#filterForm #fDate");
        var $fs = $('#filterForm select[name="shift"]');
        if ($fd.length && (!$fd.val() || !$fd.val().trim())) {
            $fd.val(today());
            if ($.fn.datepicker) $fd.datepicker("update", $fd.val());
        }
        if ($fs.length && (!$fs.val() || !$fs.val().trim())) {
            $fs.val(autoShift()).trigger("change"); // default saja; user bebas ubah
        }
    }

    function syncModalFromFilter() {
        var $m = $("#modal-greensand");
        if (!$m.length) return;
        var fd = $("#filterForm #fDate").val();
        var sh = $('#filterForm select[name="shift"]').val();
        var $gd = $m.find("#gfnDate");
        var $gs = $m.find('select[name="shift"]');
        if (fd) { $gd.val(fd); if ($.fn.datepicker) $gd.datepicker("update", fd); }
        if (sh) { $gs.val(sh).trigger("change"); }
    }

    (function initFilterIcon(){
        var $c = $("#filterCollapse");
        var $i = $("#filterIcon");
        var $h = $("#filterHeader");
        function setIcon(open){ if ($i.length){ $i.removeClass("ri-add-line ri-subtract-line").addClass(open?"ri-subtract-line":"ri-add-line"); } }
        if (!$c.length) return;
        setIcon($c.hasClass("show"));
        $c.on("shown.bs.collapse", function(){ setIcon(true); });
        $c.on("hidden.bs.collapse", function(){ setIcon(false); });
        $h.on("click", function(){});
    })();

    function initAutoDismissFlash() {
        $(".alert.auto-dismiss").each(function(){
            var $el = $(this);
            var ms = parseInt($el.attr("data-timeout"),10);
            if (!Number.isFinite(ms) || ms < 0) ms = 3000;
            setTimeout(function(){
                if (typeof $.fn.alert === "function") {
                    try { $el.alert("close"); return; } catch(e){}
                }
                $el.fadeOut(200,function(){ $(this).remove(); });
            }, ms);
        });
    }

    function recalc() {
        var tb = document.getElementById("gfnBody");
        if (!tb) return;
        var rows = tb.querySelectorAll("tr[data-row]");
        var tg = 0;
        rows.forEach(function(tr){
            var raw = (tr.querySelector(".gfn-gram") && tr.querySelector(".gfn-gram").value) || "0";
            var g = parseFloat(String(raw).replace(",", "."));
            if (!isNaN(g)) tg += g;
        });
        var tp = 0, tpi = 0;
        rows.forEach(function(tr){
            var idx = parseFloat(tr.dataset.index || "0");
            var raw = (tr.querySelector(".gfn-gram") && tr.querySelector(".gfn-gram").value) || "0";
            var g = parseFloat(String(raw).replace(",", "."));
            var p = tg > 0 ? (g / tg) * 100 : 0;
            var pi = p * idx;
            var cP = tr.querySelector(".gfn-percent");
            var cPI = tr.querySelector(".gfn-percent-index");
            if (cP) cP.textContent = fmt(p, 2);
            if (cPI) cPI.textContent = fmt(pi, 1);
            tp += p; tpi += pi;
        });
        var elTG = document.getElementById("gfn-total-gram");
        var elTP = document.getElementById("gfn-total-percent");
        var elTPI = document.getElementById("gfn-total-percent-index");
        if (elTG) elTG.textContent = fmt(tg, 2);
        if (elTP) elTP.textContent = fmt(tp, 2);
        if (elTPI) elTPI.textContent = fmt(tpi, 1);
    }

    function renderGFNCharts() {
        if (!$.plot) return;
        var $line = $("#gfn-line");
        if (!$line.length) return;
        var dataObj = window.gfnChartData || {};
        var rows = Array.isArray(dataObj.rows) ? dataObj.rows : [];
        if (!rows.length) { $line.empty(); return; }

        var ticks = [], lineData = [];
        for (var i=0;i<rows.length;i++){
            var x = i+1;
            var pct = parseFloat(rows[i] && rows[i].percentage) || 0;
            ticks.push([x, String(x)]);
            lineData.push([x, pct]);
        }

        var plot;
        try {
            plot = $.plot($line, [{ data: lineData, label: "%", lines:{show:true,lineWidth:2}, points:{show:true,radius:3} }], {
                xaxis:{ticks:ticks}, yaxis:{min:0},
                grid:{hoverable:true, clickable:true, borderWidth:1, labelMargin:10},
                tooltip:true,
                tooltipOpts:{ content:"No %x: %y.2%%", defaultTheme:false }
            });
        } catch(e){ return; }

        try {
            var ctx = plot.getCanvas().getContext("2d");
            var s = plot.getData()[0] || { data: [] };
            ctx.save();
            ctx.font = "12px Arial, sans-serif";
            ctx.textAlign = "center";
            ctx.textBaseline = "bottom";
            for (var j=0;j<s.data.length;j++){
                var pt = s.data[j];
                var o = plot.pointOffset({ x: pt[0], y: pt[1] });
                ctx.fillText(pt[1].toFixed(2) + "%", o.left, o.top - 6);
            }
            ctx.restore();
        } catch(_){}
    }

    var _resizeTimer = null;
    function onWinResize(){
        if (_resizeTimer) clearTimeout(_resizeTimer);
        _resizeTimer = setTimeout(renderGFNCharts, 150);
    }

    function ensureWarnHost(){
        var m = document.getElementById("modal-greensand");
        if (!m) return null;
        var box = m.querySelector(".modal-body");
        if (!box) return null;
        var host = m.querySelector("#gfnDupAlert");
        if (!host){
            host = document.createElement("div");
            host.id = "gfnDupAlert";
            host.className = "alert alert-danger d-none";
            host.setAttribute("role","alert");
            host.style.marginBottom = "12px";
            box.insertBefore(host, box.firstChild);
        }
        return host;
    }
    function showWarn(msg){
        var host = ensureWarnHost();
        if (host){ host.textContent = msg || "Data sudah ada."; host.classList.remove("d-none"); }
        else { alert(msg || "Data sudah ada."); }
    }
    function hideWarn(){ var host = document.getElementById("gfnDupAlert"); if (host) host.classList.add("d-none"); }
    function destroyWarn(){ var host = document.getElementById("gfnDupAlert"); if (host && host.parentNode) host.parentNode.removeChild(host); }

    async function checkDuplicate(date, shift){
        if (!(window.aceRoutes && aceRoutes.gfnExists)) return false;
        var url = aceRoutes.gfnExists + "?date=" + encodeURIComponent(date||"") + (shift ? "&shift=" + encodeURIComponent(shift) : "");
        try {
            var res = await fetch(url, { headers:{Accept:"application/json"}, credentials:"same-origin" });
            if (!res.ok) return false;
            var j = await res.json();
            return !!(j.exists || j.found || j.duplicate || j.data_exists);
        } catch(_){ return false; }
    }

    // === CHANGE: Edit modal TIDAK mengunci tanggal/shift ===
    function openEditModalFromDisplay() {
        destroyWarn();

        var dataObj = window.gfnChartData || {};
        var recap = dataObj.recap || null;
        var rows = Array.isArray(dataObj.rows) ? dataObj.rows : [];
        if (!recap || !rows.length) return;

        GFN_MODE = "edit";

        var $m = $("#modal-greensand");
        var $form = $m.find("form#form-greensand");

        $m.find(".modal-title").text("Edit Data GFN ACE LINE");
        $m.find('button[type="submit"]').html('<i class="ri-save-3-line me-1"></i> Update');

        if (window.aceRoutes && aceRoutes.gfnUpdate) $form.attr("action", aceRoutes.gfnUpdate);
        if ($form.find('input[name="_method"]').length === 0)
            $('<input type="hidden" name="_method" value="PUT">').appendTo($form);
        else $form.find('input[name="_method"]').val("PUT");

        var $gd = $m.find("#gfnDate");
        var d = recap.gfn_date;
        $gd.val(d);
        if ($.fn.datepicker) {
            try { $gd.datepicker("update", d); } catch(e){}
        }

        var $gs = $m.find('select[name="shift"]');
        // tidak disable opsi; user bebas ganti
        $gs.val(recap.shift).trigger("change");

        var totalGram = recap && recap.total_gram ? parseFloat(recap.total_gram) : 0;
        $m.find("#gfnBody tr[data-row]").each(function (i, tr) {
            var pct = rows[i] ? parseFloat(rows[i].percentage || 0) : 0;
            var g = totalGram > 0 ? (pct / 100.0) * totalGram : 0;
            var inp = tr.querySelector(".gfn-gram");
            if (inp) inp.value = String(Math.round(g * 100) / 100);
        });

        recalc();
        $m.modal("show");
        unlockSubmit();
    }

    document.addEventListener("input", function (e) {
        if (e.target && e.target.classList.contains("gfn-gram")) recalc();
    });

    ["blur","change"].forEach(function (ev) {
        document.addEventListener(ev, function (e) {
            if (e.target && e.target.classList.contains("gfn-gram")) {
                var s = (e.target.value || "").trim().replace(",", ".");
                var n = parseFloat(s);
                if (!Number.isFinite(n)) { e.target.value = ""; recalc(); return; }
                if (n > 1000) n = 1000;
                if (n < 0) n = 0;
                e.target.value = String(n);
                recalc();
            }
        });
    });

    if (window.openModalGFN) {
        $(function(){
            destroyWarn();
            $("#modal-greensand").modal("show");
            recalc();
            unlockSubmit();
        });
    }

    $(document).on("click", ".btn-delete-gs", function(){
        destroyWarn();
        var d = $(this).data("gfn-date");
        var s = $(this).data("shift");
        $("#delDateText").text(d || "—");
        $("#delShiftText").text(s || "—");
        $("#delDate").val(d || "");
        $("#delShift").val(s || "");
    });

    $(document).on("click", ".btn-edit-gs", function(){
        destroyWarn();
        openEditModalFromDisplay();
    });

    // === CHANGE: saat modal dibuka (create), tidak mengunci shift ke jam sekarang; semua opsi aktif ===
    $(document).on("shown.bs.modal", "#modal-greensand", function () {
        hideWarn();
        unlockSubmit();

        var $m = $("#modal-greensand");
        var $gd = $m.find("#gfnDate");
        var $gs = $m.find('select[name="shift"]');

        if ($.fn.datepicker && $gd.data("datepicker") == null) {
            $gd.datepicker({
                format: "yyyy-mm-dd",
                autoclose: true,
                orientation: "bottom",
                container: "#modal-greensand",
                // tidak ada startDate/endDate
            }).datepicker("setDate", new Date());
        }

        if (GFN_MODE === "create") {
            syncModalFromFilter();

            if ($gd.length && (!$gd.val() || !$gd.val().trim())) {
                $gd.val(today());
                if ($.fn.datepicker) $gd.datepicker("update", $gd.val());
            }

            // semua opsi shift aktif; set default saja (boleh diubah)
            if ($gs.length) {
                $gs.find("option").prop("disabled", false);
                var cs = autoShift();
                if (!$gs.val()) $gs.val(cs).trigger("change");
            }
        }
    });

    $(document).on("hidden.bs.modal", "#modal-greensand", function () {
        destroyWarn();
        unlockSubmit();

        var $m = $("#modal-greensand");
        var $form = $m.find("form#form-greensand");

        $m.find(".gfn-gram").val("");
        var $gd = $m.find("#gfnDate");
        $gd.val("");
        if ($.fn.datepicker) { try { $gd.datepicker("update", ""); } catch(_){ } }
        $m.find('select[name="shift"]').val("").trigger("change.select2");

        $m.find(".gfn-percent").text("0,00");
        $m.find(".gfn-percent-index").text("0,0");
        $("#gfn-total-gram").text("0,00");
        $("#gfn-total-percent").text("100,00");
        $("#gfn-total-percent-index").text("0,0");

        GFN_MODE = "create";
        $m.find(".modal-title").text("Form Add Data GFN ACE LINE");
        $m.find('button[type="submit"]').html('<i class="ri-checkbox-circle-line me-1"></i> Submit');

        if ($form.find('input[name="_method"]').length) $form.find('input[name="_method"]').remove();
        if (window.aceRoutes && aceRoutes.gfnStore) $form.attr("action", aceRoutes.gfnStore);
    });

    $(document).on("submit", "#form-greensand", async function (e) {
        hideWarn();
        lockSubmit();

        var $form = $(this);
        var d = ($form.find("#gfnDate").val() || "").trim();
        var s = ($form.find('select[name="shift"]').val() || "").trim();
        if (!d) return;

        var isEdit = $form.find('input[name="_method"][value="PUT"]').length > 0;
        if (isEdit) return; // edit langsung submit; backend handle

        e.preventDefault();
        var dup = await checkDuplicate(d, s);
        if (dup) {
            showWarn("Data untuk tanggal " + d + (s ? " (shift " + s + ")" : "") + " sudah ada. Hapus data tersebut dulu sebelum input ulang.");
            unlockSubmit();
            return;
        }
        this.submit();
    });

    $(function(){
        initSelect2();
        initDatepickers();
        seedFilter();
        initAutoDismissFlash();

        var btn = document.getElementById("btn-add-greensand");
        if (btn) {
            btn.addEventListener("click", function () {
                destroyWarn();
                setTimeout(function () {
                    var el = document.getElementById("modal-greensand");
                    if (el) $(el).modal("show");
                }, 30);
            });
        }

        if (window.gfnChartData) {
            renderGFNCharts();
            window.addEventListener("resize", onWinResize);
        }
    });
})();
