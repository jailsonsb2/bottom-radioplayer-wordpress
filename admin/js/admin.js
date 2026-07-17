/* Bottom Radio Player — admin settings page: stations repeater + media picker. */
(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        const list = document.getElementById("brp-stations-list");
        const addButton = document.getElementById("brp-add-station");
        const template = document.getElementById("brp-station-template");
        if (!list || !addButton || !template) return;

        const DIACRITICS_RE = new RegExp(
            "[" + String.fromCharCode(0x0300) + "-" + String.fromCharCode(0x036f) + "]",
            "g"
        );

        function slugify(text) {
            return text
                .toString()
                .normalize("NFD")
                .replace(DIACRITICS_RE, "")
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, "-")
                .replace(/^-+|-+$/g, "");
        }

        function nextIndex() {
            const cards = list.querySelectorAll(".brp-station-card");
            let max = -1;
            cards.forEach((card) => {
                const i = parseInt(card.getAttribute("data-index"), 10);
                if (!isNaN(i) && i > max) max = i;
            });
            return max + 1;
        }

        addButton.addEventListener("click", function () {
            const index = nextIndex();
            const html = template.innerHTML.split("__INDEX__").join(String(index));
            const wrapper = document.createElement("div");
            wrapper.innerHTML = html.trim();
            const card = wrapper.firstElementChild;
            if (card) list.appendChild(card);
        });

        list.addEventListener("click", function (event) {
            const removeBtn = event.target.closest(".brp-remove-station");
            if (removeBtn) {
                const card = removeBtn.closest(".brp-station-card");
                if (card) card.remove();
                return;
            }

            const chooseBtn = event.target.closest(".brp-choose-image");
            if (chooseBtn) {
                event.preventDefault();
                openMediaPicker(chooseBtn);
            }
        });

        list.addEventListener("input", function (event) {
            const target = event.target;

            if (target.classList.contains("brp-field-name")) {
                const card = target.closest(".brp-station-card");
                const title = card && card.querySelector(".brp-station-title");
                if (title) title.textContent = target.value || (title.dataset.placeholder || "New station");

                const hashField = card && card.querySelector(".brp-field-hash");
                if (hashField && hashField.dataset.auto === "1") {
                    hashField.value = slugify(target.value);
                }
            }

            if (target.classList.contains("brp-field-hash")) {
                target.dataset.auto = "0";
            }
        });

        function openMediaPicker(button) {
            if (!window.wp || !wp.media) return;

            const picker = button.closest(".brp-image-picker");
            const input = picker.querySelector(".brp-image-url");
            const preview = picker.querySelector(".brp-image-preview");

            const frame = wp.media({
                title: button.dataset.title || "Select an image",
                multiple: false,
                library: { type: "image" },
            });

            frame.on("select", function () {
                const attachment = frame.state().get("selection").first().toJSON();
                const url = attachment.url;
                input.value = url;
                if (preview) {
                    preview.src = url;
                    preview.style.display = "";
                }
            });

            frame.open();
        }
    });
})();
