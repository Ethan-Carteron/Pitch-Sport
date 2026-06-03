/**
 * Import page — drag & drop + file selection feedback.
 *
 * Manages the visual state transitions between:
 *   - dropzone (no file)  →  preview (file selected)
 *   - preview (file selected)  →  dropzone (file removed)
 */
(function () {
    'use strict';

    const dropzone = document.getElementById('import-dropzone');
    const preview  = document.getElementById('import-preview');
    const actions  = document.getElementById('import-actions');
    const fileInput = document.getElementById('csv-file');
    const fileName  = document.getElementById('import-file-name');
    const fileSize  = document.getElementById('import-file-size');
    const removeBtn = document.getElementById('import-file-remove');

    if (!dropzone || !fileInput) {
        return;
    }

    /* ─── Helpers ─── */

    function formatBytes(bytes) {
        if (bytes === 0) return '0 o';
        var units = ['o', 'Ko', 'Mo', 'Go'];
        var i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
    }

    function showPreview(file) {
        fileName.textContent = file.name;
        fileSize.textContent = formatBytes(file.size);

        dropzone.style.display = 'none';
        preview.style.display  = 'block';
        actions.style.display  = 'flex';
    }

    function resetToDropzone() {
        fileInput.value = '';
        dropzone.style.display = 'block';
        preview.style.display  = 'none';
        actions.style.display  = 'none';
    }

    /* ─── File input change ─── */

    fileInput.addEventListener('change', function () {
        if (fileInput.files.length > 0) {
            showPreview(fileInput.files[0]);
        }
    });

    /* ─── Remove button ─── */

    if (removeBtn) {
        removeBtn.addEventListener('click', resetToDropzone);
    }

    /* ─── Drag & Drop ─── */

    var dragCounter = 0;

    dropzone.addEventListener('dragenter', function (e) {
        e.preventDefault();
        dragCounter++;
        dropzone.classList.add('drag-over');
    });

    dropzone.addEventListener('dragleave', function (e) {
        e.preventDefault();
        dragCounter--;
        if (dragCounter === 0) {
            dropzone.classList.remove('drag-over');
        }
    });

    dropzone.addEventListener('dragover', function (e) {
        e.preventDefault();
    });

    dropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        dragCounter = 0;
        dropzone.classList.remove('drag-over');

        var files = e.dataTransfer.files;
        if (files.length === 0) return;

        var file = files[0];

        if (!file.name.toLowerCase().endsWith('.csv')) {
            return;
        }

        /* Programmatically set the file on the input via DataTransfer */
        var dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;

        showPreview(file);
    });

    /* ─── Click anywhere on dropzone opens file picker ─── */

    dropzone.addEventListener('click', function (e) {
        /* Avoid double-trigger when clicking the label (which already targets the input) */
        if (e.target.closest('label[for="csv-file"]')) return;
        fileInput.click();
    });
})();
