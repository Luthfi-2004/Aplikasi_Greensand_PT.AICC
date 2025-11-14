(function () {
    // base name
    function baseName(name) {
        const m = name && name.match(/^(.*)_(min|max)$/);
        return m ? m[1] : null;
    }

    // get button
    function getSubmitBtn(form) {
        // id dulu
        let btn = form.querySelector("#gsSubmitBtn");
        // fallback
        if (!btn) btn = form.querySelector("button[type='submit']");
        return btn;
    }

    // lock tombol
    function lockSubmit(form) {
        const btn = getSubmitBtn(form);
        if (!btn || btn.disabled) return;
        btn.disabled = true;
        btn.dataset.orig = btn.innerHTML;
        btn.innerHTML =
            '<span class="spinner-border spinner-border-sm mr-1"></span> Saving...';
    }

    // unlock tombol
    function unlockSubmit(form) {
        const btn = getSubmitBtn(form);
        if (!btn) return;
        if (btn.dataset.orig) btn.innerHTML = btn.dataset.orig;
        btn.disabled = false;
    }

    // normal angka
    function normalize(el) {
        if (!el) return;
        el.value = el.value.replace(",", ".");
        const v = el.value.trim();
        if (v === "") return;
        const n = Number(v);
        if (!isNaN(n)) {
            let s = String(n);
            if (s.indexOf(".") >= 0) s = s.replace(/\.?0+$/, "");
            el.value = s;
        }
    }

    // pasang handler
    function attach(form) {
        const inputs = Array.from(form.querySelectorAll("input.std-num"));
        if (!inputs.length) return;

        // grup pasangan
        const groups = {};

        // inisiasi input
        inputs.forEach(function (el) {
            // ketik ganti
            el.addEventListener("input", function () {
                this.value = this.value.replace(",", ".");
            });

            // blur normal
            el.addEventListener("blur", function () {
                normalize(this);

                // cek pasangan
                const b = baseName(this.name);
                if (!b) return;
                groups[b] = groups[b] || {};
                if (/_min$/.test(this.name)) groups[b].min = this;
                if (/_max$/.test(this.name)) groups[b].max = this;

                // auto tukar
                const g = groups[b];
                if (g.min && g.max) {
                    const a = g.min.value.trim();
                    const z = g.max.value.trim();
                    if (a !== "" && z !== "" && !isNaN(Number(a)) && !isNaN(Number(z))) {
                        const amin = Number(a), amax = Number(z);
                        if (amin > amax) {
                            g.min.value = String(amax).replace(/\.?0+$/, "");
                            g.max.value = String(amin).replace(/\.?0+$/, "");
                        }
                    }
                }
            });

            // seed pasangan
            const b = baseName(el.name);
            if (b) {
                groups[b] = groups[b] || {};
                if (/_min$/.test(el.name)) groups[b].min = el;
                if (/_max$/.test(el.name)) groups[b].max = el;
            }
        });

        // jelang submit
        form.addEventListener("submit", function () {
            // valid cek
            if (form.checkValidity && !form.checkValidity()) return;
            // mulai spinner
            lockSubmit(form);

            // final rapih
            Object.values(groups).forEach(function (g) {
                if (g.min) normalize(g.min);
                if (g.max) normalize(g.max);
                if (g.min && g.max) {
                    const a = g.min.value.trim();
                    const z = g.max.value.trim();
                    if (a !== "" && z !== "" && !isNaN(Number(a)) && !isNaN(Number(z))) {
                        const amin = Number(a), amax = Number(z);
                        if (amin > amax) {
                            g.min.value = String(amax).replace(/\.?0+$/, "");
                            g.max.value = String(amin).replace(/\.?0+$/, "");
                        }
                    }
                }
            });
            // catatan: biarkan submit jalan
        });

        // invalid form
        form.addEventListener("invalid", function (e) {
            // batal spinner
            unlockSubmit(form);
        }, true);
    }

    // dom siap
    document.addEventListener("DOMContentLoaded", function () {
        // pasang semua
        document.querySelectorAll("form").forEach(attach);

        // auto dismiss
        document.querySelectorAll(".alert.auto-dismiss").forEach(function (el) {
            var timeout = parseInt(el.getAttribute("data-timeout") || "3000", 10);
            setTimeout(function () {
                // coba bootstrap
                if (window.jQuery && jQuery.fn && jQuery.fn.alert) {
                    try { jQuery(el).alert("close"); return; } catch (e) {}
                }
                // fallback manual
                el.classList.remove("show");
                el.classList.add("fade");
                setTimeout(function () {
                    if (el && el.parentNode) el.parentNode.removeChild(el);
                }, 150);
            }, timeout);
        });
    });
})();
