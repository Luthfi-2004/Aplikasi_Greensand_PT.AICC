/* public/assets/js/users.js */
$(function () {
    let usersTable = null;

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    usersTable = $("#usersTable").DataTable({
        processing: true,
        serverSide: true,
        ajax: window.usersDataUrl,
        pageLength: 25,
        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100],
        ],
        order: [[1, "asc"]],
        columns: [
            { data: "action", orderable: false, searchable: false },
            { data: "username", name: "username" },
            { data: "email", name: "email" },
            { data: "role", name: "role" },
        ],
        drawCallback: function () {
            bindRowButtons();
        },
    });

    function flash(text, type = "success") {
        const $area = $("#flashArea");
        if (!$area.length) return;
        const klass = type === "success" ? "alert-success" : "alert-danger";
        $area.html(`<div class="alert ${klass} mb-0 py-2">${text}</div>`);
        setTimeout(
            () =>
                $area.find(".alert").fadeOut(300, function () {
                    $(this).remove();
                }),
            2200
        );
    }

    function clearFormErrors() {
        $("#userForm .form-control").removeClass("is-invalid");
        $("#err_username,#err_email,#err_password,#err_role").text("");
        $("#formAlert").addClass("d-none").text("");
    }

    function applyFormErrors(errs) {
        Object.entries(errs || {}).forEach(([field, messages]) => {
            const msg = Array.isArray(messages)
                ? messages[0]
                : String(messages);
            const $inp = $("#" + field);
            if ($inp.length) $inp.addClass("is-invalid");
            const $err = $("#err_" + field);
            if ($err.length) $err.text(msg);
        });
    }

    function fillForm(user, meta) {
        $("#id").val(user.id);
        $("#username").val(user.username || "");
        $("#email").val(user.email || "");
        $("#password").val("");
        $("#role")
            .val(user.role || "")
            .trigger("change");
        $("#role").prop("disabled", meta && meta.can_edit_role === false);
    }

    function resetForm() {
        $("#userForm")[0].reset();
        $("#id").val("");
        $("#_method").val("POST");
        clearFormErrors();
        $("#role").val("").trigger("change");
        $("#role").prop("disabled", false);
        $("#modalUserLabel").text("Tambah User");
    }

    function openEdit(id) {
        $.get(window.userShowUrl(id))
            .done((res) => {
                resetForm();
                $("#modalUserLabel").text("Edit User");
                $("#_method").val("PUT");
                fillForm(res.user, res.meta || {});
                $("#modal-user").modal("show");
            })
            .fail((xhr) => {
                flash("Gagal memuat data user.", "danger");
                console.error(xhr.responseText);
            });
    }

    // tombol Add
    $(".btn-add").on("click", function () {
        resetForm();
        $("#modal-user").modal("show");
    });

    // bind tombol edit/hapus pada baris datatable
    function bindRowButtons() {
        $(".btn-edit")
            .off("click")
            .on("click", function () {
                openEdit($(this).data("id"));
            });

        $(".btn-delete")
            .off("click")
            .on("click", function () {
                const id = $(this).data("id");
                const $btn = $(this);

                // buka modal konfirmasi password khusus HAPUS
                $("#confirmModalTitle").text("Konfirmasi Hapus");
                $("#confirmModalDesc").text(
                    "Masukkan password Anda untuk menghapus user ini."
                );
                $("#confirm_password_input").val("");
                $("#confirmModalAlert").addClass("d-none").text("");

                $("#confirmModalOk")
                    .off("click")
                    .on("click", function () {
                        const pwd = $("#confirm_password_input").val() || "";
                        if (!pwd) {
                            $("#confirmModalAlert")
                                .removeClass("d-none")
                                .text("Password wajib diisi.");
                            return;
                        }
                        $("#confirmModalOk")
                            .prop("disabled", true)
                            .text("Processing...");

                        $.ajax({
                            url: window.userDestroyUrl(id),
                            method: "POST",
                            data: { _method: "DELETE", confirm_password: pwd },
                            success: function () {
                                $("#modal-confirm").modal("hide");
                                usersTable.ajax.reload(null, false);
                                flash("User berhasil dihapus.", "success");
                            },
                            error: function (xhr) {
                                $("#confirmModalOk")
                                    .prop("disabled", false)
                                    .text("Konfirmasi");
                                if (xhr.status === 422 && xhr.responseJSON) {
                                    const msg =
                                        xhr.responseJSON?.errors
                                            ?.confirm_password?.[0] ||
                                        xhr.responseJSON.message ||
                                        "Validasi gagal.";
                                    $("#confirmModalAlert")
                                        .removeClass("d-none")
                                        .text(msg);
                                    return;
                                }
                                if (xhr.status === 419) {
                                    $("#confirmModalAlert")
                                        .removeClass("d-none")
                                        .text(
                                            "Sesi kadaluarsa (419). Silakan refresh halaman."
                                        );
                                    return;
                                }
                                $("#confirmModalAlert")
                                    .removeClass("d-none")
                                    .text("Gagal menghapus user.");
                                console.error(xhr.responseText);
                            },
                        });
                    });

                $("#modal-confirm").modal("show");
            });
    }

    // submit create/update → TANPA konfirmasi password
    $("#userForm").on("submit", function (e) {
        e.preventDefault();
        clearFormErrors();

        const id = $("#id").val();
        const method = $("#_method").val() || "POST";
        const url =
            method === "PUT" ? window.userUpdateUrl(id) : window.usersStoreUrl;
        const $btn = $("#submitBtn");

        $btn.prop("disabled", true).text("Saving...");

        $.ajax({
            url: url,
            method: "POST",
            data: $(this).serialize(),
            success: function () {
                $("#modal-user").modal("hide");
                usersTable.ajax.reload(null, false);
                flash(
                    method === "PUT"
                        ? "User berhasil diperbarui."
                        : "User berhasil dibuat.",
                    "success"
                );
                $btn.prop("disabled", false).text("Submit");
            },
            error: function (xhr) {
                $btn.prop("disabled", false).text("Submit");

                if (
                    xhr.status === 422 &&
                    xhr.responseJSON &&
                    xhr.responseJSON.errors
                ) {
                    applyFormErrors(xhr.responseJSON.errors);
                    return;
                }
                if (xhr.status === 419) {
                    $("#formAlert")
                        .removeClass("d-none")
                        .text(
                            "Sesi kadaluarsa (419). Silakan refresh halaman."
                        );
                    return;
                }
                $("#formAlert")
                    .removeClass("d-none")
                    .text("Kesalahan saat menyimpan data.");
                console.error(xhr.responseText);
            },
        });
    });

    // select2
    if (typeof $.fn.select2 === "function") {
        $("#role").select2({
            width: "100%",
            placeholder: "Pilih Role",
            dropdownParent: $("#modal-user"),
        });
    }
});
