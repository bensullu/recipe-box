// Client-side JS validator for the recipe add/edit form.
// Intercepts submit, validates each field, blocks the request on errors,
// and shows inline .error-message text next to the offending field.
(function () {
    "use strict";

    const form = document.getElementById("recipe-add-form") || document.getElementById("recipe-edit-form");
    if (!form) return;

    const fields = {
        title:        { el: form.querySelector("#title") },
        category_id:  { el: form.querySelector("#category_id") },
        prep_time:    { el: form.querySelector("#prep_time") },
        servings:     { el: form.querySelector("#servings") },
        ingredients:  { el: form.querySelector("#ingredients") },
        instructions: { el: form.querySelector("#instructions") },
    };

    function setError(name, message) {
        const wrapper = fields[name]?.el?.closest(".field");
        if (!wrapper) return;
        let span = wrapper.querySelector(".error-message");
        if (!span) {
            span = document.createElement("span");
            span.className = "error-message";
            wrapper.appendChild(span);
        }
        span.textContent = message;
        fields[name].el.setAttribute("aria-invalid", "true");
    }

    function clearError(name) {
        const wrapper = fields[name]?.el?.closest(".field");
        if (!wrapper) return;
        const span = wrapper.querySelector(".error-message");
        if (span) span.remove();
        fields[name].el.removeAttribute("aria-invalid");
    }

    function validateTitle() {
        const v = (fields.title.el.value || "").trim();
        if (v === "") { setError("title", "Title is required."); return false; }
        if (v.length > 150) { setError("title", "Title must be at most 150 characters."); return false; }
        clearError("title"); return true;
    }

    function validateCategory() {
        const v = fields.category_id.el.value;
        if (!v || v === "0") { setError("category_id", "Please pick a category."); return false; }
        clearError("category_id"); return true;
    }

    function validateInteger(name, label, min, max) {
        const raw = (fields[name].el.value || "").trim();
        if (raw === "") { setError(name, label + " is required."); return false; }
        const n = Number(raw);
        if (!Number.isFinite(n) || !Number.isInteger(n)) { setError(name, label + " must be a whole number."); return false; }
        if (n < min || n > max) { setError(name, label + " must be between " + min + " and " + max + "."); return false; }
        clearError(name); return true;
    }

    function validateText(name, label) {
        const v = (fields[name].el.value || "").trim();
        if (v === "") { setError(name, label + " are required."); return false; }
        clearError(name); return true;
    }

    function validateAll() {
        const results = [
            validateTitle(),
            validateCategory(),
            validateInteger("prep_time", "Preparation time", 1, 1440),
            validateInteger("servings", "Servings", 1, 100),
            validateText("ingredients", "Ingredients"),
            validateText("instructions", "Instructions"),
        ];
        return results.every(Boolean);
    }

    form.addEventListener("submit", function (event) {
        if (!validateAll()) {
            event.preventDefault();
            const firstBad = form.querySelector('[aria-invalid="true"]');
            if (firstBad) firstBad.focus();
        }
    });
}());
