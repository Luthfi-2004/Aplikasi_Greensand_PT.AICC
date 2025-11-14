// Greensand UI
$(function () {
    // Routes cek
    if (!window.greensandRoutes) {
        console.error(
            "greensandRoutes tidak ditemukan. Pastikan Blade sudah @push('scripts')."
        );
        return;
    }

    // AJAX setup
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    // Helper util
    const helpers = {
        // Deteksi shift
        detectShiftByNow() {
            const hh = new Date().getHours();
            return hh >= 6 && hh < 18 ? "D" : "N";
        },
        // Tab aktif — DI-SCOPE KE TAB UTAMA (#gsMainTabs), bukan global .nav-tabs
        getActiveTab() {
            const st = (window.__GS_ACTIVE_TAB__ || "").toLowerCase();
            if (["mm1", "mm2", "all"].includes(st)) return st;

            // Cari hanya di tab utama
            const href =
                $("#gsMainTabs .nav-link.active").attr("href") || "#mm1";
            const id = href.startsWith("#") ? href.slice(1) : href;
            return ["mm1", "mm2", "all"].includes(id) ? id : "mm1";
        },
        // Ambil waktu
        pickTime(val) {
            if (!val) return "";
            const m = String(val).match(/T?(\d{2}:\d{2})(?::\d{2})?/);
            return m ? m[1] : String(val);
        },
        // Ambil keyword
        getKeyword: () => $("#keywordInput").val() || "",
        // Normalisasi angka
        normalizeNumbersInForm($form) {
            $form.find(".js-num").each(function () {
                const v = (this.value || "").trim();
                if (!v) return;
                this.value = v.replace(",", ".");
            });
            $form.find(".js-num-int").each(function () {
                const v = (this.value || "").trim();
                if (!v) return;
                this.value = v.replace(/[^0-9\-]/g, "");
            });
        },
        // Fokus error
        focusFirstError() {
            const $mmErr = $("#mm_error:visible");
            if ($mmErr.length) {
                $("#mm1_btn")[0]?.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
                return;
            }
            const $invalid = $("#gsForm .is-invalid").first();
            if ($invalid.length) {
                $invalid[0].scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
                return;
            }
            const $alert = $("#gsFormAlert:not(.d-none)");
            if ($alert.length)
                $alert[0].scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
        },
    };

    // === Toggle BC tab tergantung MM ===
    function applyBcVisibilityByMm(mmVal) {
        const isMM2 = mmVal === "2" || mmVal === "MM2";
        const $bcNav = $("#tab-bc-nav"); // <li id="tab-bc-nav"> ... </li>
        const $bcPane = $("#tab-bc"); // <div id="tab-bc" class="tab-pane">

        if (isMM2) {
            const $active = $("#gsMainTabs .nav-link.active");
            if ($active.attr("href") === "#tab-bc") {
                $('#gsMainTabs a[href="#tab-mm"]').tab("show");
            }
            $bcPane
                .find(
                    'input[type="text"], input[type="number"], input[type="time"]'
                )
                .val("");
            $bcNav.addClass("d-none");
            $bcPane.removeClass("show active");
        } else {
            $bcNav.removeClass("d-none");
        }
    }

    // Status awal
    window.__GS_ACTIVE_TAB__ = "mm1";
    let pendingDeleteId = null;
    const instances = { mm1: null, mm2: null, all: null };

    // Error handler
    const errorHandler = {
        clear() {
            $("#gsForm .form-control, #gsForm .custom-select").removeClass(
                "is-invalid"
            );
            $("#mm1_btn,#mm2_btn").removeClass("is-invalid");
            $("#gsForm .invalid-feedback").text("").hide();
            const $alert = $("#gsFormAlert");
            if ($alert.length) $alert.addClass("d-none").text("");
        },
        apply(errs) {
            const map = {
                mm: {
                    type: "group",
                    target: "#mm_error",
                    groupBtns: ["#mm1_btn", "#mm2_btn"],
                },
                mix_ke: {
                    type: "input",
                    target: "#mix_ke_error",
                    control: "#mix_ke",
                },
                mix_start: {
                    type: "input",
                    target: "#mix_start_error",
                    control: "#mix_start",
                },
                mix_finish: {
                    type: "input",
                    target: "#mix_finish_error",
                    control: "#mix_finish",
                },
                rs_time: {
                    type: "input",
                    target: "#rs_time_error",
                    control: "#rs_time",
                },
            };
            let general = [];
            Object.entries(errs || {}).forEach(([key, messages]) => {
                const msg = Array.isArray(messages)
                    ? messages.join(" ")
                    : String(messages);
                const m = map[key];
                if (m) {
                    if (m.type === "input" && m.control) {
                        $(m.control).addClass("is-invalid");
                        if ($(m.target).length) $(m.target).text(msg).show();
                    } else if (m.type === "group" && m.groupBtns) {
                        m.groupBtns.forEach((sel) =>
                            $(sel).addClass("is-invalid")
                        );
                        if ($(m.target).length) $(m.target).text(msg).show();
                    }
                } else {
                    general.push(msg);
                }
            });
            if (general.length) {
                const $alert = $("#gsFormAlert");
                if ($alert.length)
                    $alert.removeClass("d-none").text(general.join(" "));
            }
            helpers.focusFirstError();
        },
    };

    // Form manajer
    const formManager = {
        reset() {
            $("#gsForm")[0]?.reset();
            $("#gs_id").val("");
            $("#gs_mode").val("create");
            $("#gsModalMode").text("Add");

            $('input[name="mm"]').prop("checked", false);
            $("#mm1_btn,#mm2_btn").removeClass("active is-invalid");
            $("#mm_error").hide().text("");

            applyBcVisibilityByMm("1");

            const curShift =
                $("#shiftSelect").val() || helpers.detectShiftByNow();
            const label =
                curShift === "D" ? "Day" : curShift === "S" ? "Swing" : "Night";
            $("#gsShiftInfo").text(`Shift: ${curShift} (${label})`);

            errorHandler.clear();
        },
        fill(data) {
            $("#gs_id").val(data.id);
            $("#gs_mode").val("edit");
            $("#gsModalMode").text("Edit");

            const curShift = data.shift || "-";
            const label =
                curShift === "D"
                    ? "Day"
                    : curShift === "S"
                    ? "Swing"
                    : curShift === "N"
                    ? "Night"
                    : "-";
            $("#gsShiftInfo").text(`Shift: ${curShift} (${label})`);

            const mmVal = data.mm === "MM2" ? "2" : "1";
            $(`input[name="mm"][value="${mmVal}"]`).prop("checked", true);
            $("#mm1_btn,#mm2_btn").removeClass("active is-invalid");
            (mmVal === "1" ? $("#mm1_btn") : $("#mm2_btn")).addClass("active");

            applyBcVisibilityByMm(mmVal);

            $("#mix_ke").val(data.mix_ke || "");
            $("#mix_start").val(helpers.pickTime(data.mix_start));
            $("#mix_finish").val(helpers.pickTime(data.mix_finish));
            $("#rs_time").val(helpers.pickTime(data.rs_time));

            const fields = [
                "mm_p",
                "mm_c",
                "mm_gt",
                "mm_cb_mm",
                "mm_cb_lab",
                "mm_m",
                "machine_no",
                "mm_bakunetsu",
                "mm_ac",
                "mm_tc",
                "mm_vsd",
                "mm_ig",
                "mm_cb_weight",
                "mm_tp50_weight",
                "mm_tp50_height",
                "mm_ssi",
                "add_m3",
                "add_vsd",
                "add_sc",
                "bc12_cb",
                "bc12_m",
                "bc11_ac",
                "bc11_vsd",
                "bc16_cb",
                "bc16_m",
                "rs_type",
                "bc9_moist",
                "bc10_moist",
                "bc11_moist",
                "bc9_temp",
                "bc10_temp",
                "bc11_temp",
                "add_water_mm",
                "add_water_mm_2",
                "temp_sand_mm_1",
                "temp_sand_mm_2",
                "total_air_mm1",
                "total_air_mm2",
                "total_mixing_mm1",
                "total_mixing_mm2",
                "total_air_bc9",
                "total_flask",
                "rcs_pick_up",
                "rcs_avg",
                "add_bentonite_ma",
                "total_sand",
                "add_water_bc10",
                "lama_bc10_jalan",
                "rating_pasir_es",
                "bc9_ic_moist",
                "bc9_ic_ac",
                "ssi1_awal",
                "ssi1_akhir",
                "ssi2_awal",
                "ssi2_akhir",
            ];
            fields.forEach((f) => $("#" + f).val(data[f] ?? ""));
        },
    };

    // Bersih interaksi
    $('#mm_group input[name="mm"]')
        .off("change")
        .on("change", function () {
            $("#mm1_btn,#mm2_btn").removeClass("is-invalid");
            $("#mm_error").hide().text("");
            const mmVal = $(this).val();
            applyBcVisibilityByMm(mmVal);
        });

    $("#mix_ke")
        .off("input")
        .on("input", function () {
            $("#mix_ke").removeClass("is-invalid");
            $("#mix_ke_error").text("").hide();
        });

    // Tombol tambah
    $(document)
        .off("click", ".btn-add-gs")
        .on("click", ".btn-add-gs", function () {
            formManager.reset();
            $("#modal-greensand").modal("show");
        });

    // Tombol edit
    $(document)
        .off("click", ".btn-edit-gs")
        .on("click", ".btn-edit-gs", function () {
            errorHandler.clear();
            const id = $(this).data("id");
            formManager.reset();
            $.get(`${greensandRoutes.base}/${id}`)
                .done((res) => {
                    formManager.fill(res.data);
                    $("#modal-greensand").modal("show");
                })
                .fail((xhr) => {
                    $("#gsFormAlert")
                        .removeClass("d-none")
                        .text("Gagal mengambil data (edit).");
                    console.error(xhr.responseText || xhr);
                });
        });

    // Submit form
    $("#gsForm")
        .off("submit")
        .on("submit", function (e) {
            e.preventDefault();
            errorHandler.clear();

            helpers.normalizeNumbersInForm($(this));

            let hasError = false;

            const mmVal = $('input[name="mm"]:checked').val();
            if (!mmVal) {
                $("#mm1_btn,#mm2_btn").addClass("is-invalid");
                $("#mm_error").text("MM wajib dipilih.").show();
                hasError = true;
            }

            const mixKeRaw = ($("#mix_ke").val() || "").trim();
            if (!mixKeRaw) {
                $("#mix_ke").addClass("is-invalid");
                $("#mix_ke_error").text("Mix ke wajib diisi.").show();
                hasError = true;
            } else if (!/^\d+$/.test(mixKeRaw) || parseInt(mixKeRaw, 10) <= 0) {
                $("#mix_ke").addClass("is-invalid");
                $("#mix_ke_error")
                    .text("Mix ke harus bilangan bulat > 0.")
                    .show();
                hasError = true;
            }

            const shift = $("#shiftSelect").val() || "";
            if (!shift) {
                $("#gsFormAlert")
                    .removeClass("d-none")
                    .text("Shift wajib dipilih dari filter.");
                hasError = true;
            }

            if (hasError) {
                helpers.focusFirstError();
                return;
            }

            const mode = $("#gs_mode").val();
            const id = $("#gs_id").val();
            const date = $("#filterDate").val() || "";
            let formData =
                $(this).serialize() +
                `&shift=${encodeURIComponent(shift)}&date=${encodeURIComponent(
                    date
                )}`;

            const $btn = $("#gsSubmitBtn");
            $btn.prop("disabled", true)
                .data("orig", $btn.html())
                .html(
                    '<span class="spinner-border spinner-border-sm mr-1"></span> Saving...'
                );

            const req =
                mode === "edit"
                    ? $.post(
                          `${greensandRoutes.base}/${id}`,
                          formData + "&_method=PUT"
                      )
                    : $.post(greensandRoutes.store, formData);

            req.done(() => {
                $("#modal-greensand").modal("hide");
                reloadAll();
                if (window.gsFlash)
                    gsFlash("Data berhasil disimpan.", "success");
            })
                .fail((xhr) => {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        if (xhr.responseJSON.errors.mm) {
                            $("#mm1_btn,#mm2_btn").addClass("is-invalid");
                            $("#mm_error")
                                .text(xhr.responseJSON.errors.mm.join(" "))
                                .show();
                        }
                        if (xhr.responseJSON.errors.mix_ke) {
                            $("#mix_ke").addClass("is-invalid");
                            $("#mix_ke_error")
                                .text(xhr.responseJSON.errors.mix_ke.join(" "))
                                .show();
                        }
                        // Alert khusus aturan baru & yang lama
                        if (xhr.responseJSON.errors.icssi) {
                            $("#gsFormAlert")
                                .removeClass("d-none")
                                .text(xhr.responseJSON.errors.icssi.join(" "));
                        }
                        if (xhr.responseJSON.errors.molding) {
                            $("#gsFormAlert")
                                .removeClass("d-none")
                                .text(
                                    xhr.responseJSON.errors.molding.join(" ")
                                );
                        }
                        if (xhr.responseJSON.errors.limit2) {
                            $("#gsFormAlert")
                                .removeClass("d-none")
                                .text(xhr.responseJSON.errors.limit2.join(" "));
                        }
                        errorHandler.apply(xhr.responseJSON.errors);
                    } else if (xhr.status === 419) {
                        $("#gsFormAlert")
                            .removeClass("d-none")
                            .text(
                                "CSRF token invalid (419). Silakan refresh halaman."
                            );
                    } else {
                        $("#gsFormAlert")
                            .removeClass("d-none")
                            .text("Gagal menyimpan data.");
                    }
                    helpers.focusFirstError();
                    console.error(xhr.responseText || xhr);
                })
                .always(() => {
                    const $btn = $("#gsSubmitBtn");
                    const orig = $btn.data("orig");
                    if (typeof orig !== "undefined") {
                        $btn.html(orig).removeData("orig");
                    }
                    $btn.prop("disabled", false);
                });
        });

    // Aksi hapus
    $(document)
        .off("click", ".btn-delete-gs")
        .on("click", ".btn-delete-gs", function () {
            pendingDeleteId = $(this).data("id");
            $("#confirmDeleteModal").modal("show");
        });

    // Konfirmasi hapus
    $("#confirmDeleteYes")
        .off("click")
        .on("click", function () {
            if (!pendingDeleteId) return;
            $.post(`${greensandRoutes.base}/${pendingDeleteId}`, {
                _method: "DELETE",
            })
                .done(() => {
                    $("#confirmDeleteModal").modal("hide");
                    reloadAll();
                    if (window.gsFlash)
                        gsFlash("Data berhasil dihapus.", "success");
                })
                .fail((xhr) => {
                    const msg =
                        xhr.status === 419
                            ? "CSRF token invalid (419). Silakan refresh halaman."
                            : "Gagal menghapus data.";
                    $("#confirmDeleteModal .modal-body").prepend(
                        `<div class="alert alert-danger mb-2">${msg}</div>`
                    );
                    setTimeout(
                        () => $("#confirmDeleteModal .alert").remove(),
                        2500
                    );
                    console.error(xhr.responseText || xhr);
                })
                .always(() => {
                    pendingDeleteId = null;
                });
        });

    // Select2 init
    $(function () {
        $("#shiftSelect").select2({
            width: "100%",
            placeholder:
                $("#shiftSelect").data("placeholder") || "Select shift",
        });
    });

    // Filter init
    $("#filterDate").datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        orientation: "bottom",
    });
    $("#btnSearch, #btnQuickSearch").off("click").on("click", reloadAll);
    $("#keywordInput")
        .off("keydown")
        .on("keydown", (e) => {
            if (e.key === "Enter") $("#btnSearch").trigger("click");
        });
    $("#btnRefresh")
        .off("click")
        .on("click", function () {
            $("#filterDate").datepicker("setDate", new Date());
            $("#shiftSelect").val(helpers.detectShiftByNow()).trigger("change");
            $("#keywordInput").val("");
            reloadAll();
            if (window.gsFlash) gsFlash("Filter direset.", "secondary");
        });

    const baseColumns = [
        { data: "action" },
        { data: "date" },
        { data: "pic" },
        { data: "shift" },
        { data: "mm" },
        { data: "mix_ke" },
        { data: "mix_start" },
        { data: "mix_finish" },
        { data: "mm_p" },
        { data: "mm_c" },
        { data: "mm_gt" },
        { data: "mm_cb_mm" },
        { data: "mm_cb_lab" },
        { data: "mm_m" },
        { data: "machine_no" },
        { data: "mm_bakunetsu" },
        { data: "mm_ac" },
        { data: "mm_tc" },
        { data: "mm_vsd" },
        { data: "mm_ig" },
        { data: "mm_cb_weight" },
        { data: "mm_tp50_weight" },
        { data: "mm_tp50_height" },
        { data: "mm_ssi" },
        { data: "add_m3" },
        { data: "add_vsd" },
        { data: "add_sc" },
        { data: "bc12_cb" },
        { data: "bc12_m" },
        { data: "bc11_ac" },
        { data: "bc11_vsd" },
        { data: "bc16_cb" },
        { data: "bc16_m" },
        { data: "rs_time" },
        { data: "rs_type" },
        { data: "bc9_moist" },
        { data: "bc10_moist" },
        { data: "bc11_moist" },
        { data: "bc9_temp" },
        { data: "bc10_temp" },
        { data: "bc11_temp" },
        { data: "add_water_mm" },
        { data: "add_water_mm_2" },
        { data: "temp_sand_mm_1" },
        { data: "temp_sand_mm_2" },
        { data: "total_air_mm1" },
        { data: "total_air_mm2" },
        { data: "total_mixing_mm1" },
        { data: "total_mixing_mm2" },
        { data: "total_air_bc9" },
        { data: "total_flask" },
        { data: "rcs_pick_up" },
        { data: "rcs_avg" },
        { data: "add_bentonite_ma" },
        { data: "total_sand" },
        { data: "add_water_bc10" },
        { data: "lama_bc10_jalan" },
        { data: "rating_pasir_es" },
        { data: "bc9_ic_moist" },
        { data: "bc9_ic_ac" },
        { data: "ssi1_awal" },
        { data: "ssi1_akhir" },
        { data: "ssi2_awal" },
        { data: "ssi2_akhir" },
    ];

    const baseColumnsWithDefaults = baseColumns.map((c) => ({
        ...c,
        defaultContent: "",
    }));

    // Ringkasan tabel
    const summaryManager = {
        load() {
            if (!window.greensandRoutes.summary) return;
            $.get(window.greensandRoutes.summary, {
                date: $("#filterDate").val() || "",
                shift: $("#shiftSelect").val() || "",
                keyword: helpers.getKeyword(),
            })
                .done((res) => this.render(res?.summary || []))
                .fail(() => this.render([]));
        },
        ensureTfoot() {
            const $table = $("#dt-all");
            let $tfoot = $table.find("tfoot");
            if (!$tfoot.length) $tfoot = $("<tfoot/>").appendTo($table);
            return $tfoot;
        },
        render(summary) {
            const $tfoot = this.ensureTfoot();
            const colIndex = {
                mm_p: 8,
                mm_c: 9,
                mm_gt: 10,
                mm_cb_mm: 11,
                mm_cb_lab: 12,
                mm_m: 13,
                machine_no: 14,
                mm_bakunetsu: 15,
                mm_ac: 16,
                mm_tc: 17,
                mm_vsd: 18,
                mm_ig: 19,
                mm_cb_weight: 20,
                mm_tp50_weight: 21,
                mm_tp50_height: 22,
                mm_ssi: 23,
                add_m3: 24,
                add_vsd: 25,
                add_sc: 26,
                bc12_cb: 27,
                bc12_m: 28,
                bc11_ac: 29,
                bc11_vsd: 30,
                bc16_cb: 31,
                bc16_m: 32,
                bc9_moist: 35,
                bc10_moist: 36,
                bc11_moist: 37,
                bc9_temp: 38,
                bc10_temp: 39,
                bc11_temp: 40,
            };
            const makeRow = (label, valuesMap) => {
                let tds = `<td class="text-center font-weight-bold" colspan="8">${label}</td>`;
                for (let i = 8; i < baseColumns.length; i++) {
                    const val = valuesMap?.[i] ?? "";
                    tds += `<td class="text-center">${val}</td>`;
                }
                return `<tr class="gs-summary-row">${tds}</tr>`;
            };
            const rows = { min: {}, max: {}, avg: {}, judge: {} };
            summary.forEach((s) => {
                const idx = colIndex[s.field];
                if (idx == null) return;
                rows.min[idx] = s.min ?? "";
                rows.max[idx] = s.max ?? "";
                rows.avg[idx] = s.avg ?? "";
                rows.judge[idx] = s.judge
                    ? `<span class="${
                          s.judge === "NG"
                              ? "text-danger font-weight-bold"
                              : "text-success font-weight-bold"
                      }">${s.judge}</span>`
                    : "";
            });
            const html =
                makeRow("MIN", rows.min) +
                makeRow("MAX", rows.max) +
                makeRow("AVG", rows.avg) +
                makeRow("JUDGE", rows.judge);
            $tfoot.html(html);
            $tfoot.find("td").addClass("text-center");
        },
    };

    function makeDt($el, url) {
        if ($.fn.dataTable.isDataTable($el)) return $el.DataTable();
        const isAll = $el.attr("id") === "dt-all";
        return $el.DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            scrollX: true,
            autoWidth: false,
            searching: true,
            orderMulti: false,
            searchDelay: 350,
            pagingType: "simple_numbers",
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100, 500, 1000],
                [10, 25, 50, 100, 500, 1000],
            ],
            ajax: {
                url: url,
                type: "POST",
                data: (d) => {
                    d.date = $("#filterDate").val();
                    d.shift = $("#shiftSelect").val() || "";
                    d.keyword = helpers.getKeyword();
                },
            },
            order: isAll
                ? [
                      [3, "asc"],
                      [1, "asc"],
                      [5, "asc"],
                  ]
                : [
                      [1, "asc"],
                      [5, "asc"],
                  ],
            columns: baseColumnsWithDefaults,
            stateSave: false,
            orderCellsTop: true,
            drawCallback: function () {
                const isAll = $(this).attr("id") === "dt-all";
                if (!isAll) return;

                const api = this.api();
                const $tbody = $(api.table().body());
                $tbody.find("tr.mm-spacer").remove();

                let prevMM = null;
                api.rows({ page: "current" }).every(function () {
                    const d = this.data();
                    const curMM = (d?.mm ?? "").toString();
                    const node = $(this.node());
                    if (prevMM !== null && prevMM !== curMM) {
                        node.before(
                            `<tr class="mm-spacer"><td colspan="${api
                                .columns()
                                .count()}" style="height:25px;border:none;padding:0;"></td></tr>`
                        );
                    }
                    prevMM = curMM;
                });

                summaryManager.load();
            },
        });
    }

    // Init datatable
    instances.mm1 = makeDt($("#dt-mm1"), greensandRoutes.mm1);

    // Tab bootstrap — DI-SCOPE KE #gsMainTabs
    const href0 = (
        $("#gsMainTabs .nav-link.active").attr("href") || "#mm1"
    ).toLowerCase();
    window.__GS_ACTIVE_TAB__ =
        href0 === "#mm2" ? "mm2" : href0 === "#all" ? "all" : "mm1";

    $('#gsMainTabs a[data-toggle="tab"]')
        .off("shown.bs.tab")
        .on("shown.bs.tab", function (e) {
            const href = ($(e.target).attr("href") || "").toLowerCase();
            window.__GS_ACTIVE_TAB__ =
                href === "#mm2" ? "mm2" : href === "#all" ? "all" : "mm1";
            if (href === "#mm2" && !instances.mm2)
                instances.mm2 = makeDt($("#dt-mm2"), greensandRoutes.mm2);
            if (href === "#all" && !instances.all)
                instances.all = makeDt($("#dt-all"), greensandRoutes.all);
            $.fn.dataTable
                .tables({ visible: true, api: true })
                .columns.adjust();
            if (href === "#all") setTimeout(() => summaryManager.load(), 100);
        });

    function reloadAll() {
        $.fn.dataTable
            .tables({ visible: false, api: true })
            .ajax.reload(null, false);
        if (helpers.getActiveTab() === "all")
            setTimeout(() => summaryManager.load(), 500);
    }
    window.reloadAll = reloadAll;

    // Ekspor excel
    $("#btnExport")
        .off("click")
        .on("click", function () {
            if (!window.greensandRoutes || !greensandRoutes.export) return;
            const tab = window.__GS_ACTIVE_TAB__ || helpers.getActiveTab();
            const mm = tab === "mm1" ? "MM1" : tab === "mm2" ? "MM2" : "";
            const params = {
                date: $("#filterDate").val() || "",
                shift: $("#shiftSelect").val() || "",
                keyword: helpers.getKeyword(),
            };
            if (mm) params.mm = mm;
            const q = $.param(params);
            window.location.href = greensandRoutes.export + (q ? "?" + q : "");
            if (window.gsFlash) gsFlash("Menyiapkan file Excel…", "info");
        });

    // Default filter
    $("#filterDate").datepicker("setDate", new Date());
    $("#shiftSelect").val(helpers.detectShiftByNow()).trigger("change");
    reloadAll();

    // UI filter
    $("#filterHeader")
        .off("click")
        .on("click", function () {
            $("#filterCollapse").stop(true, true).slideToggle(180);
            $("#filterIcon").toggleClass("ri-subtract-line ri-add-line");
        });

    // Input numeric sanitizer
    function allowNumericInput(el, allowDecimal) {
        el.addEventListener("input", function () {
            let v = el.value;
            v = v.replace(/[^0-9,.\-]/g, "");
            v = v.replace(/(?!^)-/g, "");
            if (!allowDecimal) v = v.replace(/[.,]/g, "");
            el.value = v;
        });
    }

    $("#modal-greensand").on("shown.bs.modal", function () {
        const curMm = $('#mm_group input[name="mm"]:checked').val() || "1";
        applyBcVisibilityByMm(curMm);

        document
            .querySelectorAll("#modal-greensand .js-num")
            .forEach((inp) => allowNumericInput(inp, true));
        document
            .querySelectorAll("#modal-greensand .js-num-int")
            .forEach((inp) => allowNumericInput(inp, false));
    });

    if ($("#modal-greensand").is(":visible")) {
        const curMm = $('#mm_group input[name="mm"]:checked').val() || "1";
        applyBcVisibilityByMm(curMm);

        document
            .querySelectorAll("#modal-greensand .js-num")
            .forEach((inp) => allowNumericInput(inp, true));
        document
            .querySelectorAll("#modal-greensand .js-num-int")
            .forEach((inp) => allowNumericInput(inp, false));
    }
});
