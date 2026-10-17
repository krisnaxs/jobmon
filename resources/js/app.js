import "./bootstrap";
import Alpine from "alpinejs";
Alpine.store("sidebar", {
    open: window.innerWidth >= 1024,
    collapsed: localStorage.getItem("sidebar-collapsed") === "true",

    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    },
    toggleCollapse() {
        this.collapsed = !this.collapsed;
        localStorage.setItem(
            "sidebar-collapsed",
            this.collapsed ? "true" : "false",
        );
    },
});
Alpine.data("headerApp", () => ({
    showLogoutConfirm: false,

    toggleSidebar() {
        if (window.innerWidth >= 1024) {
            Alpine.store("sidebar").toggleCollapse();
        } else {
            Alpine.store("sidebar").open = !Alpine.store("sidebar").open;
        }
    },

    confirmLogout() {
        this.showLogoutConfirm = false;
        document.getElementById("headerLogoutForm").submit();
    },

    init() {},
}));
window.addEventListener("resize", () => {
    Alpine.store("sidebar").open = window.innerWidth >= 1024;
});
window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

window.api = {
    async _request(method, url, body = null) {
        const headers = {
            "X-CSRF-TOKEN": window.CSRF_TOKEN,
            Accept: "application/json",
        };

        const opts = { method, headers };

        if (body !== null) {
            const isFormData = body instanceof FormData;

            if (isFormData) {
                opts.body = body;
            } else {
                headers["Content-Type"] = "application/json";
                opts.body = JSON.stringify(body);
            }
        }

        const res = await fetch(url, opts);

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            let msg = err.message || "Request gagal: " + res.status;
            if (err.errors) {
                const details = Object.entries(err.errors)
                    .map(
                        ([field, msgs]) =>
                            `${field}: ${Array.isArray(msgs) ? msgs.join(", ") : msgs}`,
                    )
                    .join(" | ");
                if (details) msg = details;
            }
            throw new Error(msg);
        }

        if (res.status === 204) return {};
        return res.json().catch(() => ({}));
    },

    post(url, body = {}) {
        return this._request("POST", url, body);
    },

    put(url, body = {}) {
        return this._request("PUT", url, body);
    },

    patch(url, body = {}) {
        return this._request("PATCH", url, body);
    },

    delete(url, body = {}) {
        return this._request("DELETE", url, body);
    },

    async get(url) {
        const res = await fetch(url, {
            headers: { Accept: "application/json" },
        });
        if (!res.ok) throw new Error("Request gagal: " + res.status);
        return res.json();
    },
};

window.Alpine = Alpine;
Alpine.start();
