document.querySelectorAll("[data-file-input]").forEach((input) => {
    const field = input.closest("[data-file-field]");
    const preview = field?.querySelector("[data-file-preview]");
    const nameElement = preview?.querySelector("[data-file-name]");
    const sizeElement = preview?.querySelector("[data-file-size]");

    if (!field || !preview || !nameElement || !sizeElement) return;

    const emptyText = field.dataset.emptyText || "No file selected";
    const emptyHint = sizeElement.textContent;

    input.addEventListener("change", () => {
        const file = input.files?.[0];
        if (!file) {
            nameElement.textContent = emptyText;
            sizeElement.textContent = emptyHint;
            preview.classList.remove("has-file");
            return;
        }

        const size = file.size < 1024 * 1024
            ? `${Math.max(1, Math.round(file.size / 1024))} KB`
            : `${(file.size / (1024 * 1024)).toFixed(1)} MB`;

        nameElement.textContent = file.name;
        sizeElement.textContent = `${size} · Ready to upload`;
        preview.classList.add("has-file");
    });
});
